=== WC - APG NIF/CIF/NIE Field ===
Contributors: artprojectgroup
Donate link: https://artprojectgroup.es/tienda/donacion
Tags: woocommerce, commerce, e-commerce, commerce, shop, virtual shop, nif, cif, nie, shipping form, billing form, wooCommerce pdf invoices & packing slips, wpml, eori, vies
Requires at least: 3.8
Tested up to: 6.3
Stable tag: 2.1.0.1
WC requires at least: 2.4
WC tested up to: 7.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add to WooCommerce a NIF/CIF/NIE field.

== Description ==
**IMPORTANT: *WC - APG NIF/CIF/NIE field* required WooCommerce 2.4.0 or higher and the <a href="http://php.net/manual/en/class.soapclient.php">SoapClient</a> PHP class.**

**WC - APG NIF/CIF/NIE field** add to your WooCommerce shop a new NIF/CIF/NIE field to all billing and shipping forms available to admin and customer in WooCommerce.

= Features =
* You can require the NIF/CIF/NIE field in billing form.
* You can require the NIF/CIF/NIE field in shipping form.
* You can customize the priority (position) of the field.
* You can validate the NIF/CIF/NIE field.
* You can validate the VIES VAT number field to exempt the taxes.
* You can validate the EORI field to prevent the sale if a valid number is not entered.
* You can select the country(ies) where the EORI field will be validated.
* You can customize the label and placeholder of the NIF/CIF/NIE, VIES VAT number or EORI field.
* You can customize the error message of the field NIF/CIF/NIE, VIES VAT number or EORI.
* Add and require phone and email fields in shipping form.
* You can remove the phone and email fields from the default address.
* You can display and customize an error message for the billing form using the `apg_nif_display_error_message` and `apg_nif_error_message` filters.
* 100% compatible with [WooCommerce PDF Invoices & Packing Slips](https://es.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/).
* 100% compatible with [WPML](https://wpml.org/?aid=80296&affiliate_key=m66Ss5ps0xoS).

= Translations =
* Español ([**Art Project Group**](https://artprojectgroup.es/)).
* English ([**Art Project Group**](https://artprojectgroup.es/)).

= Technical support =
**Art Project Group** offers [**Technical support**](https://artprojectgroup.es/tienda/ticket-de-soporte) to configure or install ***WC - APG NIF/CIF/NIE field***.

= Origin =
**WC - APG NIF/CIF/NIE field** has been programmed from code published on [*¿Cómo añadir un campo NIF o CIF con validación a WooCommerce 2.4?*](https://artprojectgroup.es/como-anadir-un-campo-nif-o-cif-con-validacion-a-woocommerce-2-4) from [Art Project Group](https://artprojectgroup.es/).

= More information =
On our official website you can learn more about [**WC - APG NIF/CIF/NIE field**](https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-nifcifnie-field).

= Comments =
Don’t forget to leave us your comment on:

* [WC - APG NIF/CIF/NIE field](https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-nifcifnie-field) on Art Project Group.
* [Art Project Group](https://www.facebook.com/artprojectgroup) on Facebook.
* [@artprojectgroup](https://twitter.com/artprojectgroup) on Twitter.

= More plugins =
Remember that you can find more [plugins for WordPress](https://artprojectgroup.es/plugins-para-wordpress) and more [plugins for WooCommerce](https://artprojectgroup.es/plugins-para-woocommerce) on [Art Project Group](https://artprojectgroup.es) and our profile on [WordPress](https://profiles.wordpress.org/artprojectgroup/).

= GitHub =
You can follow the development of this plugin on [Github](https://github.com/artprojectgroup/wc-apg-nifcifnie-field).

== Installation ==
1. You can:
 * Upload the `wc-apg-nifcifnie-field` folder to `/wp-content/plugins/` directory via FTP. 
 * Upload the full ZIP file via *Plugins -> Add New -> Upload* on your WordPress Administration Panel.
 * Search **WC - APG NIF/CIF/NIE field** in the search engine available on *Plugins -> Add New* and press *Install Now* button.
2. Activate plugin through *Plugins* menu on WordPress Administration Panel.
3. Set up plugin on *WooCommerce -> NIF/CIF/NIE field* or through *Settings* button.
4. Ready, now you can enjoy it, and if you like it and find it useful, make a [*donation*](https://artprojectgroup.es/tienda/donacion).

== Frequently asked questions ==
= How do you set up? =
The plugin settings is very simple, you just must indicate if you want NIF/CIF/NIE field is validated or not.

= Support =
If you need help to configuring or installing **WC - APG NIF/CIF/NIE field**, **Art Project Group** offers its service [**Technical Support**](https://artprojectgroup.es/tienda/ticket-de-soporte). 

*In any case **Art Project Group** provides any kind of free technical support.*

== Screenshots ==
1. Screenshot of WC - APG NIF/CIF/NIE field.
2. Screenshot of WC - APG NIF/CIF/NIE field. Billing and shipping forms.

== Changelog ==
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
= 2.1.0.1 =
* Small fixes.

== Translations ==
* *English*: by [**Art Project Group**](https://artprojectgroup.es/) (default language).
* *Español*: por [**Art Project Group**](https://artprojectgroup.es/).

== Support ==
Since **WC - APG NIF/CIF/NIE field** is totally free, **Art Project Group** only provides payment [**Technical Support**](https://artprojectgroup.es/tienda/ticket-de-soporte) service. In any case **Art Project Group** provide any kind of free technical support.

== Donation ==
Did you liked and you have proved useful **WC - APG NIF/CIF/NIE field** on your website? We would appreciate a [small donation](https://artprojectgroup.es/tienda/donacion) that will help us to continue improving this plugin and create more plugins totally free for the entire WordPress community.

== Thanks ==
* To all that use it.
* All that you help to improve it.
* All you made donations.
* All that you encourage us with your comments.

Thank you very much to all!