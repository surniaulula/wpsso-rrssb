<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersMessages' ) ) {

	class WpssoRrssbFiltersMessages {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/*
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

			$addon_name   = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'name' ];
			$widget_name  = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'widget' ][ 'sharing' ];
			$buttons_link = $this->p->util->get_admin_url( 'rrssb-buttons', _x( 'Responsive Buttons', 'lib file description', 'wpsso-rrssb' ) );

			switch ( $msg_key ) {

				case 'info-styles-rrssb-common':	// All tab.

					$text = '<p>';

					$text .= sprintf( __( 'The %1$s add-on uses the "%2$s" class to wrap all social sharing buttons.', 'wpsso-rrssb' ),
						$addon_name, 'wpsso-rrssb' ) . ' ';

					$text .= '</p><p>';

					$text .= __( 'This style tab can be used to edit the styling applied to all social sharing buttons.', 'wpsso-rrssb' ) . ' ';

					$text .= __( 'To edit the style for a specific location, use the style tab for that location.', 'wpsso-rrssb' ) . ' ';

					$text .= '</p>';

					break;

				case 'info-styles-rrssb-content':	// Content tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons for the WordPress content area are wrapped in a "%s" CSS class.', 'wpsso-rrssb' ),
						'wpsso-rrssb-content' ) . ' ';

					$text .= '</p><p>';

					$text .= sprintf( __( 'Social sharing buttons can be enabled individually from the %s settings page.', 'wpsso-rrssb' ),
						$buttons_link ) . ' ';

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'content' );

					break;

				case 'info-styles-rrssb-excerpt':	// Excerpt tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons for the WordPress excerpt area are wrapped in a "%s" CSS class.', 'wpsso-rrssb' ),
						'wpsso-rrssb-excerpt' ) . ' ';

					$text .= '</p><p>';

					$text .= sprintf( __( 'Social sharing buttons can be enabled individually from the %s settings page.', 'wpsso-rrssb' ),
						$buttons_link ) . ' ';

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'excerpt' );

					break;

				case 'info-styles-rrssb-sidebar':	// Sidebar tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons for the CSS floating sidebar are wrapped in a "%s" container ID.', 'wpsso-rrssb' ),
						'wpsso-rrssb-sidebar' ) . ' ';

					$text .= '</p><p>';

					$text .= sprintf( __( 'Social sharing buttons can be enabled individually from the %s settings page.', 'wpsso-rrssb' ),
						$buttons_link ) . ' ';

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'sidebar', $is_css_id = true );

					$text .= '<p>';

					$text .= __( 'Note that in order to achieve a vertical display each unordered list contains a single button.', 'wpsso-rrssb' );

					$text .= '</p>';

					break;

				case 'info-styles-rrssb-admin_edit':	// Admin tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons for the WordPress admin editor are wrapped in a "%s" CSS class.', 'wpsso-rrssb' ),
						'wpsso-rrssb-admin_edit' ) . ' ';

					$text .= '</p><p>';

					$text .= sprintf( __( 'Social sharing buttons can be enabled individually from the %s settings page.', 'wpsso-rrssb' ),
						$buttons_link ) . ' ';

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'admin_edit' );

					break;

				case 'info-styles-rrssb-shortcode':	// Shortcode tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons added by the RRSSB shortcode are wrapped in a "%s" CSS class by default (a different class name may be provided to the shortcode).', 'wpsso-rrssb' ), 'wpsso-rrssb-shortcode' ) . ' ';

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'shortcode' );

					break;

				case 'info-styles-rrssb-widget':	// Widget tab.

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled for the RRSSB widget are wrapped in a "%s" CSS class.', 'wpsso-rrssb' ),
						'wpsso-rrssb-widget' ) . ' ';

					$text .= '</p>';

					$text .= '<p>Example CSS:</p>
<pre>
aside.widget
  .wpsso-rrssb-widget
    ul.rrssb-buttons
        li.rrssb-facebook {}
</pre>';

					break;

				case 'info-styles-rrssb-woocommerce':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons for the WooCommerce short description or add to cart button areas are wrapped in a "%1$s" or "%2$s" CSS class.', 'wpsso-rrssb' ), 'wpsso-rrssb-wc_short_desc', 'wpsso-rrssb-wc_add_to_cart' ) . ' ';

					$text .= '</p><p>';

					$text .= sprintf( __( 'Social sharing buttons can be enabled individually from the %s settings page.', 'wpsso-rrssb' ),
						$buttons_link ) . ' ';
					$text .= '</p>';

					$text .= $this->get_info_css_example( array( 'wc_short_desc', 'wc_add_to_cart' ) );

      					break;
			}

			return $text;
		}

		public function filter_messages_tooltip_buttons( $text, $msg_key ) {

			switch ( $msg_key ) {

				/*
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

					$text .= sprintf( __( 'You may leave this option blank or hide it using the "%s" CSS class.', 'wpsso-rrssb' ), 'rrssb-buttons-cta' ) . ' ';

					break;

				case ( strpos( $msg_key, 'tooltip-buttons_pos_' ) === false ? false : true ):	// Buttons Position in Content and Excerpt.

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

					$username_label = _x( 'X (Twitter) Business @username', 'option label', 'wpsso-rrssb' );

					$text = sprintf( __( 'Append the %1$s to the tweet.', 'wpsso-rrssb' ), $username_label ) . ' ';

					$text .= sprintf( __( 'The %1$s will be displayed and recommended after the webpage is shared.', 'wpsso-rrssb' ), $username_label );

					break;

				case 'tooltip-buttons_rec_author':

					$username_label   = _x( 'X (Twitter) Business @username', 'option label', 'wpsso-rrssb' );
					$add_option_label = _x( 'Add via Business @username', 'option label', 'wpsso-rrssb' );

					$text = __( 'Recommend following the author\'s X (Twitter) @username after sharing a webpage.', 'wpsso-rrssb' ) . ' ';

					$text .= sprintf( __( 'If the %1$s option is also checked, the %2$s is suggested first.', 'wpsso-rrssb' ),
						$add_option_label, $username_label );

					break;

				/*
				 * SSO > Responsive Styles settings page.
				 */
				case 'tooltip-buttons_use_social_style':		// Use the Social Stylesheet.

					$sharing_css_url = WpssoRrssbSocial::get_sharing_css_url();

					$text = sprintf( __( 'Combine and include the CSS of all <em>%s</em> in webpages.', 'wpsso-rrssb' ),
						_x( 'Responsive Styles', 'lib file description', 'wpsso-rrssb' ) ) . ' ';

					$text .= sprintf( __( 'The combined CSS will be minified and saved into a single stylesheet with a URL of <a href="%1$s">%2$s</a>.',
						'wpsso-rrssb' ), $sharing_css_url, $sharing_css_url ) . ' ';

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

		private function get_info_css_example( $types, $is_css_id = false ) {

			$text = '<p>' . __( 'Example CSS:', 'wpsso-rrssb' ) . '</p>';

			$types = is_array( $types ) ? $types : array( $types );

			foreach ( $types as $type ) {

				$container = $is_css_id ? '#wpsso-rrssb-' . $type : '.wpsso-rrssb-' . $type;

				$text .= '<pre>
div.wpsso-rrssb
  ' . $container . '
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';

			}

			return $text;
		}
	}
}
