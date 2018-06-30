<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbGplAdminSharing' ) ) {

	class WpssoRrssbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_cache_rows' => 3,
				'rrssb_buttons_advanced_rows' => 2,
				'post_buttons_rows' => 4,
			), 40 );
		}

		public function filter_plugin_cache_rows( $table_rows, $form, $network = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			SucomUtil::add_before_key( $table_rows, 'plugin_show_purge_count', array(
				'plugin_sharing_buttons_cache_exp' => $form->get_th_html( _x( 'Sharing Buttons HTML Cache Expiry',
					'option label', 'wpsso-rrssb' ), '', 'plugin_sharing_buttons_cache_exp' ) . 
				'<td nowrap class="blank">' . $this->p->options['plugin_sharing_buttons_cache_exp'] . ' ' . 
				_x( 'seconds (0 to disable)', 'option comment', 'wpsso-rrssb' ) . '</td>' . 
				WpssoAdmin::get_option_site_use( 'plugin_sharing_buttons_cache_exp', $form, $network ),
			) );

			return $table_rows;
		}

		public function filter_rrssb_buttons_advanced_rows( $table_rows, $form ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$table_rows['buttons_force_prot'] = $form->get_th_html( _x( 'Force Protocol for Shared URLs',
				'option label', 'wpsso-rrssb' ), '', 'buttons_force_prot' ) . 
			'<td class="blank">' . $form->get_no_select( 'buttons_force_prot', 
				array_merge( array( '' => 'none' ), $this->p->cf['sharing']['force_prot'] ) ) . '</td>';

			$table_rows['plugin_sharing_buttons_cache_exp'] = $form->get_th_html( _x( 'Sharing Buttons HTML Cache Expiry',
				'option label', 'wpsso-rrssb' ), '', 'plugin_sharing_buttons_cache_exp' ) . 
			'<td nowrap class="blank">' . $this->p->options['plugin_sharing_buttons_cache_exp'] . ' ' . 
				_x( 'seconds (0 to disable)', 'option comment', 'wpsso-rrssb' ) . '</td>';

			return $table_rows;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( empty( $mod['post_status'] ) || $mod['post_status'] === 'auto-draft' ) {

				$table_rows['save_a_draft'] = '<td><blockquote class="status-info"><p class="centered">' . 
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'wpsso-rrssb' ), SucomUtil::titleize( $mod['post_type'] ) ) . '</p></td>';

				return $table_rows;	// abort
			}

			$thumb_size_info = SucomUtil::get_size_info( 'thumbnail' );
			$def_cap_title = $this->p->page->get_caption( 'title', 0, $mod, true, false );

			$table_rows[] = '<td colspan="3">' . $this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssorrssb' ) ) . '</td>';

			/**
			 * Disable Buttons Checkbox
			 */
			$form_rows['buttons_disabled'] = array(
				'label' => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-buttons_disabled', 'td_class' => 'blank',
				'content' => $form->get_no_checkbox( 'buttons_disabled' ),
			);

			/**
			 * Email.
			 */
			$email_cap_len  = $this->p->options['email_cap_len'];
			$email_cap_ht   = $this->p->options['email_cap_hashtags'];
			$email_cap_text = $this->p->page->get_caption( 'excerpt', $email_cap_len, $mod, true, $email_cap_ht, true, 'none' );

			$form_rows['subsection_email'] = array(
				'td_class' => 'subsection', 'header' => 'h5', 'label' => 'Email',
			);

			$form_rows['email_title'] = array(
				'label' => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_title', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( $def_cap_title, 'wide' ),
			);

			$form_rows['email_desc'] = array(
				'label' => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $email_cap_text, '', '', $email_cap_len ),
			);

			/**
			 * Twitter.
			 */
			$twitter_cap_type = empty( $this->p->options['twitter_caption'] ) ? 'title' : $this->p->options['twitter_caption'];
			$twitter_cap_len  = $this->p->rrssb_sharing->get_tweet_max_len();
			$twitter_cap_ht   = $this->p->options['twitter_cap_hashtags'];
			$twitter_cap_text = $this->p->page->get_caption( $twitter_cap_type, $twitter_cap_len, $mod, true, $twitter_cap_ht );

			$form_rows['subsection_twitter'] = array(
				'td_class' => 'subsection', 'header' => 'h5', 'label' => 'Twitter',
			);

			$form_rows['twitter_desc'] = array(
				'label' => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-twitter_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $twitter_cap_text, '', '', $twitter_cap_len ),
			);

			/**
			 * Pinterest.
			 */
			$pin_cap_len  = $this->p->options['pin_cap_len'];
			$pin_cap_ht   = $this->p->options['pin_cap_hashtags'];
			$pin_cap_text = $this->p->page->get_caption( 'excerpt', $pin_cap_len, $mod, true, $pin_cap_ht );
			$pin_media    = $this->p->og->get_media_info( $this->p->lca . '-pinterest-button', array( 'pid', 'img_url' ), $mod, 'schema' );
			$force_regen  = $this->p->util->is_force_regen( $mod, 'schema' );	// False by default.

			if ( ! empty( $pin_media['pid'] ) ) {
				list(
					$pin_media['img_url'],
					$img_width,
					$img_height,
					$img_cropped,
					$img_pid
				) = $this->p->media->get_attachment_image_src( $pin_media['pid'], 'thumbnail', false, $force_regen ); 
			}

			$form_rows['subsection_pinterest'] = array(
				'td_class' => 'subsection', 'header' => 'h5', 'label' => 'Pinterest',
			);

			$form_rows['pin_desc'] = array(
				'label' => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-pin_desc', 'td_class' => 'blank top',
				'content' => $form->get_no_textarea_value( $pin_cap_text, '', '', $pin_cap_len ) . 
					( empty( $pin_media['img_url'] ) ? '' : '</td><td class="top thumb_preview">' . 
						'<img src="' . $pin_media['img_url'] . '" style="max-width:' . $thumb_size_info['width'] . 'px;">' ),
			);

			/**
			 * Other Title / Caption Input
			 */
			foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit' => 'Reddit',
				'tumblr' => 'Tumblr',
			) as $opt_pre => $name ) {

				$other_cap_len  = $this->p->options[$opt_pre . '_cap_len'];
				$other_cap_ht   = $this->p->options[$opt_pre . '_cap_hashtags'];
				$other_cap_text = $this->p->page->get_caption( 'excerpt', $other_cap_len, $mod, true, $other_cap_ht );

				$form_rows['subsection_' . $opt_pre] = array(
					'td_class' => 'subsection', 'header' => 'h5', 'label' => $name,
				);

				$form_rows[$opt_pre . '_title'] = array(
					'label' => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'th_class' => 'medium', 'tooltip' => 'post-' . $opt_pre . '_title', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $def_cap_title, 'wide' ),
				);

				$form_rows[$opt_pre . '_desc'] = array(
					'label' => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'th_class' => 'medium', 'tooltip' => 'post-' . $opt_pre . '_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $other_cap_text, '', '', $other_cap_len ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}
	}
}

