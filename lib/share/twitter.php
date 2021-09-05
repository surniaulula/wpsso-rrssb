<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareTwitter' ) ) {

	class WpssoRrssbSubmenuShareTwitter {

		private $p;	// Wpsso class object.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_twitter_rows' => 3,
			) );
		}

		public function filter_rrssb_share_twitter_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $submenu->show_on_checkboxes( 'twitter' ) . '</td>';

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'twitter_button_order', range( 1, count( $submenu->share ) ) ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_utm_source' ) .
				$form->get_th_html( _x( 'UTM Source', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'twitter_utm_source' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_caption' ) .
				$form->get_th_html( _x( 'Tweet Text Source', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'twitter_caption', $this->p->cf[ 'form' ][ 'caption_types' ] ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_caption_max_len' ) . 
				$form->get_th_html( _x( 'Tweet Text Length', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'twitter_caption_max_len', $css_class = 'chars' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ) . '</td>';

			$table_rows[] = '' . 
				$form->get_th_html( _x( 'Append Hashtags to Tweet', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'twitter_caption_hashtags', range( 0, $this->p->cf[ 'form' ][ 'max_hashtags' ] ),
					$css_class = 'short', '', true ) . ' ' .
				_x( 'tag names', 'option comment', 'wpsso-rrssb' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_via' ) .
				$form->get_th_html( _x( 'Add via Business @username', 'option label', 'wpsso-rrssb' ), '', 'buttons_add_via'  ) . 
				'<td>' . $form->get_checkbox( 'twitter_via' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_rel_author' ) .
				$form->get_th_html( _x( 'Recommend Author @username', 'option label', 'wpsso-rrssb' ), '', 'buttons_rec_author'  ) . 
				'<td>' . $form->get_checkbox( 'twitter_rel_author' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_rrssb_html' ) . 
				'<td colspan="2">' . $form->get_textarea( 'twitter_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareTwitter' ) ) {

	class WpssoRrssbShareTwitter {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'twitter_on_admin_edit'    => 1,
					'twitter_on_content'       => 1,
					'twitter_on_excerpt'       => 0,
					'twitter_on_sidebar'       => 0,
					'twitter_on_woo_short'     => 1,
					'twitter_button_order'     => 3,
					'twitter_utm_source'       => 'twitter',
					'twitter_caption'          => 'excerpt',
					'twitter_caption_max_len'  => 280,	// Changed from 140 to 280 on 2017/11/17.
					'twitter_caption_hashtags' => 3,
					'twitter_via'              => 1,
					'twitter_rel_author'       => 1,
					'twitter_rrssb_html'       => '<li class="rrssb-twitter">
	<a href="https://twitter.com/intent/tweet?original_referer=%%sharing_url%%&url=%%short_url%%&text=%%twitter_text%%&hashtags=%%twitter_hashtags%%&via=%%twitter_via%%&related=%%twitter_related%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M24.253 8.756C24.69 17.08 18.297 24.182 9.97 24.62c-3.122.162-6.22-.646-8.86-2.32 2.702.18 5.375-.648 7.507-2.32-2.072-.248-3.818-1.662-4.49-3.64.802.13 1.62.077 2.4-.154-2.482-.466-4.312-2.586-4.412-5.11.688.276 1.426.408 2.168.387-2.135-1.65-2.73-4.62-1.394-6.965C5.574 7.816 9.54 9.84 13.802 10.07c-.842-2.738.694-5.64 3.434-6.48 2.018-.624 4.212.043 5.546 1.682 1.186-.213 2.318-.662 3.33-1.317-.386 1.256-1.248 2.312-2.4 2.942 1.048-.106 2.07-.394 3.02-.85-.458 1.182-1.343 2.15-2.48 2.71z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-twitter -->',
				),
			),
		);

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $def_opts ) {

			return array_merge( $def_opts, self::$cf[ 'opt' ][ 'defaults' ] );
		}

		/**
		 * Pre-defined attributes:
		 *
		 *	'use_post'
		 *	'add_page'
		 *	'sharing_url'
		 *	'sharing_short_url'
		 *	'rawurlencode' (true)
		 */
		public function get_html( $mod, $atts ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$atts[ 'tweet' ]    = WpssoRrssbSocial::get_tweet_text( $mod, $atts, $opt_pre = 'twitter', $md_pre = 'twitter' );
			$atts[ 'hashtags' ] = '';
			$atts[ 'via' ]      = '';
			$atts[ 'related' ]  = '';

			if ( ! empty( $this->p->options[ 'twitter_via' ] ) ) {

				$atts[ 'via' ] = preg_replace( '/^@/', '', SucomUtil::get_key_value( 'tc_site', $this->p->options ) );
			}

			if ( ! empty( $this->p->options[ 'twitter_rel_author' ] ) && ! empty( $mod[ 'post_author' ] ) && $atts[ 'use_post' ] ) {

				$atts[ 'related' ] = preg_replace( '/^@/', '', get_the_author_meta( $this->p->options[ 'plugin_cm_twitter_name' ], $mod[ 'post_author' ] ) );
			}

			/**
			 * Remove empty query arguments from the twitter button html (prevents appending an empty 'via' word to the tweet).
			 */
			$twitter_button_html = $this->p->options[ 'twitter_rrssb_html' ];

			$extras = array();

			foreach ( array( 
				'text'     => 'tweet',
				'hashtags' => 'hashtags',
				'via'      => 'via',
				'related'  => 'related',
			) as $query_key => $atts_key  ) {

				if ( empty( $atts[ $atts_key ] ) ) {

					$twitter_button_html = preg_replace( '/&(amp;)?' . $query_key . '=%%twitter_' . $query_key . '%%/', '', $twitter_button_html );

				} else {

					$extras[ 'twitter_' . $query_key ] = $atts[ $atts_key ];
				}
			}

			return $this->p->util->replace_inline_variables( $twitter_button_html, $mod, $atts, $extras );
		}
	}
}
