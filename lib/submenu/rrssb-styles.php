<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuRrssbStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuRrssbStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;
		}

		protected function add_plugin_hooks() {
			$this->p->util->add_plugin_filters( $this, array(
				'action_buttons' => 1,
			) );
		}

		/**
		 * Called by the extended WpssoAdmin class.
		 */
		protected function add_meta_boxes() {
			add_meta_box( $this->pagehook.'_sharing_styles',
				_x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' ),
					array( $this, 'show_metabox_sharing_styles' ), $this->pagehook, 'normal' );
		}

		public function filter_action_buttons( $action_buttons ) {

			$action_buttons[0]['reload_default_sharing_rrssb_styles'] = _x( 'Reload Default Styles', 'submit button', 'wpsso-rrssb' );

			return $action_buttons;
		}

		public function show_metabox_sharing_styles() {

			$metabox_id = 'sharing-styles';

			if ( file_exists( WpssoRrssbSharing::$sharing_css_file ) && ( $fsize = filesize( WpssoRrssbSharing::$sharing_css_file ) ) !== false ) {
				$css_min_msg = ' <a href="'.WpssoRrssbSharing::$sharing_css_url.'">minified css is '.$fsize.' bytes</a>';
			} else {
				$css_min_msg = '';
			}

			$this->p->util->do_metabox_table( array( 
				$this->form->get_th_html( _x( 'Use the Social Stylesheet',
					'option label', 'wpsso-rrssb' ), 'highlight', 'buttons_use_social_style' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_style' ).$css_min_msg.'</td>',

				$this->form->get_th_html( _x( 'Enqueue the Stylesheet',
					'option label', 'wpsso-rrssb' ), null, 'buttons_enqueue_social_style' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_style' ).'</td>',
			) );

			$table_rows  = array();

			$styles_tabs = apply_filters( $this->p->lca.'_rrssb_styles_tabs', $this->p->cf['sharing']['rrssb_styles'] );

			foreach ( $styles_tabs as $tab_key => $title ) {

				$styles_tabs[$tab_key] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// translate the tab title

				$table_rows[$tab_key] = array_merge( $this->get_table_rows( $metabox_id, $tab_key ), 
					apply_filters( $this->p->lca.'_'.$metabox_id.'_'.$tab_key.'_rows', array(), $this->form ) );
			}

			$this->p->util->do_metabox_tabbed( $metabox_id, $styles_tabs, $table_rows );
		}

		protected function get_table_rows( $metabox_id, $tab_key ) {

			$table_rows['buttons_css_'.$tab_key] = '<th class="textinfo">'.$this->p->msgs->get( 'info-styles-'.$tab_key ).'</th>'.
			'<td'.( isset( $this->p->options['buttons_css_'.$tab_key.':is'] ) &&
				$this->p->options['buttons_css_'.$tab_key.':is'] === 'disabled' ? ' class="blank"' : '' ).'>'.
			$this->form->get_textarea( 'buttons_css_'.$tab_key, 'button_css code' ).'</td>';

			return $table_rows;
		}
	}
}
