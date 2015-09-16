=== WPSSO Ridiculously Responsive Social Sharing Buttons + WooCommerce bbPress BuddyPress ===
Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB)
Plugin Slug: wpsso-rrssb
Contributors: jsmoriss
Donate Link: https://wpsso.com/
Tags: wpsso, facebook, google+, twitter, pinterest, linkedin, svg, retina, bbpress, buddypress, shorten, woocommerce, widget, shortcode
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Requires At Least: 3.1
Tested Up To: 4.3.1
Stable Tag: 1.0.5

WPSSO extension to add Ridiculously Responsive (SVG) Social Sharing Buttons in your content, excerpts, CSS sidebar, widget, shortcode, etc.

== Description ==

<p><img src="https://surniaulula.github.io/wpsso-rrssb/assets/icon-256x256.png" width="256" height="256" style="width:33%;min-width:128px;max-width:256px;float:left;margin:0 40px 20px 0;" />Add awesome and <strong>Ridiculously Responsive Social Sharing Buttons</strong> to posts / pages, custom post types, <a href="https://wordpress.org/plugins/bbpress/">bbPress</a>, <a href="https://wordpress.org/plugins/buddypress/">BuddyPress</a>, <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> product pages, and many more. The sharing buttons can be included in your content, excerpts, in a floating CSS sidebar, in a widget, from shortcodes, and on admin editing pages.</p>


<blockquote>
<p>The Scalable Vector Graphics (SVG) used by WPSSO RRSSB resize automatically to their container size, so they always look great from any device (phone, tablet, laptop, etc.), including high resolution retina displays.</p>
</blockquote>

<p>WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB) works in conjunction with the <a href="https://wordpress.org/plugins/wpsso/">WordPress Social Sharing Optimization (WPSSO)</a> plugin, extending its features with additional settings pages, tabs, and options to provide <strong>Ridiculously Responsive Social Sharing Buttons</strong>. Using WPSSO as its framework, WPSSO RRSSB provides customized and accurate information about your content to social websites.</p>

= Quick List of Features =

**Free / Basic Version**

WPSSO RRSSB allows you to include a selection of <strong>Ridiculously Responsive Social Sharing Buttons</strong> in multiple locations:

* Above and/or below your content and/or excerpt text.
* On admin editing pages, including media, product pages, and custom post types.
* In a WordPress sharing widget.
* In a floating CSS sidebar.
* A shortcode within your content and/or excerpt.
* A function in your theme's template(s).

Each of these social sharing buttons can be enabled, configured, and styled individually:

* Email
* Facebook
* Google+
* Twitter
* Pinterest
* LinkedIn
* Reddit
* Pocket
* Tumblr
* VK

A built-in stylesheet editor allows you to fine-tune the CSS for each social sharing button location easily (content, excerpt, shortcode, widget, etc.).

<blockquote>
<p>Download the Free version from <a href="http://surniaulula.github.io/wpsso-rrssb/">GitHub</a> or <a href="https://wordpress.org/plugins/wpsso-rrssb/">WordPress.org</a>. WPSSO RRSSB uses the CSS and JS libraries provided by the <a href="https://github.com/kni-labs/rrssb">RRSSB project on GitHub</a>.</p>
</blockquote>

**Pro / Power-User Version**

