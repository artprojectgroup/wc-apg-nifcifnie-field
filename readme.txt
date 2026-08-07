=== WC - APG NIF/CIF/NIE Field ===
Contributors: artprojectgroup
Donate link: https://artprojectgroup.es/tienda/donacion
Tags: nif, cif, nie, eori, vies
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 4.15.0
Requires PHP: 7.4
WC requires at least: 5.6
WC tested up to: 11.0.0
License: GNU General Public License v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add to WooCommerce a NIF/CIF/NIE field for all billing and shipping forms, with validation and VIES/EORI support.

== Description ==
**WC - APG NIF/CIF/NIE Field** adds to your WooCommerce store a new NIF/CIF/NIE field in all billing and shipping forms, available to both the administrator and the customer.

**Requirements:** the plugin needs WooCommerce 2.4.0 or higher and the [SoapClient](http://php.net/manual/en/class.soapclient.php) PHP class for VIES/EORI validation.

= Features =
* Fully compatible with the Checkout block of the WordPress block editor.
* You can require the NIF/CIF/NIE field in the billing form.
* You can require the NIF/CIF/NIE field in the billing form only from a given order amount.
* You can require the NIF/CIF/NIE field in the shipping form.
* You can hide the NIF/CIF/NIE field from the shipping form.
* You can customize the priority (position) of the field.
* You can validate the NIF/CIF/NIE field.
* You can validate the VIES VAT number field to apply a tax exemption.
* You can validate the EORI field to prevent the sale if a valid number is not entered.
* You can select the country(ies) where the EORI field will be validated.
* You can customize the label and placeholder of the NIF/CIF/NIE, VIES VAT number or EORI field.
* You can customize the error message of the field NIF/CIF/NIE, VIES VAT number or EORI.
* You can customize the error message if the maximum number of requests to the VIES VAT number verification API is exceeded.
* Add and require phone and email fields in the shipping form.
* You can remove the phone and email fields from the default address.
* You can display and customize an error message for the billing form using the `apg_nif_display_error_message` and `apg_nif_error_message` filters.
* You can remove the Email and Phone fields from the submission form with the `apg_nif_add_fields` filter.
* You can skip validation by country or external condition with the `apg_nif_skip_validation` filter.
* You can override the required status for billing or shipping with the `apg_nif_skip_required` filter.
* You can change the order amount compared against the configured threshold with the `apg_nif_importe_del_pedido` filter.
* Adds a customer download button in WooCommerce (Customers) that includes the NIF/CIF/NIE field in the CSV.
* It validates documents from:
 * Albania.
 * Andorra.
 * Austria.
 * Argentina.
 * Åland Islands.
 * Belgium.
 * Bulgaria.
 * Belarus.
 * Switzerland.
 * Chile.
 * Cyprus.
 * Czech Republic.
 * Germany.
 * Denmark.
 * Estonia.
 * Spain.
 * European Union.
 * Finland.
 * Faroe Islands.
 * France.
 * Great Britain.
 * Greece.
 * Croatia.
 * Hungary.
 * Ireland.
 * Iceland.
 * Italy.
 * Liechtenstein.
 * Lithuania.
 * Luxembourg.
 * Latvia.
 * Monaco.
 * Moldova.
 * Montenegro.
 * North Macedonia.
 * Malta.
 * Netherlands.
 * Norway.
 * Poland.
 * Portugal.
 * Romania.
 * Serbia.
 * Sweden.
 * Slovenia.
 * Slovak Republic.
 * San Marino.
 * Ukraine.
* 100% compatible with [WooCommerce PDF Invoices & Packing Slips](https://es.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/).
* 100% compatible with [WPML](https://wpml.org/?aid=80296&affiliate_key=m66Ss5ps0xoS).
* 100% compatible with [Checkout Field Editor (Checkout Manager) for WooCommerce](https://wordpress.org/plugins/woo-checkout-field-editor-pro/)

= Translations =
* English: by [**Art Project Group**](https://artprojectgroup.es/) (default language).
* Spanish: by [**Art Project Group**](https://artprojectgroup.es/).

= More information =
You can learn more about **WC - APG NIF/CIF/NIE Field** on our [official website](https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-nifcifnie-field), and follow the development on [GitHub](https://github.com/artprojectgroup/wc-apg-nifcifnie-field).

== Installation ==
1. Install the plugin in one of the following ways:
 * Upload the `wc-apg-nifcifnie-field` folder to the `/wp-content/plugins/` directory via FTP.
 * Upload the full ZIP file via *Plugins -> Add New -> Upload* in the WordPress administration panel.
 * Search for **WC - APG NIF/CIF/NIE Field** in *Plugins -> Add New* and click *Install Now*.
2. Activate the plugin through the *Plugins* menu in the WordPress administration panel.
3. Configure the plugin in *WooCommerce -> NIF/CIF/NIE field* or through the *Settings* link on the plugins page.

== Frequently Asked Questions ==
= How do I configure the plugin? =
The plugin settings are very simple: you only need to indicate whether you want the NIF/CIF/NIE field to be validated or not, and configure the optional VIES and EORI validation if you need them.

= How do I fix duplicate NIF metadata from older orders? =
If your store placed orders via the Checkout block (or the classic checkout) with an older version of the plugin, some orders may have duplicate or legacy `billing_nif` / `shipping_nif` metadata rows that prevent the field from updating correctly in the admin panel.

**Option 1 – Settings page (recommended):** Go to *WooCommerce -> NIF/CIF/NIE field*. If duplicates are detected, a "Fix duplicate NIF metadata" button appears at the bottom of the page. Click it to run the cleanup. The button disappears automatically once there are no more duplicates.

**Option 2 – WP-CLI (large stores):** Run the following command from the root of your WordPress installation to avoid HTTP timeouts:

`wp eval-file wp-content/plugins/wc-apg-nifcifnie-field/includes/admin/limpieza-meta-duplicados.php`

= Where can I get support? =
**WC - APG NIF/CIF/NIE Field** is a free plugin. **Art Project Group** does not provide free technical support, but offers a paid [technical support](https://artprojectgroup.es/tienda/ticket-de-soporte) service for installation and configuration.

== Screenshots ==
1. Screenshot of WC - APG NIF/CIF/NIE field.
2. Screenshot of WC - APG NIF/CIF/NIE field. Billing and shipping forms. Checkout Block.
3. Screenshot of WC - APG NIF/CIF/NIE field. Billing and shipping forms. Classic Shortcode.

== Changelog ==
= 4.15.0 =
* New setting "Require billing field from this amount": the NIF/CIF/NIE field becomes required in the billing form when the order total (taxes included) is equal to or greater than the amount entered, even if the "Require billing field?" option is not checked. Leave it empty to disable it. It never affects the shipping form, which keeps behaving as configured. The `apg_nif_importe_del_pedido` filter lets you compare a different amount (for example, the subtotal without taxes or without shipping costs).
* Fixed: in the Checkout block, the billing field was not enforced as required when "Show shipping field?" was unchecked and both required options were checked. The field was not registered as natively required and the plugin's own check was skipped, so the order could be placed with an empty NIF/CIF/NIE.
* Fixed: in the Checkout block, the per-form required check only ran when some validation (NIF/CIF/NIE, VIES or EORI) was enabled. Requiring the field in a single form without any validation active had no effect.
* Security: the AJAX endpoint that applies the VAT exemption in the Checkout block (`apg_nif_quita_iva_bloques`) trusted the value sent by the browser and did not check any nonce, so any visitor could remove the VAT from their own order without a valid VAT number. It now verifies the nonce and recalculates the exemption on the server with the submitted VAT number and country.
* The field label is now kept in sync with the order amount in both checkouts: the "(optional)" text disappears as soon as the order reaches the configured amount, and comes back if it drops below it (for example, after applying a coupon). The state is always calculated on the server, so the label and the validation can never disagree.
* In the Checkout block, with "Use same address for billing" ticked only the shipping form is shown, and that address is also the billing one: its NIF/CIF/NIE field now follows the billing rules, as it should.
* Fixed: when the field was required and left empty with validation enabled, two error messages were shown (the required one and the invalid format one). The format is no longer validated on an empty field.
* Fixed: uninstalling the plugin left behind the `apg_nif_meta_pedido_migrado` option and the VIES/EORI cache transients.
* The `json_split_objects()` helper function has been renamed to `apg_nif_json_split_objects()`. It was declared in the global namespace without a prefix, which could cause a fatal error if another plugin declared the same name.
* Internal cleanup: removed unreachable code, unused globals and an unnecessary address-fields lookup in the shipping form.

= 4.14.1 =
* Fixed: the metadata cleanup and the one-time automatic migration did not actually process the HPOS order table (`wc_orders_meta`); they relied on the `$wpdb->wc_orders_meta` property, which WooCommerce does not set, so on HPOS stores the 4.14.0 migration touched only `postmeta` and left the orders untouched. The HPOS table is now detected from the database prefix, and the automatic one-time cleanup runs again on update to 4.14.1 to migrate those orders to `_billing_nif` / `_shipping_nif`.

= 4.14.0 =
* The order meta key for the NIF/CIF/NIE is now `_billing_nif` / `_shipping_nif` (with a leading underscore), matching WooCommerce's native order meta convention (`_billing_*`) so the value is read by ERP integrations such as Holded and others that expect the underscored key. Reading still falls back to the legacy `billing_nif` / `shipping_nif` for orders placed with previous versions.
* The duplicate-metadata cleanup tool now migrates the legacy `billing_nif` / `shipping_nif` keys to the canonical `_billing_nif` / `_shipping_nif` (in both `postmeta` and HPOS `wc_orders_meta`). Run it from the plugin settings or via WP-CLI on large stores.
* This cleanup also runs automatically once, in the admin, right after updating to this version, so the migration happens without clicking the button (the settings button and the WP-CLI script remain available as an alternative for very large stores).
* Customer (user) meta is unchanged: it stays `billing_nif` / `shipping_nif`, as WooCommerce stores customer billing fields without the underscore.

= 4.13.0 =
* Fixed the NIF/CIF/NIE field not being auto-filled when selecting a customer while creating or editing an order in the admin panel. The field is now rendered with the id that WooCommerce's customer-details script expects (`_billing_nif` / `_shipping_nif`), while the value is still stored under the canonical `billing_nif` / `shipping_nif` keys.
* Unified the NIF metadata key across every save path. The classic checkout no longer leaves a legacy `_billing_nif` / `_shipping_nif` copy, and editing an order from the admin panel no longer creates a second metadata entry.
* Rewrote the duplicate NIF metadata detection and cleanup tool. Detection now also finds orders where `billing_nif` and `_billing_nif` (or `shipping_nif` and `_shipping_nif`) coexist, works on both `postmeta` and `wc_orders_meta` (HPOS, including sync mode and configurations where the cleanup button previously failed to appear), and caches the query for one hour to avoid performance issues on large stores. The cleanup migrates lone legacy keys to the canonical key, removes legacy keys when the canonical one already exists, and deduplicates repeated rows, so detection and cleanup always agree (fixing the case where the button appeared but reported "0 fixed").
* Reviewed and corrected the plugin's English texts. Fixed the wording of the settings description and field tooltips (e.g. "validate the field before submission", "allow and validate the VIES VAT number / the EORI number") and of the information box ("Contact us", "If you enjoy this plugin and find it helpful"), and renamed the "End Purchase block" reference to the correct WooCommerce term "Checkout block".

= 4.12.2 =
* Fixed the root cause of the NIF field not saving when editing an existing order from the admin panel: the field definition was missing an explicit `id`, so WooCommerce was writing the admin-entered value to `_billing_nif` / `_shipping_nif` instead of `billing_nif` / `shipping_nif`. As a result, the display always showed the original checkout value unchanged.
* Fixed the duplicate NIF metadata detection function so it checks both `postmeta` and `wc_orders_meta` (HPOS) regardless of the active storage mode. Previously the cleanup button could fail to appear on stores using HPOS sync mode or certain HPOS configurations.

= 4.12.1 =
* Fixed a regression introduced in 4.12.0 where editing the NIF field on an existing order from the admin panel and saving would silently discard the new value, leaving the old one in the database. This happened on stores with duplicate NIF metadata rows created by older versions of the plugin. The automatic deduplication hook has been removed; use the cleanup button in the plugin settings or the WP-CLI script instead.

= 4.12.0 =
* Fixed duplicate `billing_nif` / `shipping_nif` metadata rows generated when placing orders with Checkout Blocks, which caused the NIF field to appear unchanged after editing an order from the admin panel.
* Added a "Fix duplicate NIF metadata" button in the plugin settings page to clean up existing duplicate rows in stores affected by previous versions.

= 4.11.4 =
* Fixed a Checkout Blocks regression introduced in 4.11.3 where the NIF/CIF/NIE field could be treated as a native address field during Store API requests and incorrectly trigger a required-field error on checkout.

= 4.11.3 =
* Fixed the NIF/CIF/NIE field in My Account address editing when the account page is rendered with the `[woocommerce_my_account]` shortcode on stores using Checkout Blocks.
= 4.11.2 =
* Fixed order admin and formatted address NIF loading for orders created with Checkout Blocks by reading `billing_nif` and `shipping_nif` first, with fallback to legacy meta keys.
= 4.11.1 =
* Fixed `requerido_envio` in classic checkout so the shipping NIF/CIF/NIE field is only required when the customer checks "Ship to a different address".
= 4.11.0 =
* Added the `apg_nif_skip_required` filter to override the required status for billing or shipping via hook.
= 4.10.0 =
* Added support for Italian `Codice Fiscale` validation.
* Fixed international prefix detection so non-ISO leading letters are not treated as country prefixes.
* Fixed Checkout Block VIES validation initialization in `valida-bloques-nif.js`.
= 4.9.1 =
* Fixed compatibility with FunnelKit Builder custom checkout validation when showing the shipping field.
* Minor fixes.
= 4.9.0 =
* Added compatibility declaration for `product_instance_caching`.
* Improved VIES, EORI and VAT exemption handling.
= 4.8.3 =
* Added a new option to hide the shipping NIF/CIF/NIE field.
* Improved required billing/shipping field handling in the Checkout Block.
* Fixed compatibility with Checkout Field Editor (Checkout Manager) for WooCommerce.
* Minor fixes.
= 4.8.2 =
* Minor fixes.
= 4.8.1 =
* Added the `apg_nif_skip_validation` filter
* Minor fixes.
= 4.8.0 =
* Fixed JavaScript validation.
= 4.7.0.6 =
* Minor fixes.
= 4.7.0.5 =
* Minor fixes.
= 4.7.0.4 =
* Minor fixes.
= 4.7.0.3 =
* Minor fixes.
= 4.7.0.2 =
* Minor fixes.
= 4.7.0.1 =
* Minor fixes.
= 4.7 =
* Addition of a customer download button in WooCommerce (Customers) that includes the NIF/CIF/NIE field in the CSV.
* Minor fixes.
= 4.6.0.1 =
* Minor fixes.
= 4.6 =
* Addition of document validation for new countries.
* Minor fixes.
= 4.5.0.2 =
* Minor fixes.
= 4.5.0.1 =
* Minor fixes.
= 4.5 =
* Added PHPDoc blocks throughout the code.
* Fixed compatibility with Checkout Field Editor (Checkout Manager) for WooCommerce.
* Minor fixes.
= 4.4.0.1 =
* Minor fixes.
= 4.4 =
* Fixed tax exemption handling.
* Minor fixes.
= 4.3 =
* Fixed required field handling in the addresses on the My Account page.
* Minor fixes.
= 4.2 =
* Fixed JavaScript validation.
* Minor fixes.
= 4.1.0.1 =
* Correction and unification of user metas.
= 4.1 =
* Added shipping country check to the tax exemption.
* Minor fixes.
= 4.0.0.9 =
* Minor fixes.
= 4.0.0.8 =
* Minor fixes.
= 4.0.0.7 =
* Minor fixes.
= 4.0.0.6 =
* Added the NIF field to the Checkout block address.
= 4.0.0.5 =
* Minor fixes.
= 4.0.0.4 =
* Added live validation.
* Improved security.
* Minor fixes.
= 4.0.0.3 =
* Minor fixes.
= 4.0.0.2 =
* Minor fixes.
= 4.0.0.1 =
* Minor fixes.
= 4.0 =
* Full compatibility added for VIES and EORI validation in the Checkout Block.
* General performance improvements.
* Full code compliance with WordPress security standards.
* Addition of document validation for new countries.
* Minor fixes.
= 3.2.0.1 =
* Small fixes.
= 3.2 =
* Added `apg_nif_add_fields` filter.
* Improved validation.
= 3.1.0.2 =
* Small fixes.
= 3.1.0.1 =
* Small fixes.
= 3.1 =
* Improved security.
* Improved international validation process.
* Small fixes.
= 3.0.15 =
* Small fixes.
= 3.0.14 =
* Small fixes.
= 3.0.13 =
* Small fixes.
= 3.0.12 =
* Small fixes.
= 3.0.11 =
* Small fixes.
= 3.0.10 =
* Limits features to the Checkout Block.
= 3.0.9 =
* Small fixes.
= 3.0.8 =
* Small fixes.
= 3.0.7 =
* Small fixes.
= 3.0.6 =
* Small fixes.
= 3.0.5 =
* Small fixes.
= 3.0.4 =
* Improved compatibility with third party plugins.
= 3.0.3 =
* Improved compatibility with third party plugins.
= 3.0.2 =
* Small fixes.
= 3.0.1 =
* Fixed a bug affecting versions below WooCommerce 8.9.
= 3.0 =
* Added support for the Checkout block.
* Improved compatibility with third party plugins.
= 2.1.0.2 =
* Small fixes.
= 2.1.0.1 =
* Small fixes.
= 2.1 =
* Added HPOS support.
= 2.0.1 =
* Small fixes.
= 2.0 =
* Added EORI number validation option **Upgrade sponsored by [OldWood - Ground, Oil Varnishes & Natural Colours](https://www.oldwood1700.com)**.
* Added option to select priority of the field NIF/CIF/NIE.
* Added two filters to display and customize an error message for the billing form: `apg_nif_display_error_message` and `apg_nif_error_message`. 
* Screenshot updated.
= 1.7.4.1 =
* Header updated.
* Stylesheet updated.
* Screenshot updated.
= 1.7.4 =
* Validates the shipping form only if it has been activated.
* Validates VIES number only for supported countries.
= 1.7.3.1 =
* Indicates the corresponding form in the field validation.
= 1.7.3 =
* Add the NIF/CIF/NIE field in the order search.
= 1.7.2.6 =
* Small fixes.
= 1.7.2.5 =
* Small fixes.
= 1.7.2.4 =
* Small fixes.
= 1.7.2.3 =
* Added support for Polylang.
= 1.7.2.2 =
* Small fixes.
= 1.7.2.1 =
* Small fixes.
= 1.7.2 =
* VIES validation fix for Greece and Ireland.
= 1.7.1 =
* Adjustment to make the new option compatible with WooCommerce PDF Invoices & Packing Slips.
= 1.7 =
* Now you can now remove the phone and email fields from the default address.
= 1.6 =
* Customizable error messages and translatable with WPML.
* Small fixes.
= 1.5.1 =
* Added tax exemption on shipping costs.
= 1.5 =
* Removed tax exemption on shipping costs.
* Added phone and email fields in international addresses.
* Small fixes.
= 1.4.2 =
* Remove double phone and email address in the Thank you page.
= 1.4.1 =
* Text field name translatable with WPML.
= 1.4.0.2 =
* Updated email account template.
* Small fixes.
= 1.4.0.1 =
* Small fixes.
= 1.4 =
* Added customization for the field label and placeholder.
* Updated screenshot.
= 1.3.0.2 =
* Small fixes.
* Removed all the changes of fields order.
= 1.3.0.1 =
* Small fixes.
= 1.3 =
* Added fields in WooCommerce PDF Invoices & Packing Slips for invoices with addresses outside Spain.
= 1.2.1.3 =
* Small fixes.
= 1.2.1.2 =
* Added error message and deactivation when SoapClient PHP class doesn't exist.
= 1.2.1.1 =
* Added WooCommerce 3.4 compatibility.
= 1.2.1 =
* Small fixes.
= 1.2.0.4 =
* Remove double phone and email address in the order email.
* Fixed CIF number validation.
= 1.2.0.3 =
* Remove double phone and email address in the order email.
= 1.2.0.2 =
* Header updated.
* Stylesheet updated.
* Screenshot updated.
= 1.2.0.1 =
* Fixed CIF number validation.
= 1.2 =
* Prevent NIF/CIF/NIE field validation out from Spain.
* Minor fixes.
= 1.1.0.6 =
* Display the email field in manually created orders in WooCommerce 3.x.
* Prevent NIF/CIF/NIE validation with VIES number validation selected.
= 1.1.0.5 =
* Text field name translatable.
= 1.1.0.4 =
* Improved performance.
= 1.1.0.3 =
* Fixed localization.
= 1.1.0.2 =
* Improved VIES number validation.
= 1.1.0.1 =
* Internationalization of VIES number validation.
= 1.1 =
* Added VIES VAT number validation option.
= 1.0.1.3 =
* Adjust to optimize WooCommerce 3.0 compatibility.
= 1.0.1.2 =
* Support for multisite installations.
= 1.0.1.1 =
* Display all fields in non registered users.
= 1.0.1 =
* Fixed email addresses format.
* Fixed missed Spanish translations.
= 1.0 =
* Display the email field in manually created orders.
* Display the email in customer data.
* Removed all custom classes from fields to improve universal compatibility with templates.
* Internal plugin structure fully rewritten for easy maintenance.
= 0.3 =
* Load the NIF/CIF/NIE field value in manually created orders.
= 0.2 =
* Added new setting options.
* Updated translation.
* Updated screenshot.
= 0.1 =
* Initial version.

== Upgrade Notice ==
= 4.15.0 =
* Security fix: the VAT exemption endpoint used by the Checkout block trusted the browser and checked no nonce, so any visitor could remove the VAT from their own order without a valid VAT number. Also adds a new setting to require the billing field from a given order amount. Recommended update for every store, and required if you use the VIES VAT number validation.

= 4.14.1 =
* Fixes the migration not reaching the HPOS order table (`wc_orders_meta`), so the NIF data on HPOS stores is actually migrated to `_billing_nif` / `_shipping_nif`. Recommended update.

== Thanks ==
Thanks to everyone who uses the plugin, helps improve it, makes a donation or encourages us with their comments.

If you find this plugin useful, you can support its development with a [small donation](https://artprojectgroup.es/tienda/donacion).

== External Services ==
1. To the WordPress.org Plugins API to fetch plugin information.  
 - It sends the plugin slug when requesting data.
 - More information: https://wordpress.org/about/privacy/

2. To the European Commission VAT number validation API (VIES) and EORI number validation API.
 - It sends the country and VAT number — VIES validation —.
 - It sends the EORI number.
 - More information: https://commission.europa.eu/privacy-policy-websites-managed-european-commission_es

3. To the UK Government EORI number validation API.
 - It sends the EORI number.
 - More information: https://www.gov.uk/help/privacy-notice

4. To the VatApp EORI number validation API.
 - It sends the EORI number.
 - More information: https://vatapp.net/privacy-policy
