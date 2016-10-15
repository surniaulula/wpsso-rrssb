<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbGplAdminSharing' ) ) {

	class WpssoRrssbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'post_buttons_rows' => 4,		// $table_rows, $form, $head, $mod
			), 40 );
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			if ( empty( $mod['post_status'] ) || $mod['post_status'] === 'auto-draft' ) {
				$table_rows['save_a_draft'] = '<td><blockquote class="status-info"><p class="centered">'.
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'wpsso-rrssb' ), SucomUtil::titleize( $mod['post_type'] ) ).'</p></td>';
				return $table_rows;	// abort
			}

			$size_info = SucomUtil::get_size_info( 'thumbnail' );
			$title_caption = $this->p->webpage->get_caption( 'title', 0, $mod, true, false );

			$table_rows[] = '<td colspan="3" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssorrssb' ) ).'</td>';

			/*
			 * Email
			 */
			$caption_len = $this->p->options['email_cap_len'];
			$caption_text = $this->p->webpage->get_caption( 'excerpt', $caption_len, 
				$mod, true, $this->p->options['email_cap_hashtags'], true, 'none' );

			$form_rows['email_title'] = array(
				'label' => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_title', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( $title_caption, 'wide' ),
			);
			$form_rows['email_desc'] = array(
				'label' => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
			);

			/*
			 * Twitter
			 */
			$caption_len = $this->p->rrssb->get_tweet_max_len();
			$caption_text = $this->p->webpage->get_caption( 'title', $caption_len, 
				$mod, true, $this->p->options['twitter_cap_hashtags'] );

			$form_rows['twitter_desc'] = array(
				'label' => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-twitter_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
			);

			/*
			 * Pinterest
			 */
			$caption_len = $this->p->options['pin_cap_len'];
			$caption_text = $this->p->webpage->get_caption( 'excerpt', $caption_len, 
				$mod, true, $this->p->options['pin_cap_hashtags'] );

			$media = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-pinterest-button',
				array( 'pid', 'img_url' ), $mod, 'rp' );

			if ( ! empty( $media['pid'] ) ) {
				list(
					$media['img_url'],
					$img_width,
					$img_height,
					$img_cropped,
					$img_pid
				) = $this->p->media->get_attachment_image_src( $media['pid'], 'thumbnail', false ); 
			}

			$form_rows['pin_desc'] = array(
				'label' => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-pin_desc', 'td_class' => 'blank top',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ).
					( empty( $media['img_url'] ) ? '' : '</td><td class="top thumb_preview">'.
					'<img src="'.$media['img_url'].'" style="max-width:'.$size_info['width'].'px;">' ),
			);

			/*
			 * Generic Title / Caption Input
			 */
			Foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit' => 'Reddit',
				'tumblr' => 'Tumblr',
			) as $opt_prefix => $name ) {

				$caption_len = $this->p->options[$opt_prefix.'_cap_len'];
				$caption_text = $this->p->webpage->get_caption( 'excerpt', $caption_len,
					$mod, true, $this->p->options[$opt_prefix.'_cap_hashtags'] );

				$form_rows[$opt_prefix.'_title'] = array(
					'label' => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'th_class' => 'medium', 'tooltip' => 'post-'.$opt_prefix.'_title', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $title_caption, 'wide' ),
				);

				$form_rows[$opt_prefix.'_desc'] = array(
					'label' => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'th_class' => 'medium', 'tooltip' => 'post-'.$opt_prefix.'_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
				);
			}

			/*
			 * Disable Buttons Checkbox
			 */
			$form_rows['buttons_disabled'] = array(
				'label' => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-rrssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-buttons_disabled', 'td_class' => 'blank',
				'content' => $form->get_no_checkbox( 'buttons_disabled' ),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}
	}
}

?>
