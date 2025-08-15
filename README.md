# Quelix (English Version)

Integrate the Quentn marketing & automation platform directly into Bricks Builder forms. Quelix adds a "Quentn" action to any Bricks form so each submission can instantly create (or update) a contact in Quentn and optionally assign tags.

![Overview – Quelix + Bricks + Quentn](https://placehold.co/1080x720?text=Quelix+Overview)

## Key Features
- Adds a new form action: "Quentn" inside Bricks Builder.
- Map form fields (Email, First Name, Last Name) to Quentn contact data.
- Assign selected Quentn tags automatically on submission.
- Clean error handling: prevents silent failures and displays form result messages.
- Extensible via WordPress filters (e.g. `quentn_contact_data`, `quentn_contact_terms`).

![Form Actions – Quentn Action Enabled](https://placehold.co/1080x720?text=Bricks+Form+Actions)

## Benefits
- No manual CSV exports or copy & paste.
- Reuse existing Bricks forms—no custom coding needed.
- Immediate funnel entry: submit → contact appears in Quentn with tags.
- Flexible field mapping; only Email is required.
- Caching of tag (term) options for performance.

![Tag Selection UI](https://placehold.co/1080x720?text=Tag+Selection)

## Requirements
| Component | Minimum |
|-----------|---------|
| PHP       | 8.3+ (due to typed class constant usage) |
| WordPress | 6.0+ (hooks & modern form features) |
| Theme     | Bricks (parent or child theme active) |
| Plugin    | Official Quentn WP Plugin (configured with API key & base URL) |

If you need broader compatibility (lower PHP version), you can refactor the typed class constant—open an issue if you want guidance.

## Installation
1. Upload the plugin folder to `wp-content/plugins/` or install via ZIP in the WordPress admin.
2. Activate the plugin in WordPress.
3. Ensure: Bricks theme is active AND the Quentn plugin is configured (API key + Base URL).
4. Clear any caching layers (object/page cache) if hooks don’t appear.

![Plugin Activation](https://placehold.co/1080x720?text=Plugin+Activation)

## Usage
1. Open (or create) a Bricks form element.
2. In the Actions panel, add the action "Quentn".
3. A new settings group "Quentn" appears.
4. Configure:
   - Tags: Select one or more Quentn tags to auto-assign.
   - Field: Email (required) – choose the form field containing the user’s email.
   - Field: First Name (optional)
   - Field: Last Name (optional)
5. Save the page and submit a test entry on the frontend.
6. Verify the contact appears inside Quentn with correct data and tags.

![Field Mapping – Email / First / Last Name](https://placehold.co/1080x720?text=Field+Mapping)
![Successful Frontend Submission](https://placehold.co/1080x720?text=Success+Message)
![Contact in Quentn After Submission](https://placehold.co/1080x720?text=Quentn+Contact)

## How It Works (Under the Hood)
- Hooks into Bricks form control registration to inject custom controls for tags + field selectors.
- Caches tag (term) options so repeated form renders don’t spam the Quentn API.
- On submission, maps the configured fields, builds a payload, applies filters, and calls Quentn’s contact creation endpoint.
- Updates the form result with a success or error message.

## Available Filters
You (or other plugins) can modify behavior:
- `quentn_contact_data` – Filter the associative array before sending to Quentn.
- `quentn_contact_terms` – Filter the tag ID array.

Example (in a small must-use plugin or theme functions):
```php
add_filter('quentn_contact_data', function(array $data, array $settings, array $fields){
    $data['custom_field'] = 'LandingPage-123';
    return $data;
}, 10, 3);
```

## Troubleshooting
| Symptom | Check |
|---------|-------|
| Quentn action not visible | Bricks active? Quentn plugin configured? Clear caches. |
| Submission does nothing | Open browser console / PHP error log; verify Email field mapping. |
| Tags not applied | Confirm tags selected in form AND exist in Quentn. |
| PHP warning about undefined index | Re-save form ensuring Email mapping is set. |
| API errors | Confirm API base URL & key in Quentn plugin settings. |

![Troubleshooting – Logs / Errors](https://placehold.co/1080x720?text=Debug+Logs)

## Privacy / GDPR Notice
Ensure you collect valid consent (e.g. a required consent checkbox) and update your privacy policy to mention transmission of data to Quentn. Only collect the data you truly need. Consider double opt-in flows where required.

## Uninstall / Deactivation
Deactivating Quelix does NOT remove any contacts or tags inside Quentn. It only stops new submissions from being sent.

## Roadmap (Potential Enhancements)
- Admin notice if dependencies missing.
- Additional hooks (`quelix_before_create_contact`, `quelix_after_create_contact`).
- Batch sync mode / log screen.
- Optional lower PHP version compatibility layer.

Open an issue if you need one of these prioritized.

## Changelog
(Placeholder – add entries as you publish versions.)

| Version | Date | Notes |
|---------|------|-------|
| 0.1.0   | TBD  | Initial public version. |

---
Automate your lead flow: Bricks form → Quentn contact → Funnel magic.
