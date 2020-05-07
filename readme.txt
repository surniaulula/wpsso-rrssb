=== Ridiculously Responsive Social Sharing Buttons (RRSSB) | WPSSO Add-on ===
Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons
Plugin Slug: wpsso-rrssb
Text Domain: wpsso-rrssb
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://surniaulula.github.io/wpsso-rrssb/assets/
Tags: responsive, share buttons, social widget, social media, woocommerce, facebook, google, twitter, pinterest, linkedin, whatsapp, bbpress, buddypress
Contributors: jsmoriss
Requires PHP: 5.6
Requires At Least: 5.2
Tested Up To: 5.4.1
WC Tested Up To: 4.1.0
Stable Tag: 4.2.0

Ridiculously Responsive (SVG) Social Sharing Buttons for your Content, Excerpts, CSS Sidebar, Widget, Shortcode, Templates, and Editor.

== Description ==

<p style="margin:0;"><img class="readme-icon" src="https://surniaulula.github.io/wpsso-rrssb/assets/icon-256x256.png"></p>

*Ridiculously Responsive Social Sharing Buttons* automatically resize to their container width, so they always look great on any device (phone, tablet, laptop, etc.), no matter its size or resolution &mdash; including mobile, Retina, and high-PPI displays!

**Add Ridiculously Responsive Social Sharing Buttons to:**

Posts, Pages, custom post types, bbPress, BuddyBlog posts, BuddyPress activities, WooCommerce product pages, and much more.</p>

**Add Ridiculously Responsive Social Sharing Buttons in:**

The content, excerpt, widget, CSS sidebar, shortcodes, templates, and *admin editor page* &mdash; so you can share directly from the admin editor page!

**Do you use bbPress, BuddyPress, BuddyBlog, or WooCommerce?**

The WPSSO RRSSB add-on includes special integration modules to add social sharing buttons to even more locations!

