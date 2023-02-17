=== WPSSO Ridiculously Responsive Social Sharing Buttons (RRSSB) ===
Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons
Plugin Slug: wpsso-rrssb
Text Domain: wpsso-rrssb
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-rrssb/assets/
Tags: responsive, share buttons, social widget, social media, woocommerce, facebook, google, twitter, pinterest, linkedin, whatsapp
Contributors: jsmoriss
Requires Plugins: wpsso
Requires PHP: 7.2
Requires At Least: 5.4
Tested Up To: 6.1.1
WC Tested Up To: 7.4.0
Stable Tag: 10.5.0

Ridiculously Responsive (SVG) Social Sharing Buttons for your content, excerpts, CSS sidebar, widget, shortcode, templates, and editor.

== Description ==

<!-- about -->

*Ridiculously Responsive Social Sharing Buttons* automatically resize to their container width, so they always look great on any device (phone, tablet, laptop, etc.), no matter its size or resolution - including mobile, Retina, and high-PPI displays!

<!-- /about -->

<h3>Users Love the WPSSO RRSSB Add-on</h3>

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "Fast and sleek! These buttons are the bees knees for real. They look great across all platforms, play nice with w3-total-cache and doesn’t impact my 100/100 pagespeed score." - [renoduck](https://wordpress.org/support/topic/fast-and-sleek/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "FANTASTIC! This plugin is one of my favorites! This plugin makes it SOOO simple to add social sharing to wordpress posts!" - [mikegoubeaux](https://wordpress.org/support/topic/fantastic-1214/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "These buttons are great, work on mobile phones as well as desktops, and they load super fast!" - [undergroundnetwork](https://wordpress.org/support/topic/best-sharing-buttons-out-there/)

<h3>WPSSO RRSSB Add-on Features</h3>

Include responsive social sharing buttons in many locations:

* Above and/or below your content, excerpt text, WooCommerce short description, and WooCommerce add to cart.
* In the admin editing page Document SSO metabox.
* In a CSS / javascript sidebar.
* In a WordPress sharing widget.
* From a shortcode in your content or excerpt.
* From a function in your theme's template(s).

Provides UTM medium, UTM source, and UTM campaign values for each social sharing button.

A built-in stylesheet editor allows you to easily fine-tune the CSS for each social sharing button location (content, excerpt, shortcode, widget, etc.).

Includes CSS and JS libraries provided by the <a href="https://github.com/kni-labs/rrssb">RRSSB project on GitHub</a>.

<h3>WPSSO Core Required</h3>

WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/), which provides complete structured data for WordPress to present your content at its best on social sites and in search results – no matter how URLs are shared, reshared, messaged, posted, embedded, or crawled.

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO RRSSB add-on](https://wpsso.com/docs/plugins/wpsso-rrssb/installation/install-the-plugin/).
* [Uninstall the WPSSO RRSSB add-on](https://wpsso.com/docs/plugins/wpsso-rrssb/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

<h3 class="top">Frequently Asked Questions</h3>

* None.

<h3>Notes and Documentation</h3>

* [RRSSB Shortcode for Social Sharing Buttons](https://wpsso.com/docs/plugins/wpsso-rrssb/notes/rrssb-shortcode/)

== Screenshots ==

01. WPSSO RRSSB example showing buttons enabled in the CSS sidebar, content text, and widget.
02. WPSSO RRSSB example showing a WooCommerce product page with buttons in the CSS sidebar, short and long product descriptions.

== Changelog ==

<h3 class="top">Version Numbering</h3>

Version components: `{major}.{minor}.{bugfix}[-{stage}.{level}]`

* {major} = Major structural code changes and/or incompatible API changes (ie. breaking changes).
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Edition Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-rrssb/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/wpsso-rrssb/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium edition customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<p><strong>WPSSO Core Standard edition users (ie. the plugin hosted on WordPress.org) have access to <a href="https://wordpress.org/plugins/wpsso-rrssb/advanced/">the latest development version under the Advanced Options section</a>.</strong></p>

<h3>Changelog / Release Notes</h3>

**Version 10.6.0-dev.2 (2023/02/17)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the 'wpssorrssb_status_std_features' filter to 'wpssorrssb_features_status'.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.4.
	* WPSSO Core v15.4.0-dev.2.

**Version 10.5.0 (2023/01/27)**

* **New Features**
	* None.
* **Improvements**
	* Updated the condition to force the "Add Hidden Image for Pinterest" option only when the Pinterest button is enabled for the content.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.4.
	* WPSSO Core v14.7.0.

**Version 10.4.1 (2023/01/26)**

* **New Features**
	* None.
* **Improvements**
	* Added compatibility declaration for WooCommerce HPOS.
	* Updated the minimum WordPress version from v5.2 to v5.4.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `WpssoAbstractAddOn` library class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.4.
	* WPSSO Core v14.7.0.

**Version 10.4.0 (2023/01/20)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `SucomAbstractAddOn` common library class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v14.5.0.

**Version 10.3.0 (2022/12/28)**

* **New Features**
	* None.
* **Improvements**
	* Update for the `.wpsso-rrssb-content` default CSS.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v14.0.0.

**Version 10.2.0 (2022/08/24)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the 'std' library folder to 'integ' for WPSSO Core v13.0.0.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v13.0.0.

**Version 10.1.0 (2022/05/17)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added support for the new `SucomUtil::sanitize_twitter_name()` method in WPSSO Core.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v12.3.0.

**Version 10.0.0 (2022/03/26)**

Removed support for bbPress, BuddyPress, and BuddyBlog.

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v12.0.0.

**Version 9.4.1 (2022/03/07)**

Maintenance release.

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v11.5.0.

**Version 9.4.0 (2022/02/26)**

* **New Features**
	* None.
* **Improvements**
	* Updated the `WpssoOpenGraph->get_media_info()` method to `WpssoMedia->get_media_info()` for WPSSO Core v11.2.0.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v11.0.0.

**Version 9.3.0 (2022/02/17)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `WpssoPage` class `get_title()`, `get_description()`, and `get_caption()` method arguments for WPSSO Core v11.0.0.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v11.0.0.

**Version 9.2.0 (2022/02/10)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* Refactored all share button `get_html()` methods to optimize the `WpssoUtilInline->replace_variables()` arguments.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v10.3.0.

**Version 9.1.0 (2022/02/05)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Removed the `$read_cache` argument from `WpssoPage` methods for WPSSO Core v10.1.0.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v10.1.0.

**Version 9.0.0 (2022/02/02)**

* **New Features**
	* None.
* **Improvements**
	* Moved the buttons Call To Action option text from the add-on config array to a default translatable string.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Replaced call to `WpssoUtil->add_post_type_names()` by `WpssoOptions->set_default_text()`.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v10.0.0.

**Version 8.3.0 (2022/01/19)**

* **New Features**
	* None.
* **Improvements**
	* Renamed the ".wpsso-rrssb-buttons-cta" CSS class to ".rrssb-buttons-cta".
	* Renamed the Document SSO &gt; Share Buttons tab to Edit Share Buttons.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the lib/abstracts/ folder to lib/abstract/.
	* Renamed the `SucomAddOn` class to `SucomAbstractAddOn`.
	* Renamed the `WpssoAddOn` class to `WpssoAbstractAddOn`.
	* Renamed the `WpssoWpMeta` class to `WpssoAbstractWpMeta`.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v9.14.0.

**Version 8.2.0 (2021/12/16)**

* **New Features**
	* None.
* **Improvements**
	* Added a 'wpsso_upgraded_options' filter hook to reload the default CSS for old versions.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v9.12.0.

**Version 8.1.1 (2021/11/16)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the `SucomAddOn->get_missing_requirements()` method.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.2.
	* WPSSO Core v9.8.0.

**Version 8.1.0 (2021/11/02)**

* **New Features**
	* None.
* **Improvements**
	* Added support for the new `WpssoUtilInline` class methods in WPSSO Core v9.5.0.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.2.
	* WPSSO Core v9.5.0.

**Version 8.0.0 (2021/10/09)**

* **New Features**
	* Added a new "Position in WC Add to Cart" option.
	* Added a new "WC Add to Cart" option for each social sharing button (enabled by default).
	* Added a new "WooCommerce" tab in the SSO &gt; Responsive Styles settings page.
	* Removed the "Woo Short" tab in the SSO &gt; Responsive Styles settings page.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* None.
* **Requires At Least**
	* PHP v7.0.
	* WordPress v5.2.
	* WPSSO Core v9.1.0.

== Upgrade Notice ==

= 10.6.0-dev.2 =

(2023/02/17) Renamed the 'wpssorrssb_status_std_features' filter to 'wpssorrssb_features_status'.

= 10.5.0 =

(2023/01/27) Updated the condition to force the "Add Hidden Image for Pinterest" option only when the Pinterest button is enabled for the content.

= 10.4.1 =

(2023/01/26) Added compatibility declaration for WooCommerce HPOS. Updated the minimum WordPress version from v5.2 to v5.4.

= 10.4.0 =

(2023/01/20) Updated the `SucomAbstractAddOn` common library class.

= 10.3.0 =

(2022/12/28) Update for the `.wpsso-rrssb-content` default CSS.

= 10.2.0 =

(2022/08/24) Renamed the 'std' library folder to 'integ' for WPSSO Core v13.0.0.

= 10.1.0 =

(2022/05/17) Added support for the new `SucomUtil::sanitize_twitter_name()` method in WPSSO Core.

= 10.0.0 =

(2022/03/26) Removed support for bbPress, BuddyPress, and BuddyBlog.

= 9.4.1 =

(2022/03/07) Maintenance release.

= 9.4.0 =

(2022/02/26) Updated the `WpssoOpenGraph->get_media_info()` method to `WpssoMedia->get_media_info()` for WPSSO Core v11.2.0.

= 9.3.0 =

(2022/02/17) Updated `WpssoPage` class method arguments for WPSSO Core v11.0.0.

= 9.2.0 =

(2022/02/10) Refactored all share button `get_html()` methods.

= 9.1.0 =

(2022/02/05) Removed the `$read_cache` argument from `WpssoPage` methods for WPSSO Core v10.1.0.

= 9.0.0 =

(2022/02/02) Moved the buttons Call To Action option text from the add-on config array to a default translatable string.

= 8.3.0 =

(2022/01/19) Renamed the ".wpsso-rrssb-buttons-cta" CSS class to ".rrssb-buttons-cta". Renamed the Document SSO &gt; Share Buttons tab. Renamed the lib/abstracts/ folder and its classes.

= 8.2.0 =

(2021/12/16) Added a 'wpsso_upgraded_options' filter hook to reload the default CSS for old versions.

= 8.1.1 =

(2021/11/16) Refactored the `SucomAddOn->get_missing_requirements()` method.

= 8.1.0 =

(2021/11/02) Added support for the new `WpssoUtilInline` class methods in WPSSO Core v9.5.0.

= 8.0.0 =

(2021/10/09) Added a new "WC Add to Cart" option for each social sharing button (enabled by default).

