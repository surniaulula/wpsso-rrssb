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
Requires PHP: 7.2.34
Requires At Least: 5.5
Tested Up To: 6.4.0
WC Tested Up To: 8.2.1
Stable Tag: 10.7.0

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

A stylesheet editor allows you to easily fine-tune the CSS for each social sharing button location (content, excerpt, shortcode, widget, etc.).

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

**Version 10.8.0-dev.10 (2021/11/04)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the settings page load process.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v16.7.0-dev.10.

**Version 10.7.0 (2023/08/09)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated filter hook names for WPSSO Core v15.19.0:
		* Renamed the 'save_setting_options' filter hook to 'save_settings_options'.
		* Renamed the 'load_setting_page_*' filter hooks to 'load_settings_page_*'.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v15.19.0.

**Version 10.6.0 (2023/02/20)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the 'wpssorrssb_status_std_features' filter to 'wpssorrssb_features_status'.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.
	* WPSSO Core v15.4.0.

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
	* WordPress v5.5.
	* WPSSO Core v14.7.0.

**Version 10.4.1 (2023/01/26)**

* **New Features**
	* None.
* **Improvements**
	* Added compatibility declaration for WooCommerce HPOS.
	* Updated the minimum WordPress version from v5.2 to v5.5.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Updated the `WpssoAbstractAddOn` library class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.5.
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

== Upgrade Notice ==

= 10.8.0-dev.10 =

(2021/11/04) Refactored the settings page load process.

= 10.7.0 =

(2023/08/09) Updated filter hook names for WPSSO Core v15.19.0.

= 10.6.0 =

(2023/02/20) Renamed the 'wpssorrssb_status_std_features' filter to 'wpssorrssb_features_status'.

= 10.5.0 =

(2023/01/27) Updated the condition to force the "Add Hidden Image for Pinterest" option only when the Pinterest button is enabled for the content.

= 10.4.1 =

(2023/01/26) Added compatibility declaration for WooCommerce HPOS. Updated the minimum WordPress version from v5.2 to v5.5.

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