<h3>Users Love the WPSSO RRSSB Add-on</h3>

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; &mdash; "Fast and sleek! These buttons are the bees knees for real. They look great across all platforms, play nice with w3-total-cache and doesn’t impact my 100/100 pagespeed score." - [renoduck](https://wordpress.org/support/topic/fast-and-sleek/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; &mdash; "FANTASTIC! This plugin is one of my favorites! This plugin makes it SOOO simple to add social sharing to wordpress posts!" - [mikegoubeaux](https://wordpress.org/support/topic/fantastic-1214/)

&#x2605;&#x2605;&#x2605;&#x2605;&#x2605; &mdash; "These buttons are great, work on mobile phones as well as desktops, and they load super fast!" - [undergroundnetwork](https://wordpress.org/support/topic/best-sharing-buttons-out-there/)

<h3>WPSSO RRSSB Standard Features</h3>

* Extends the features of the WPSSO Core plugin.

* Include Ridiculously Responsive Social Sharing Buttons in multiple locations:

	* Above and/or below your content, excerpt text, bbPress single, BuddyBlog posts, BuddyPress activities, and WooCommerce short description.
	* In an admin editing page metabox &ndash; including media, product pages, and custom post types.
	* In a CSS / javascript sidebar.
	* In a WordPress sharing widget.
	* A shortcode in your content or excerpt.
	* A function in your theme's template(s).

* Each of these social sharing buttons can be configured and styled individually:

	* Email
	* Facebook
	* Google+
	* LinkedIn
	* Pinterest
	* Pocket
	* Reddit
	* Tumblr
	* Twitter
	* VK
	* WhatsApp (for mobile devices)

* Options to include / exclude social sharing buttons by post type.

* Include / exclude individual social sharing buttons based on the viewing device (desktop and/or mobile).

* Automatically exclude buttons from [Accelerated Mobile Pages (AMP)](https://wordpress.org/plugins/amp/) plugin pages.

* A built-in stylesheet editor allows you to easily fine-tune the CSS for each social sharing button location (content, excerpt, shortcode, widget, etc.).

* Uses the CSS and JS libraries provided by the <a href="https://github.com/kni-labs/rrssb">RRSSB project on GitHub</a>.

<h3>WPSSO Core Plugin Required</h3>

WPSSO Ridiculously Responsive Social Sharing Buttons (aka WPSSO RRSSB) is an add-on for the [WPSSO Core plugin](https://wordpress.org/plugins/wpsso/).

== Installation ==

<h3 class="top">Install and Uninstall</h3>

* [Install the WPSSO RRSSB add-on](https://wpsso.com/docs/plugins/wpsso-rrssb/installation/install-the-plugin/).
* [Uninstall the WPSSO RRSSB add-on](https://wpsso.com/docs/plugins/wpsso-rrssb/installation/uninstall-the-plugin/).

== Frequently Asked Questions ==

<h3 class="top">Frequently Asked Questions</h3>

* None.

<h3>Notes and Documentation</h3>

* [RRSSB Shortcode for Sharing Buttons](https://wpsso.com/docs/plugins/wpsso-rrssb/notes/rrssb-shortcode/)

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

<h3>Standard Version Repositories</h3>

* [GitHub](https://surniaulula.github.io/wpsso-rrssb/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/wpsso-rrssb/)

<h3>Changelog / Release Notes</h3>

**Version 4.3.0-dev.3 (2020/05/07)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored the required plugin check to (optionally) check the class name and a version constant.
	* Updated clear cache method call for WPSSO Core v7.4.0.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v5.2.
	* WPSSO Core v7.4.0-dev.3.

**Version 4.2.0 (2020/04/28)**

* **New Features**
	* None.
* **Improvements**
	* Updated the minimum required WordPress version from 4.2 to 5.2. 
	* Updated the RRSSB jQuery to use `jQuery(document).ready()` by default (faster) and `jQuery(window).load()` in the block editor.
* **Bugfixes**
	* Fixed the default sidebar stylesheet to use the CSS class instead of the CSS id.
* **Developer Notes**
	* Re-added the 'wpsso-rrssb-sidebar' CSS id to the sidebar container for backwards compatibility.
	* Moved the sidebar HTML from 'wp_footer' to 'wp_body_open'.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v5.2.
	* WPSSO Core v7.3.0.

**Version 4.1.0 (2020/04/17)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added support for the new 'is_public' key in the `$mod` array (available since WPSSO Core v7.0.0).
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.2.
	* WPSSO Core v7.0.1.

**Version 4.0.0 (2020/04/06)**

Please note that all default button CSS styles will be reloaded with this version. If you have made custom CSS changes in the SSO &gt; Responsive Styles settings page, please save your custom CSS before updating to this version.

* **New Features**
	* None.
* **Improvements**
	* Updated "Requires At Least" to WordPress v4.2.
	* Removed the "Share Buttons" metabox from admin editing pages and moved "Admin Edit" buttons to the Document SSO metabox footer.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Refactored WPSSO Core active and minimum version dependency checks.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.2.
	* WPSSO Core v6.28.0.

**Version 3.6.0 (2020/03/27)**

* **New Features**
	* None.
* **Improvements**
	* Removed the "Pinterest Sharing Image" size and its options. The Pinterest share button now uses the Pinterest image size from WPSSO Core v6.26.0.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed 'custom_meta' hooks to 'document_meta' for WPSSO Core v6.26.0.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v4.0.
	* WPSSO Core v6.27.1.

== Upgrade Notice ==

= 4.3.0-dev.3 =

(2020/05/07) Refactored the required plugin check to (optionally) check the class name and a version constant.

= 4.2.0 =

(2020/04/28) Updated the minimum required WordPress version from 4.2 to 5.2. Fixed the default sidebar stylesheet to use the CSS class instead of the CSS id.

= 4.1.0 =

(2020/04/17) Added support for the new 'is_public' key in the `$mod` array (available since WPSSO Core v7.0.0).

= 4.0.0 =

(2020/04/06) Please note that all default button CSS styles will be reloaded with this version. If you have made custom CSS changes in the SSO &gt; Responsive Styles settings page, please save your custom CSS before updating to this version.

= 3.6.0 =

(2020/03/27) Removed the "Pinterest Sharing Image" size and its options.

