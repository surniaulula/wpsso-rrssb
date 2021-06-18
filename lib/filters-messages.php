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
				'messages_info'           => 2,
				'messages_tooltip'        => 2,
			) );
		}

		public function filter_messages_info( $text, $msg_key ) {

			if ( strpos( $msg_key, 'info-styles-rrssb-' ) !== 0 ) {

				return $text;
			}

			$short = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'short' ];

			switch ( $msg_key ) {

				case 'info-styles-rrssb-sharing':

					$text = '<p>';

					$text .= sprintf( __( 'The %1$s add-on uses the "%2$s" class to wrap all sharing buttons, and each button has its own individual class name as well.', 'wpsso-rrssb' ), $short, 'wpsso-rrssb' );

					$text .= '</p><p>';

					$text .= __( 'This tab can be used to edit the CSS common to all sharing button locations.', 'wpsso-rrssb' );

					$text .= '</p>';

					break;

				case 'info-styles-rrssb-content':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress content are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-content' ).

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'content', true );

					break;

				case 'info-styles-rrssb-excerpt':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress excerpt are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-excerpt' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'excerpt', true );

					break;

				case 'info-styles-rrssb-sidebar':

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

				case 'info-styles-rrssb-shortcode':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons added from a shortcode are assigned the "%1$s" class by default.', 'wpsso-rrssb' ), 'wpsso-rrssb-shortcode' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'shortcode', true );

					break;

				case 'info-styles-rrssb-widget':

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

				case 'info-styles-rrssb-admin_edit':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for admin editing pages are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-admin_edit' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'admin_edit', true );

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

		public function filter_messages_tooltip( $text, $msg_key ) {

			if ( strpos( $msg_key, 'tooltip-buttons_' ) !== 0 ) {

				return $text;
			}

			switch ( $msg_key ) {

				case ( strpos( $msg_key, 'tooltip-buttons_pos_' ) === false ? false : true ):

					$text = sprintf( __( 'Social sharing buttons can be added to the top, bottom, or both. Each sharing button must also be enabled below (see the <em>%s</em> options).', 'wpsso-rrssb' ), _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_on_archive':

					$text = __( 'Add social sharing buttons to each post on an archive webpage (blog front page, category, etc.).', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_on_front':

					$text = __( 'If a static page has been selected for your homepage, you can add social sharing buttons to that static page as well (default is unchecked).', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_to':

					$text = __( 'Enabled social sharing buttons are added to the Post, Page, Media, and Product webpages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the appropriate option(s) here.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_force_prot':

					$text = __( 'Modify URLs shared by the sharing buttons to use a specific protocol.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_use_social_style':

					$sharing_css_url = WpssoRrssbSocial::get_sharing_css_url();

					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minified</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minified stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Responsive Styles', 'lib file description', 'wpsso-rrssb' ), $sharing_css_url, $sharing_css_url );

					break;

				case 'tooltip-buttons_enqueue_social_style':

					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_via':

					$text = sprintf( __( 'Append the %1$s to the tweet (see <a href="%2$s">the Twitter options tab</a> in the %3$s page). The %1$s will be displayed and recommended after the webpage is shared.', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General Settings', 'lib file description', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_rec_author':

					$text = sprintf( __( 'Recommend following the author\'s Twitter @username after sharing a webpage. If the %1$s option (above) is also checked, the %2$s is suggested first.', 'wpsso-rrssb' ), _x( 'Add via Business @username', 'option label', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ) );

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
