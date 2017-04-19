<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuWebsiteTwitter' ) ) {

	class WpssoRrssbSubmenuWebsiteTwitter {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_website_twitter_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		public function filter_rrssb_website_twitter_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$submenu->show_on_checkboxes( 'twitter' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'twitter_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( ! SucomUtil::get_const( 'WPSSO_VARY_USER_AGENT_DISABLE' ) ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform', 'option label', 'wpsso-rrssb' ) ).
				'<td>'.$form->get_select( 'twitter_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Length', 'option label', 'wpsso-rrssb' ) ).'<td>'.
			$form->get_input( 'twitter_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Append Hashtags to Tweet', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'twitter_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' '.
					_x( 'tag names', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Add via Business @username',
				'option label', 'wpsso-rrssb' ), '', 'buttons_add_via'  ).
			'<td>'.$form->get_checkbox( 'twitter_via' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Recommend Author @username',
				'option label', 'wpsso-rrssb' ), '', 'buttons_rec_author'  ).
			'<td>'.$form->get_checkbox( 'twitter_rel_author' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			'<td colspan="2">'.$form->get_textarea( 'twitter_rrssb_html', 'average code' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbWebsiteTwitter' ) ) {

	class WpssoRrssbWebsiteTwitter {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'twitter_order' => 4,
					'twitter_on_content' => 1,
					'twitter_on_excerpt' => 0,
					'twitter_on_sidebar' => 0,
					'twitter_on_admin_edit' => 1,
					'twitter_platform' => 'any',
					'twitter_cap_len' => 140,
					'twitter_cap_hashtags' => 0,
					'twitter_via' => 1,
					'twitter_rel_author' => 1,
					'twitter_rrssb_html' => '<li class="rrssb-twitter">
	<a href="https://twitter.com/intent/tweet?original_referer=%%sharing_url%%&amp;url=%%short_url%%&amp;text=%%twitter_text%%&amp;hashtags=%%twitter_hashtags%%&amp;via=%%twitter_via%%&amp;related=%%twitter_related%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M24.253 8.756C24.69 17.08 18.297 24.182 9.97 24.62c-3.122.162-6.22-.646-8.86-2.32 2.702.18 5.375-.648 7.507-2.32-2.072-.248-3.818-1.662-4.49-3.64.802.13 1.62.077 2.4-.154-2.482-.466-4.312-2.586-4.412-5.11.688.276 1.426.408 2.168.387-2.135-1.65-2.73-4.62-1.394-6.965C5.574 7.816 9.54 9.84 13.802 10.07c-.842-2.738.694-5.64 3.434-6.48 2.018-.624 4.212.043 5.546 1.682 1.186-.213 2.318-.662 3.33-1.317-.386 1.256-1.248 2.312-2.4 2.942 1.048-.106 2.07-.394 3.02-.85-.458 1.182-1.343 2.15-2.48 2.71z" />
			</svg>
		</span>
		<span class="rrssb-text">twitter</span>
	</a>
</li><!-- .rrssb-twitter -->',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			self::$cf['opt']['defaults']['twitter_cap_hashtags'] = $def_opts['og_desc_hashtags'];
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$atts['add_hashtags'] = empty( $this->p->options['twitter_cap_hashtags'] ) ?
				false : $this->p->options['twitter_cap_hashtags'];

			if ( ! isset( $atts['tweet'] ) )
				$atts['tweet'] = $this->p->rrssb_sharing->get_tweet_text( $mod, $atts, 'twitter', 'twitter' );

			if ( ! isset( $atts['hashtags'] ) )
				$atts['hashtags'] = '';

			if ( ! isset( $atts['via'] ) ) {
				if ( ! empty( $opts['twitter_via'] ) ) {
					$atts['via'] = preg_replace( '/^@/', '', 
						SucomUtil::get_locale_opt( 'tc_site', $opts ) );
				} else $atts['via'] = '';
			}

			if ( ! isset( $atts['related'] ) ) {
				if ( ! empty( $opts['twitter_rel_author'] ) && 
					! empty( $mod['post_author'] ) && $atts['use_post'] )
						$atts['related'] = preg_replace( '/^@/', '', 
							get_the_author_meta( $opts['plugin_cm_twitter_name'], $mod['post_author'] ) );
				else $atts['related'] = '';
			}

			$extra_inline_vars = array();

			if ( SucomUtil::get_const( 'WPSSO_VARY_USER_AGENT_DISABLE' ) || SucomUtil::is_mobile() ) {
				$twitter_button_html = $this->p->options['twitter_rrssb_html'];
			} else {
				$twitter_button_html = preg_replace( '/(\/intent)\/(tweet\?)/', '$1/+/$2', 
					$this->p->options['twitter_rrssb_html'] );
			}

			// remove empty query arguments from the twitter button html
			// prevents twitter from appending an empty 'via' word to the tweet
			foreach ( array( 
				'text' => 'tweet',
				'hashtags' => 'hashtags',
				'via' => 'via',
				'related' => 'related',
			) as $query_key => $atts_key  ) {
				if ( ! empty( $atts[$atts_key] ) )
					$extra_inline_vars['twitter_'.$query_key] = rawurlencode( $atts[$atts_key] );
				else $twitter_button_html = preg_replace( '/&(amp;)?'.$query_key.'=%%twitter_'.$query_key.'%%/', '', $twitter_button_html );
			}

			return $this->p->util->replace_inline_vars( '<!-- Twitter Button -->'.
				$twitter_button_html, $mod, $atts, $extra_inline_vars );
		}
	}
}

?>
