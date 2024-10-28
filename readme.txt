=== WPSSO Ridiculously Responsive Social Sharing Buttons (RRSSB) ===
Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons
Plugin Slug: wpsso-rrssb
Text Domain: wpsso-rrssb
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-rrssb/assets/
Tags: responsive, share buttons, social widget, social media, woocommerce
Contributors: jsmoriss
Requires Plugins: wpsso
Requires PHP: 7.4.33
Requires At Least: 5.9
Tested Up To: 6.7.0
WC Tested Up To: 9.3.3
Stable Tag: 11.7.0

Ridiculously Responsive (SVG) Social Sharing Buttons for your content, excerpts, CSS sidebar, widget, shortcode, templates, and editor.

== Description ==

<!-- about -->

*Ridiculously Responsive Social Sharing Buttons* automatically resize to their container width, so they always look great on any device (phone, tablet, laptop, etc.), no matter its size or resolution - including mobile, Retina, and high-PPI displays!

<!-- /about -->

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

WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/), which creates extensive and complete structured data to present your content at its best for social sites and search results – no matter how URLs are shared, reshared, messaged, posted, embedded, or crawled.

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

**Version 11.7.0 (2024/08/25)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Changed the main instantiation action hook from 'init_objects' to 'init_objects_preloader'.
* **Requires At Least**
	* PHP v7.4.33.
	* WordPress v5.9.
	* WPSSO Core v18.10.0.

== Upgrade Notice ==

= 11.7.0 =

(2024/08/25) Changed the main instantiation action hook from 'init_objects' to 'init_objects_preloader'.

