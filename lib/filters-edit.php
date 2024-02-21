<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersEdit' ) ) {

	class WpssoRrssbFiltersEdit {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/*
		 * Instantiated by WpssoRrssbFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array(
				'mb_sso_tabs'            => 2,
				'mb_sso_edit_share_rows' => 4,
				'mb_sso_inside_footer'   => 2,
			), $prio = 200 );
		}

		public function filter_mb_sso_tabs( $tabs, $mod ) {

			if ( $mod[ 'is_public' ] ) {

				SucomUtil::add_before_key( $tabs, 'prev_social', 'edit_share', _x( 'Edit Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
			}

			return $tabs;
		}

		/*
		 * Default option values are defined in WpssoRrssbFiltersOptions->filter_get_md_defaults().
		 */
		public function filter_mb_sso_edit_share_rows( $table_rows, $form, $head, $mod ) {

			$def_caption_title = $this->p->page->get_caption( $mod, $md_key = '', $caption_type = 'title', $caption_max_len = 0, $num_hashtags = false );

			$form_rows[ 'buttons_disabled' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Disable Share Buttons', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_disabled',
				'content'  => $form->get_checkbox( 'buttons_disabled' ),
			);

			$form_rows[ 'buttons_utm_campaign' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'UTM Campaign', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_utm_campaign',
				'content'  => $form->get_input( 'buttons_utm_campaign' ),
			);

			/*
			 * Email.
			 */
			$email_type     = 'both';
			$email_max_len  = $this->p->options[ 'email_caption_max_len' ];
			$def_email_desc = $this->p->page->get_caption( $mod, $md_key = '', $email_type, $email_max_len, $num_hashtags = false );

			$form_rows[ 'email_title' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_email_title',
				'content'  => $form->get_input( 'email_title', $css_class = 'wide', $css_id = '', 0, $def_caption_title ),
			);

			$form_rows[ 'email_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_email_desc',
				'content'  => $form->get_textarea( 'email_desc', $css_class = '', $css_id = '', $email_max_len, $def_email_desc ),
			);

			/*
			 * Pinterest.
			 */
			$pin_type    = 'excerpt';
			$pin_max_len = $this->p->options[ 'pin_caption_max_len' ];
			$pin_desc    = $this->p->page->get_caption( $mod, $md_key = '', $pin_type, $pin_max_len, $num_hashtags = false );

			$form_rows[ 'pin_desc' ] = array(
				'th_class' => 'medium',
				'td_class' => 'top',
				'label'    => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_pin_desc',
				'content'  => $form->get_textarea( 'pin_desc', $css_class = '', $css_id = '', $pin_max_len, $pin_desc ),
			);

			/*
			 * X (Twitter).
			 */
			$twitter_type     = $this->p->options[ 'twitter_caption' ];
			$twitter_max_len  = WpssoRrssbSocial::get_tweet_max_len();
			$twitter_hashtags = $this->p->options[ 'twitter_caption_hashtags' ];
			$def_twitter_desc = $this->p->page->get_caption( $mod, $md_key = '', $twitter_type, $twitter_max_len, $twitter_hashtags );

			$form_rows[ 'twitter_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'meta-buttons_twitter_desc',
				'content'  => $form->get_textarea( 'twitter_desc', $css_class = '', $css_id = '', $twitter_max_len, $def_twitter_desc ),
			);

			/*
			 * Others.
			 */
			foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit'   => 'Reddit',
			) as $opt_pre => $name ) {

				$other_type    = 'excerpt';
				$other_max_len = $this->p->options[ $opt_pre . '_caption_max_len' ];
				$other_desc    = $this->p->page->get_caption( $mod, $md_key = '', $other_type, $other_max_len, $num_hashtags = false );

				$form_rows[ $opt_pre . '_title' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'meta-buttons_' . $opt_pre . '_title',
					'content'  => $form->get_input( $opt_pre . '_title', $css_class = 'wide', $css_id = '', 0, $def_caption_title ),
				);

				$form_rows[ $opt_pre . '_desc' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'meta-buttons_' . $opt_pre . '_desc',
					'content'  => $form->get_textarea( $opt_pre . '_desc', $css_class = '', $css_id = '', $other_max_len, $other_desc ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}

		public function filter_mb_sso_inside_footer( $metabox_html, $mod ) {

			if ( empty( $mod[ 'is_public' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: ' . $mod[ 'name' ] . ' id ' . $mod[ 'id' ] . ' is not public' );
				}

				return $metabox_html;
			}

			$metabox_html .= $this->a->social->get_buttons( $text = '', $type = 'admin_edit', $mod );

			/*
			 * The type="text/javascript" attribute is unnecessary for JavaScript resources and creates warnings in the W3C validator.
			 */
			$metabox_html .= <<<EOF
<script>

function runRrssbInit() {

	var rrssbInitExists = setInterval( function() {

		if ( 'function' === typeof rrssbInit ) {

			rrssbInit();

			clearInterval( rrssbInitExists );
		}

	}, 1500 );
}

runRrssbInit();

</script>
EOF;

			return $metabox_html;
		}
	}
}
