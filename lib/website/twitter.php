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

		public $id = '';
		public $name = '';
		public $form = '';

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->id = $id;
			$this->name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'twitter_order' ).
			'<td>'.$this->form->get_select( 'twitter_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'twitter' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Tweet Text Length' ).'<td>'.
			$this->form->get_input( 'twitter_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Tweet' ).
			'<td>'.$this->form->get_select( 'twitter_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = $this->p->util->get_th( 'Add via @username', null, null, 'Append the website\'s @username to the tweet (see the '.$this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter', 'Twitter options tab' ).' on the General settings page). The website\'s @username will be displayed and recommended after the Post / Page is shared.' ).
			'<td>'.$this->form->get_checkbox( 'twitter_via' ).'</td>';

			$rows[] = $this->p->util->get_th( 'Recommend Author', null, null, 'Recommend following the Author\'s Twitter @username (from their profile) after sharing. If the \'<em>Add via @username</em>\' option (above) is also checked, the Website\'s @username is suggested first.' ).
			'<td>'.$this->form->get_checkbox( 'twitter_rel_author' ).'</td>';

			$rows[] = $this->p->util->get_th( 'Shorten URLs with', 'highlight', null, 'If you select a URL shortening service here, <strong>you must also enter its Service API Keys</strong> on the '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys', 'Advanced settings page' ).'.' ).
			( $this->p->check->aop() ? '<td>'.$this->form->get_select( 'plugin_shortener', $this->p->cf['form']['shorteners'], 'medium' ).'&nbsp;' : '<td class="blank">'.$this->form->get_hidden( 'plugin_shortener' ).$this->p->cf['form']['shorteners'][$this->p->options['plugin_shortener']].' &mdash; ' ).' <strong>using these '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys', 'Service API Keys' ).'</strong></td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'twitter_html' ).
			'<td>'.$this->form->get_textarea( 'twitter_html', 'average code' ).'</td>';

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