The [Pro version of WPSSO RRSSB](http://wpsso.com/extend/plugins/wpsso-rrssb/) includes a number of additional options and features:

* URL shortening with Bitly or Google for URLs in Tweets.
* Additional sharing button locations and CSS styles for:
	* [bbPress](https://wordpress.org/plugins/bbpress/)
	* [BuddyPress](https://wordpress.org/plugins/buddypress/)
	* [WooCommerce](https://wordpress.org/plugins/woocommerce/)

<blockquote>
<p>The WordPress Social Sharing Optimization (WPSSO) plugin is required to use the WPSSO RRSSB extension. You can use the Free version of WPSSO RRSSB with <em>both</em> the Free and Pro versions of WPSSO, but <a href="http://wpsso.com/extend/plugins/wpsso-rrssb/">WPSSO RRSSB Pro</a> requires the use of <a href="http://wpsso.com/extend/plugins/wpsso/">WPSSO Pro</a> as well. <a href="http://wpsso.com/extend/plugins/wpsso-ssb/">Purchase the WPSSO RRSSB Pro extension</a> (includes a <em>No Risk 30 Day Refund Policy</em>).</p>
</blockquote>

= Fastest Performance =

WPSSO and WPSSO RRSSB are *fast and coded for performance*, making full use of all available caching techniques (persistent / non-persistent object and disk caching).

== Installation ==

= Install and Uninstall =

<ul>
	<li><a href="http://wpsso.com/codex/plugins/wpsso-rrssb/installation/install-the-plugin/">Install the Plugin</a></li>
	<li><a href="http://wpsso.com/codex/plugins/wpsso-rrssb/installation/uninstall-the-plugin/">Uninstall the Plugin</a></li>
</ul>

== Frequently Asked Questions ==

== Other Notes ==

= Additional Documentation =

== Screenshots ==

01. An example showing **Ridiculously Responsive Social Sharing Buttons** enabled (with the default WPSSO RRSSB stylesheet) in the CSS sidebar, content text, and widget.
02. An example showing a WooCommerce product page with **Ridiculously Responsive Social Sharing Buttons** in the CSS sidebar, short and long product descriptions.
03. The 'Sharing Buttons' tab available in the 'Social Settings' metabox allows you to fine-tune the text used by some social sharing websites.
04. The 'Sharing Buttons' settings page.
05. The 'Sharing Styles' settings page.

== Changelog ==

= Free / Basic Version Repository =

* [GitHub](https://github.com/SurniaUlula/wpsso-ssb)
* [WordPress.org](https://wordpress.org/plugins/wpsso-ssb/developers/)

= Version 1.0.5 =

* **New Features**
	* *None*
* **Improvements**
	* *None*
* **Bugfixes**
	* *None*
* **Developer Notes**
	* Added a WpssoRrssbRegister class with `WpssoUtil::save_time()` calls during activation to save install / activation / update timestamps.

= Version 1.0.4 (2015/09/03) =

* **New Features**
	* *None*
* **Improvements**
	* Added Free feature summary in side metabox on settings pages.
* **Bugfixes**
	* *None*
* **Developer Notes**
	* Updated the tooltip and info message filter names for WPSSO v3.8.
	* Removed the WPSSO RRSSB specific 'installed_version' and 'ua_plugin' filters.
	* Improved the setting of constants with new `set_variable_constants()` and `get_variable_constants()` methods in the WpssoRrssbConfig class.

= Version 1.0.3 (2015/08/26) =

* **New Features**
	* *None*
* **Improvements**
	* *None*
* **Bugfixes**
	* Fixed a missing authentication query argument for update checks (Pro version).
* **Developer Notes**
	* *None*

= Version 1.0.2 (2015/08/18) =

* **New Features**
	* *None*
* **Improvements**
	* *None*
* **Bugfixes**
	* Fixed an incorrectly named object expiration variable in the `wpssorrssb_get_sharing_buttons()` function.
* **Developer Notes**
	* *None*

= Version 1.0.1 (2015/08/04) =

* **New Features**
	* *None*
* **Improvements**
	* Confirmed WordPress v4.2.4 compatibility.
* **Bugfixes**
	* Fixed a possible error on failed CSS file writes by applying PHP realpath() to the WPSSORRSSB_PLUGINDIR constant value.
* **Developer Notes**
	* *None*

= Version 1.0 (2015/08/02) =

* **New Features**
	* Added the WooCommerce, bbPress, and BuddyPress integration modules.
* **Improvements**
	* *None*
* **Bugfixes**
	* *None*
* **Developer Notes**
	* *None*

== Upgrade Notice ==

= 1.0.5 =

Added a WpssoRrssbRegister class with method calls during activation to save install / activation / update timestamps.

= 1.0.4 =

Updated the tooltip and info message filter names for WPSSO v3.8. Improved setting of constants with new methods in WpssoRrssbConfig class.

= 1.0.3 =

Fixed a missing authentication query argument for update checks (Pro version).

= 1.0.2 =

Fixed an incorrectly named object expiration variable in the wpssorrssb_get_sharing_buttons() function.

= 1.0.1 =

Fixed a possible error on failed CSS file writes by applying PHP realpath() to the WPSSORRSSB_PLUGINDIR constant value.

= 1.0 =

Added the WooCommerce, bbPress, and BuddyPress integration modules.

