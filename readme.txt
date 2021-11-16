=== WPSSO Ridiculously Responsive Social Sharing Buttons (RRSSB) ===
Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons
Plugin Slug: wpsso-rrssb
Text Domain: wpsso-rrssb
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-rrssb/assets/
Tags: responsive, share buttons, social widget, social media, woocommerce, facebook, google, twitter, pinterest, linkedin, whatsapp, bbpress, buddypress
Contributors: jsmoriss
Requires PHP: 7.2
Requires At Least: 5.2
Tested Up To: 5.8.2
WC Tested Up To: 5.9.0
Stable Tag: 8.1.0

Ridiculously Responsive (SVG) Social Sharing Buttons for your content, excerpts, CSS sidebar, widget, shortcode, templates, and editor.

== Description ==

<!-- about -->

*Ridiculously Responsive Social Sharing Buttons* automatically resize to their container width, so they always look great on any device (phone, tablet, laptop, etc.), no matter its size or resolution - including mobile, Retina, and high-PPI displays!

**Add responsive social sharing buttons to:**

Posts, pages, custom post types, bbPress, BuddyBlog posts, BuddyPress activities, WooCommerce product pages, and much more.

**Add responsive social sharing buttons in:**

The content, excerpt, widget, CSS sidebar, shortcodes, templates, and *admin editor page* (so you can quickly share directly from the admin editor page).

**Add UTM medium, source, and campaign values per share button.**

<!-- /about -->

<h3>Users Love the WPSSO RRSSB Add-on</h3>

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "Fast and sleek! These buttons are the bees knees for real. They look great across all platforms, play nice with w3-total-cache and doesn’t impact my 100/100 pagespeed score." - [renoduck](https://wordpress.org/support/topic/fast-and-sleek/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "FANTASTIC! This plugin is one of my favorites! This plugin makes it SOOO simple to add social sharing to wordpress posts!" - [mikegoubeaux](https://wordpress.org/support/topic/fantastic-1214/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; - "These buttons are great, work on mobile phones as well as desktops, and they load super fast!" - [undergroundnetwork](https://wordpress.org/support/topic/best-sharing-buttons-out-there/)

<h3>WPSSO RRSSB Add-on Features</h3>

Extends the features of the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/) (required plugin).

Include responsive social sharing buttons in many locations:

* Above and/or below your content, excerpt text, bbPress single, BuddyBlog posts, BuddyPress activities, WooCommerce short description, and WooCommerce add to cart.
* In an admin editing page metabox &ndash; including media, product pages, and custom post types.
* In a CSS / javascript sidebar.
* In a WordPress sharing widget.
* A shortcode in your content or excerpt.
* A function in your theme's template(s).

Provides UTM medium, UTM source, and UTM campaign values for each social sharing button.

A built-in stylesheet editor allows you to easily fine-tune the CSS for each social sharing button location (content, excerpt, shortcode, widget, etc.).

Uses the CSS and JS libraries provided by the <a href="https://github.com/kni-labs/rrssb">RRSSB project on GitHub</a>.

<h3>WPSSO Core Required</h3>

WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/).

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

* {major} = Major structural code changes / re-writes or incompatible API changes.
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Standard Edition Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-rrssb/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/wpsso-rrssb/)

<h3>Development Version Updates</h3>

<p><strong>WPSSO Core Premium customers have access to development, alpha, beta, and release candidate version updates:</strong></p>

<p>Under the SSO &gt; Update Manager settings page, select the "Development and Up" (for example) version filter for the WPSSO Core plugin and/or its add-ons. Save the plugin settings and click the "Check for Plugin Updates" button to fetch the latest version information. When new development versions are available, they will automatically appear under your WordPress Dashboard &gt; Updates page. You can always reselect the "Stable / Production" version filter at any time to reinstall the latest stable version.</p>

<h3>Changelog / Release Notes</h3>

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

= 8.1.1 =

(2021/11/16) Refactored the `SucomAddOn->get_missing_requirements()` method.

= 8.1.0 =

(2021/11/02) Added support for the new `WpssoUtilInline` class methods in WPSSO Core v9.5.0.

= 8.0.0 =

(2021/10/09) Added a new "WC Add to Cart" option for each social sharing button (enabled by default).

