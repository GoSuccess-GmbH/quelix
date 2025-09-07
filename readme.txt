=== Quelix ===
Contributors: gosuccess
Donate link: https://gosuccess.io
Tags: bricks, forms, quentn, contacts, integration
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Seamlessly connect Bricks Builder forms to Quentn for instant contact creation and tag assignment.

== Description ==
Integrate the Quentn marketing & automation platform directly into Bricks Builder forms. Quelix adds a **Quentn** action to any Bricks form so each submission can instantly create (or update) a contact in Quentn and optionally assign tags.

=== Key Features ===
* Adds a new form action: "Quentn" inside Bricks Builder.
* Map form fields (Email, First Name, Last Name) to Quentn contact data.
* Assign selected Quentn tags automatically on submission.
* Clean error handling (form result messages instead of silent fails).
* Extensible via WordPress filters (`quentn_contact_data`, `quentn_contact_terms`).
* Cached tag (term) options to avoid repeated API calls.

=== Benefits ===
* No manual CSV export or copy & paste.
* Reuse existing Bricks forms — no custom coding.
* Immediate funnel entry (submit → contact + tags in Quentn).
* Flexible field mapping (only Email required).
* Performance minded (in-memory term cache per request).

=== Requirements ===
* Bricks theme (parent or child) active.
* Official Quentn WP Plugin active & connected to your Quentn account.
* PHP 8.3+

=== Privacy / GDPR ===
You are responsible for obtaining valid consent before transmitting personal data to Quentn. Add an explicit consent checkbox where required and mention the data flow in your privacy policy. Collect only necessary data. Consider double opt-in if mandated by your jurisdiction.

== Installation ==
1. Upload the `quelix` folder to `/wp-content/plugins/` or install the ZIP via the WordPress admin.
2. Activate the plugin through the 'Plugins' screen.
3. Ensure the Bricks theme is active and the Quentn plugin is connected to your account.
4. Edit or create a Bricks form.
5. Add the action "Quentn" under Form Actions.
6. Configure: Tags and field mappings (Email required, First and Last Name optional).
7. Save and submit a test entry on the frontend.
8. Verify the new contact (and tags) appears in Quentn.

== Frequently Asked Questions ==
= The Quentn action does not appear =
Confirm: Bricks theme active, Quentn plugin active & configured, clear any caches.

= Submission does nothing =
Check browser console & PHP error log. Ensure Email field mapping is set.

= Tags not applied =
Make sure at least one tag was selected in the form and that it exists in Quentn.

= I see an undefined index warning =
Re-save the form ensuring the Email mapping is chosen; the plugin only proceeds with a valid mapped email.

= Can I modify the contact payload before sending? =
Yes. Use the `quentn_contact_data` filter. For terms, use `quentn_contact_terms`.

= How do I extend with custom fields? =
Hook into `quentn_contact_data` and append additional key/value pairs supported by the Quentn API.

== Screenshots ==
1. Select "Quentn" as the action for your form.
2. Assign the tags and fields to be sent to Quentn.

== Changelog ==

= 1.0.0 =
* Initial public release: Bricks form action for Quentn with tags + field mapping.
