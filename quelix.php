<?php

/*
 * Plugin Name:       Quelix
 * Description:       Integrates Quentn as an action into the Bricks Builder form element.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            GoSuccess
 * Author URI:        https://gosuccess.io
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:  quentn-wp
 */

declare(strict_types=1);

namespace GoSuccess\quelix;

defined( 'ABSPATH' ) || exit;

/**
 * Main quelix plugin class.
 *
 * Responsibilities:
 *  - Verify runtime dependencies (Bricks theme / Quentn settings)
 *  - Register custom controls + control group for the Bricks form element
 *  - Handle the custom form action to push contact data (and tags) to Quentn
 */
final class Plugin {
    /**
     * Singleton instance reference.
     */
    private static ?self $instance = null;

    /**
     * Action key prefix used to namespace added controls & action slug.
     */
    private const string ACTION_KEY = 'quentn';

    /**
    * Cache of Quentn terms (load once per request)
     */
    private static ?array $term_options_cache = null;

    public function __construct() {
        // Abort early if dependencies are not met to avoid registering dead hooks.
        if ( $this->dependencies_available() === false ) {
            return;
        }

        // Register the extra control group container (shown only when action selected).
        add_filter( 'bricks/elements/form/control_groups', [ $this, 'bricks_form_control_groups' ] );

        // Register individual custom controls (tags + field mapping selectors).
        add_filter( 'bricks/elements/form/controls', [ $this, 'bricks_form_controls' ] );

        // Register form submission action handler (runs after successful validation).
        add_action( 'bricks/form/action/' . self::ACTION_KEY, [ $this, 'bricks_form_action' ] );
    }

    /**
     * Retrieve (and lazily create) singleton instance.
     */
    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Basic dependency gate.
     *
     * We consider the integration viable if:
     *  - Active (or parent) theme is Bricks (ensures hooks exist), OR
     *  - Quentn credentials are already stored (user may switch themes temporarily)
     *
     * @return bool True if plugin should bootstrap.
     */
    private function dependencies_available(): bool {
        // Check if current theme or parent theme is "Bricks"
        $current_theme = wp_get_theme();
        $is_bricks_theme = $current_theme->get('Name') === 'Bricks' ||
            ( $current_theme->parent() && $current_theme->parent()->get('Name') === 'Bricks' );

        // Check if Quentn-WP plugin is active
        $is_quentn_active = is_plugin_active( 'quentn-wp/quentn-wp.php' );

        // Check if required options are set
        $has_app_key = get_option( 'quentn_app_key' );
        $has_base_url = get_option( 'quentn_base_url' );

        // Return true only if all conditions are met
        return $is_bricks_theme && $is_quentn_active && $has_app_key && $has_base_url;
    }

    /**
     * Inject custom control group used to visually group the Quentn controls.
     * Display condition: user selected the custom action in the actions multiselect.
     */
    public function bricks_form_control_groups( array $control_groups ): array {
        $control_groups[self::ACTION_KEY] = [
            'title'     => esc_html__( 'Quentn', 'quelix' ),
            'required'  => [ 'actions', '=', self::ACTION_KEY ],
        ];

        return $control_groups;
    }

    /**
     * Register additional controls (action option + tag & field mapping selectors).
     */
    public function bricks_form_controls( array $controls ): array {
        $controls['actions']['options'][self::ACTION_KEY] = esc_html__( 'Quentn', 'quelix' );

        // Define field mappings for Quentn
        $fields = [
            'Terms'     => [
                'label'      => esc_html__( 'Tags', 'quelix' ),
                'type'       => 'select',
                'multiple'   => true,
                'options'    => $this->get_quentn_term_options(),
                'map_fields' => false,
            ],
            'Email'     => [
                'label'      => esc_html__( 'Field: Email', 'quelix' ),
                'type'       => 'select',
                'multiple'   => false,
                'options'    => [],
                'map_fields' => true,
            ],
            'FirstName' => [
                'label'      => esc_html__( 'Field: First Name', 'quelix' ),
                'type'       => 'select',
                'multiple'   => false,
                'options'    => [],
                'map_fields' => true,
            ],
            'LastName'  => [
                'label'      => esc_html__( 'Field: Last Name', 'quelix' ),
                'type'       => 'select',
                'multiple'   => false,
                'options'    => [],
                'map_fields' => true,
            ],
        ];

        foreach ( $fields as $suffix => $config ) {
            $controls[self::ACTION_KEY . $suffix] = array_merge(
            [ 'group' => self::ACTION_KEY ],
            $config
            );
        }

        return $controls;
    }

    /**
     * Execute on form submission when the custom action is selected.
     * Maps configured form fields to Quentn contact payload and triggers API create.
     */
    public function bricks_form_action( \Bricks\Integrations\Form\Init $form ): void {
        $settings = $form->get_settings();
        $fields   = $form->get_fields();

        // Required: Email
        $email = $this->get_form_field_value( $settings, $fields, 'Email' );

        if ( ! $email ) {
            return;
        }

        $contact_data = [ 'mail' => $email ];

        // Optional: First and Last Name
        if ( $first = $this->get_form_field_value( $settings, $fields, 'FirstName' ) ) {
            $contact_data['first_name'] = $first;
        }

        if ( $last = $this->get_form_field_value( $settings, $fields, 'LastName' ) ) {
            $contact_data['last_name'] = $last;
        }

        // Optional: Tags / Terms
        if ( isset( $settings[self::ACTION_KEY . 'Terms'] ) && $settings[self::ACTION_KEY . 'Terms'] !== false ) {
            $contact_data['terms'] = apply_filters( 'quentn_contact_terms', $settings[self::ACTION_KEY . 'Terms'] );
        }

        try {
            // Final API call to create contact; filter allows last-second customization.
            \Quentn_Wp_Api_Handler::get_instance()->get_quentn_client()->contacts()->createContact(
                apply_filters( 'quentn_contact_data', $contact_data )
            );
        } catch ( \Exception $e ) {
            // Surface failure to form UX without fatal error.
            $form->set_result([
                'action'    => self::ACTION_KEY,
                'type'      => 'error',
                'message'   => $e->getMessage(),
            ]);
        }
    }

    /**
    * Returns cached term options (id => name). On error, returns an empty array.
     */
    private function get_quentn_term_options(): array {
        if ( self::$term_options_cache !== null ) {
            return self::$term_options_cache;
        }

        if ( ! class_exists( '\\Quentn_Wp_Api_Handler' ) ) {
            return self::$term_options_cache = [];
        }

        try {
            $terms = \Quentn_Wp_Api_Handler::get_instance()->get_terms();
            self::$term_options_cache = is_array( $terms ) ? array_column( $terms, 'name', 'id' ) : [];
        } catch ( \Exception $t ) {
            self::$term_options_cache = [];
        }
        return self::$term_options_cache;
    }

    /**
    * Retrieves the value of a mapped form field (suffix: Email|FirstName|LastName)
     */
    private function get_form_field_value( array $settings, array $fields, string $suffix ): ?string {
        $key = self::ACTION_KEY . $suffix;
        if ( empty( $settings[ $key ] ) ) {
            return null;
        }

        $field_id = $settings[ $key ];
        $field_key = "form-field-{$field_id}";
        
        return isset( $fields[ $field_key ] ) ? (string) $fields[ $field_key ] : null;
    }
}

Plugin::get_instance();
