<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingTwitter' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingTwitter extends WpssoRrssbSubmenuSharingButtons {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();

			$rows[] = $this->p->util->get_th( _x( 'Preferred Order',
				'option label', 'wpsso-rrssb' ), null, 'twitter_order' ).
			'<td>'.$this->form->get_select( 'twitter_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( _x( 'Show Button in',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->show_on_checkboxes( 'twitter' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Tweet Text Length',
				'option label', 'wpsso-rrssb' ) ).'<td>'.
			$this->form->get_input( 'twitter_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Append Hashtags to Tweet',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->form->get_select( 'twitter_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' '.
					_x( 'tag names', 'option comment', 'wpsso-rrssb' ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Add via @username',
				'option label', 'wpsso-rrssb' ), null, null, 
			sprintf( __( 'Append the website\'s business @username to the tweet (see the <a href="%1$s">Twitter</a> options tab on the %2$s settings page). The website\'s @username will be displayed and recommended after the webpage is shared.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General', 'lib file description', 'wpsso' ) ) ).
			'<td>'.$this->form->get_checkbox( 'twitter_via' ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Recommend Author',
				'option label', 'wpsso-rrssb' ), null, null, 
			sprintf( __( 'Recommend following the author\'s Twitter @username (from their profile) after sharing a webpage. If the <em>%1$s</em> option is also checked, the website\'s @username is suggested first.', 'wpsso-rrssb' ), _x( 'Add via @username', 'option label', 'wpsso-rrssb' ) ) ).
			'<td>'.$this->form->get_checkbox( 'twitter_rel_author' ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Shorten URLs with',
				'option label', 'wpsso-rrssb' ), 'highlight', null, 
			sprintf( __( 'If you select a URL shortening service here, you must also enter its <a href="%1$s">%2$s</a> on the %3$s settings page.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys' ), _x( 'Service API Keys', 'metabox tab', 'wpsso' ), _x( 'Advanced', 'lib file description', 'wpsso' ) ) ).
			( $this->p->check->aop() ?
				'<td>'.$this->form->get_select( 'plugin_shortener', $this->p->cf['form']['shorteners'], 'medium' ).'&nbsp; ' :
				'<td class="blank">'.$this->p->cf['form']['shorteners'][$this->p->options['plugin_shortener']].' &mdash; ' ).
			sprintf( __( 'using these <a href="%1$s">%2$s</a>', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys' ), _x( 'Service API Keys', 'metabox tab', 'wpsso' ) ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			'<td colspan="2">'.$this->form->get_textarea( 'twitter_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingTwitter' ) ) {

	class WpssoRrssbSharingTwitter {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'twitter_on_content' => 1,
					'twitter_on_excerpt' => 0,
					'twitter_on_sidebar' => 0,
					'twitter_on_admin_edit' => 1,
					'twitter_order' => 4,
					'twitter_cap_len' => 140,
					'twitter_cap_hashtags' => 0,
					'twitter_via' => 1,
					'twitter_rel_author' => 1,
					'twitter_html' => '<li class="rrssb-twitter">
	<a href="https://twitter.com/intent/tweet?original_referer=%%sharing_url%%&url=%%short_url%%&text=%%twitter_text%%&hashtags=%%twitter_hashtags%%&via=%%twitter_via%%&related=%%twitter_related%%" class="popup">
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
				'get_meta_defaults' => 2,
			) );
		}

		public function filter_get_meta_defaults( $opts_def, $mod ) {
			$meta_def = array(
				'twitter_desc' => '',
			);
			return array_merge( $opts_def, $meta_def );
		}

		public function filter_get_defaults( $opts_def ) {
			self::$cf['opt']['defaults']['twitter_cap_hashtags'] = $opts_def['og_desc_hashtags'];
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;

			$atts['add_hashtags'] = empty( $this->p->options['twitter_cap_hashtags'] ) ?
				false : $this->p->options['twitter_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'twitter', $atts );

			if ( ! isset( $atts['tweet'] ) )
				$atts['tweet'] = $this->p->util->get_tweet_text( $atts, 'twitter', 'twitter' );

			if ( ! isset( $atts['hashtags'] ) )
				$atts['hashtags'] = '';

			if ( ! isset( $atts['via'] ) ) {
				if ( ! empty( $opts['twitter_via'] ) )
					$atts['via'] = preg_replace( '/^@/', '', $opts['tc_site'] );
				else $atts['via'] = '';
			}

			if ( ! isset( $atts['related'] ) ) {
				if ( ! empty( $opts['twitter_rel_author'] ) && 
					! empty( $post ) && $use_post === true )
						$atts['related'] = preg_replace( '/^@/', '', 
							get_the_author_meta( $opts['plugin_cm_twitter_name'], $post->author ) );
				else $atts['related'] = '';
			}

			return $this->p->util->replace_inline_vars( $this->p->options['twitter_html'], $use_post, false, $atts, array(
				 	'twitter_text' => rawurlencode( $atts['tweet'] ),
				 	'twitter_hashtags' => rawurlencode( $atts['hashtags'] ),
				 	'twitter_via' => rawurlencode( $atts['via'] ),
				 	'twitter_related' => rawurlencode( $atts['related'] ),
				 ) );
		}
	}
}

?>
