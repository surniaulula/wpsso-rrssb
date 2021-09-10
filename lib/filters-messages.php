<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersMessages' ) ) {

	class WpssoRrssbFiltersMessages {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/**
		 * Instantiated by WpssoRrssbFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array( 
				'messages_info'            => 2,
				'messages_tooltip_buttons' => 2,
				'messages_tooltip_meta'    => 2,
			) );
		}

		public function filter_messages_info( $text, $msg_key ) {

			if ( 0 !== strpos( $msg_key, 'info-styles-rrssb-' ) ) {

				return $text;
			}

			$short = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'short' ];

			switch ( $msg_key ) {

				case 'info-styles-rrssb-common':	// All Buttons tab.

					$text = '<p>';

					$text .= sprintf( __( 'The %1$s add-on uses the "%2$s" class to wrap all sharing buttons, and each button has its own individual class name as well.', 'wpsso-rrssb' ), $short, 'wpsso-rrssb' );

					$text .= '</p><p>';

					$text .= __( 'This tab can be used to edit the CSS common to all sharing button locations.', 'wpsso-rrssb' );

					$text .= '</p>';

					break;

				case 'info-styles-rrssb-content':	// Content tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress content are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-content' ).

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'content', true );

					break;

				case 'info-styles-rrssb-excerpt':	// Excerpt tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress excerpt are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-excerpt' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'excerpt', true );

					break;

				case 'info-styles-rrssb-sidebar':	// CSS Sidebar tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the CSS sidebar are assigned the "%2$s" ID.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-sidebar' );

					$text .= '</p><p>';

					$text .= 'In order to achieve a vertical display, each unordered list (UL) contains a single list item (LI).';

					$text .= '</p>';

					$text .= '<p>Example CSS:</p>
<pre>
div.wpsso-rrssb 
  #wpsso-rrssb-sidebar
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';
					break;

				case 'info-styles-rrssb-admin_edit':	// Admin Edit tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for admin editing pages are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-admin_edit' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'admin_edit', true );

					break;

				case 'info-styles-rrssb-shortcode':	// Shortcode tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons added from a shortcode are assigned the "%1$s" class by default.', 'wpsso-rrssb' ), 'wpsso-rrssb-shortcode' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'shortcode', true );

					break;

				case 'info-styles-rrssb-widget':	// Widget tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s widget are assigned the "%2$s" class (along with additional unique CSS ID names).', 'wpsso-rrssb' ), $short, 'wpsso-rrssb-widget' );

					$text .= '</p>';

					$text .= '<p>Example CSS:</p>
<pre>
aside.widget 
  .wpsso-rrssb-widget 
    ul.rrssb-buttons
        li.rrssb-facebook {}
</pre>';

					break;

				case 'info-styles-rrssb-woo_short': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for WooCommerce short descriptions are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-woo_short' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'woo_short' );

      					break;

				case 'info-styles-rrssb-bbp_single': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for bbPress single templates are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bbp_single' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bbp_single' );

      					break;

				case 'info-styles-rrssb-bblog_post': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for BuddyBlog posts are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bblog_post' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bblog_post' );

      					break;

				case 'info-styles-rrssb-bp_activity': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for BuddyPress activities are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bp_activity' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bp_activity' );

      					break;
			}

			return $text;
		}

		public function filter_messages_tooltip_buttons( $text, $msg_key ) {

			switch ( $msg_key ) {

				/**
				 * SSO > Responsive Buttons settings page.
				 */
				case 'tooltip-buttons_on_archive':	// Include on Archive Webpages.

					$text = __( 'Add social sharing buttons to each post on an archive webpage (blog front page, category, etc.).', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_on_front':	// Include on Static Homepage.

					$text = __( 'If a static page has been selected for your homepage, you can add social sharing buttons to that static page as well.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_to':	// Include on Post Types.

					$text = __( 'Social sharing buttons are added to posts, pages, and attachment webpages by default.', 'wpsso-rrssb' ) . ' ';

					$text .= __( 'If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the associated options here.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_cta':	// Call to Action.

					$text = __( 'Include a call to action banner above the social sharing buttons.', 'wpsso-rrssb' ) . ' ';

					break;

				case ( strpos( $msg_key, 'tooltip-buttons_pos_' ) === false ? false : true ):	// Position in Content and Excerpt.

					$text = __( 'Social sharing buttons can be added to the top, bottom, or both locations in the text.', 'wpsso-rrssb' ) . ' ';

					$text .= sprintf( __( 'The <em>%s</em> option of each social sharing button must be enabled as well for that button to appear in that location in the text.', 'wpsso-rrssb' ), _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_force_prot':	// Force Protocol for Shared URLs.

					$text = __( 'Force the selected protocol for all shared URLs, or select none to keep the protocol as-is.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_utm_medium':	// UTM Medium for All Buttons.

					$text = __( 'Identifies the origin of a shared link.', 'wpsso-rrssb' ) . ' ';

					$text .= __( 'Popular UTM medium values are "cpc", "email", "referral", and "social".', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_via':

					$text = sprintf( __( 'Append the %1$s to the tweet (see <a href="%2$s">the Twitter options tab</a> in the %3$s page). The %1$s will be displayed and recommended after the webpage is shared.', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General Settings', 'lib file description', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_rec_author':

					$text = sprintf( __( 'Recommend following the author\'s Twitter @username after sharing a webpage. If the %1$s option (above) is also checked, the %2$s is suggested first.', 'wpsso-rrssb' ), _x( 'Add via Business @username', 'option label', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ) );

					break;

				/**
				 * SSO > Responsive Styles settings page.
				 */
				case 'tooltip-buttons_use_social_style':		// Use the Social Stylesheet.

					$sharing_css_url = WpssoRrssbSocial::get_sharing_css_url();

					$text = sprintf( __( 'Combine and include the CSS of all <em>%s</em> in webpages.', 'wpsso-rrssb' ), _x( 'Responsive Styles', 'lib file description', 'wpsso-rrssb' ) ) . ' ';

					$text .= sprintf( __( 'The combined CSS will be minified and saved into a single stylesheet with a URL of <a href="%1$s">%2$s</a>.', 'wpsso-rrssb' ), $sharing_css_url, $sharing_css_url ) . ' ';

					$text .= __( 'The minified stylesheet can be enqueued, or included directly in the webpage HTML.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_enqueue_social_style':	// Enqueue the Stylesheet.

					$text = __( 'Have WordPress enqueue the social stylesheet instead of including the social styles directly in the webpage HTML.', 'wpsso-rrssb' ) . ' ';

					$text .= __( 'Enqueueing the stylesheet may be desirable if you use an optimization plugin to concatenate all enqueued styles into a single stylesheet.', 'wpsso-rrssb' );

					break;

			}

			return $text;
		}

		public function filter_messages_tooltip_meta( $text, $msg_key ) {

			if ( 0 !== strpos( $msg_key, 'tooltip-meta-buttons_' ) ) {

				return $text;
			}

			switch ( $msg_key ) {

				case 'tooltip-meta-buttons_disabled':	// Disable Share Buttons.

					$text = __( 'Disable social sharing buttons in the post content, excerpt, and CSS sidebar.', 'wpsso-rrssb' ) . ' ';

					$text .= __( 'This does not disable social sharing buttons added using a shortcode or widget.', 'wpsso-rrssb' ) . ' ';

				 	break;

				case 'tooltip-meta-buttons_utm_campaign':	// UTM Campaign.

					$text = __( 'Identifies a strategic campaign (e.g. product launch, new feature, partnership, etc.) or a specific promotion (e.g. a sale, a giveaway, etc.).', 'wpsso-rrssb' );

				 	break;
			}

			return $text;
		}

		private function get_info_css_example( $type ) {

			$text = '<p>Example CSS:</p>
<pre>
div.wpsso-rrssb
  .wpsso-rrssb-' . $type . '
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';

			return $text;
		}
	}
}
