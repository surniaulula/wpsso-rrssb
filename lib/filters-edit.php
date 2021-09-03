<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersEdit' ) ) {

	class WpssoRrssbFiltersEdit {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/**
		 * Instantiated by WpssoRrssbFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array( 
				'post_document_meta_tabs'   => 3,
				'post_buttons_rows'         => 4,
				'metabox_sso_inside_footer' => 2,
			), $prio = 40 );	// Run after WPSSO Core's own Standard / Premium filters.
		}

		public function filter_post_document_meta_tabs( $tabs, $mod, $metabox_id ) {

			switch ( $metabox_id ) {

				case $this->p->cf[ 'meta' ][ 'id' ]:	// 'sso' metabox ID.

					if ( $mod[ 'is_public' ] ) {	// Since WPSSO Core v7.0.0.

						SucomUtil::add_after_key( $tabs, 'media', 'buttons', _x( 'Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
					}

					break;
			}

			return $tabs;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			$def_caption_title = $this->p->page->get_caption( $type = 'title', 0, $mod, true, false );

			$form_rows[ 'buttons_disabled' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'rrssb-buttons_disabled',
				'content'  => $form->get_checkbox( 'buttons_disabled' ),
			);

			/**
			 * Email
			 */
			$email_caption_max_len = $this->p->options[ 'email_caption_max_len' ];

			$email_caption_text = $this->p->page->get_caption( $type = 'excerpt', $email_caption_max_len, $mod,
				$read_cache = true, $add_hashtags = false, $do_encode = true, $md_key = 'none' );

			$form_rows[ 'subsection_email' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Email',
			);

			$form_rows[ 'email_title' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'rrssb-email_title',
				'content'  => $form->get_input( 'email_title', $css_class = 'wide', $css_id = '', 0, $def_caption_title ),
			);

			$form_rows[ 'email_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'rrssb-email_desc',
				'content'  => $form->get_textarea( 'email_desc', $css_class = '', $css_id = '', $email_caption_max_len, $email_caption_text ),
			);

			/**
			 * Twitter
			 */
			$twitter_caption_type = empty( $this->p->options[ 'twitter_caption' ] ) ? 'title' : $this->p->options[ 'twitter_caption' ];

			$twitter_caption_max_len = WpssoRrssbSocial::get_tweet_max_len();

			$twitter_caption_hashtags = $this->p->options[ 'twitter_caption_hashtags' ];

			$twitter_caption_text = $this->p->page->get_caption( $twitter_caption_type, $twitter_caption_max_len, $mod,
				$read_cache = true, $twitter_caption_hashtags );

			$form_rows[ 'subsection_twitter' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Twitter',
			);

			$form_rows[ 'twitter_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'rrssb-twitter_desc',
				'content'  => $form->get_textarea( 'twitter_desc', '', '', $twitter_caption_max_len, $twitter_caption_text ),
			);

			/**
			 * Pinterest
			 */
			$pin_caption_max_len = $this->p->options[ 'pin_caption_max_len' ];

			$pin_caption_text = $this->p->page->get_caption( $type = 'excerpt', $pin_caption_max_len, $mod,
				$read_cache = true, $add_hashtags = false );

			$form_rows[ 'subsection_pinterest' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Pinterest',
			);

			$form_rows[ 'pin_desc' ] = array(
				'th_class' => 'medium',
				'td_class' => 'top',
				'label'    => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'rrssb-pin_desc',
				'content'  => $form->get_textarea( 'pin_desc', '', '', $pin_caption_max_len, $pin_caption_text ),
			);

			/**
			 * Other Title / Caption Input
			 */
			foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit' => 'Reddit',
				'tumblr' => 'Tumblr',
			) as $opt_pre => $name ) {

				$other_caption_max_len = $this->p->options[ $opt_pre . '_caption_max_len' ];

				$other_caption_text = $this->p->page->get_caption( $type = 'excerpt', $other_caption_max_len, $mod,
					$read_cache = true, $add_hashtags = false );

				$form_rows[ 'subsection_' . $opt_pre ] = array(
					'td_class' => 'subsection',
					'col_span' => '3',
					'header'   => 'h4',
					'label'    => $name,
				);

				$form_rows[ $opt_pre . '_title' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'rrssb-' . $opt_pre . '_title',
					'content'  => $form->get_input( $opt_pre . '_title', 'wide', '', 0, $def_caption_title ),
				);

				$form_rows[ $opt_pre . '_desc' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'rrssb-' . $opt_pre . '_desc',
					'content'  => $form->get_textarea( $opt_pre . '_desc', '', '', $other_caption_max_len, $other_caption_text ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}

		public function filter_metabox_sso_inside_footer( $metabox_html, $mod ) {

			if ( empty( $mod[ 'is_public' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: ' . $mod[ 'name' ] . ' id ' . $mod[ 'id' ] . ' is not public' );
				}

				return $metabox_html;
			}

			$doing_ajax = SucomUtilWP::doing_ajax();

			$metabox_html .= $this->a->social->get_buttons( $text = '', 'admin_edit', $mod );

			/**
			 * The type="text/javascript" attribute is unnecessary for JavaScript resources and creates warnings in the W3C validator.
			 */
			$metabox_html .= <<<EOF
<script>

function runRrssbInit() {

	var rrssbInitCount = 0;

	var rrssbInitExists = setInterval( function() {

		if ( 'function' === typeof rrssbInit ) {

			rrssbInit();

			if ( ++rrssbInitCount > 5 ) {

				clearInterval( rrssbInitExists );
			}
		}

	}, 1000 );
}

runRrssbInit();

</script>
EOF;

			return $metabox_html;
		}
	}
}
