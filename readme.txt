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
Tested Up To: 5.5
WC Tested Up To: 4.5.2
Stable Tag: 4.6.1

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

**Version 4.6.2-b.2 (2020/09/15)**

* **New Features**
	* None.
* **Improvements**
	* Updated the French plugin translations.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Moved extracted translation strings from lib/gettext-*.php files to a new gettext/ folder.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v5.2.
	* WPSSO Core v8.5.0-b.2.

**Version 4.6.1 (2020/09/11)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Added a new `WpssoRrssbStyle::admin_enqueue_styles()` method to add RRSSB specific inline styles for 'sucom-settings-table'.
* **Requires At Least**
	* PHP v5.6.
	* WordPress v5.2.
	* WPSSO Core v8.4.1.

== Upgrade Notice ==

= 4.6.2-b.2 =

(2020/09/15) Updated the French plugin translations.

= 4.6.1 =

(2020/09/11) Added a new method to add RRSSB specific inline styles for 'sucom-settings-table'.

