<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuRrssbStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuRrssbStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->menu_id   = $id;
			$this->menu_name = $name;
			$this->menu_lib  = $lib;
			$this->menu_ext  = $ext;

			$this->menu_metaboxes = array(
				'rrssb_styles' => _x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' ),
			);

			$this->p->util->add_plugin_filters( $this, array( 'form_button_rows' => 2 ) );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			switch ( $menu_id ) {

				case 'tools':

					$form_button_rows[ 2 ][ 'reload_default_rrssb_styles' ] = _x( 'Reload Default Responsive Styles', 'submit button', 'wpsso-rrssb' );

					break;
			}

			return $form_button_rows;
		}

		/*
		 * Remove the "Change to View" button from this settings page.
		 */
		protected function add_form_buttons_change_show_options( &$form_button_rows ) {
		}

		public function show_metabox_rrssb_styles( $obj, $mb ) {

			$metabox_id       = isset( $mb[ 'args' ][ 'metabox_id' ] ) ? $mb[ 'args' ][ 'metabox_id' ] : '';
			$sharing_css_path = WpssoRrssbSocial::get_sharing_css_path();
			$sharing_css_url  = WpssoRrssbSocial::get_sharing_css_url();
			$css_min_msg      = '';

			if ( file_exists( $sharing_css_path ) && false !== ( $sharing_css_fsize = filesize( $sharing_css_path ) ) ) {

				$css_min_msg = ' <a href="' . $sharing_css_url . '">minified css is ' . $sharing_css_fsize . ' bytes</a>';
			}

			$table_rows  = array();

			$table_rows[ 'buttons_use_social_style' ] = ''.
				$this->form->get_th_html( _x( 'Use the Social Stylesheet', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_use_social_style' ) .
				'<td>' . $this->form->get_checkbox( 'buttons_use_social_style' ) . $css_min_msg . '</td>';

			$table_rows[ 'buttons_enqueue_social_style' ] = ''.
				$this->form->get_th_html( _x( 'Enqueue the Stylesheet', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_enqueue_social_style' ) .
				'<td>' . $this->form->get_checkbox( 'buttons_enqueue_social_style' ) . '</td>';

			$this->p->util->metabox->do_table( $table_rows, $class_href_key = 'metabox-info metabox-' . $metabox_id . '-info' );

			$tabs = $this->p->cf[ 'sharing' ][ 'rrssb_styles' ];

			$this->show_metabox_tabbed( $obj, $mb, $tabs );
		}

		protected function get_table_rows( $page_id, $metabox_id, $tab_key = '', $args = array() ) {

			$table_rows = array();

			$table_rows[ 'buttons_css_' . $tab_key ] = '' .
				'<th class="textinfo">' . $this->p->msgs->get( 'info-styles-' . $tab_key ) . '</th>' .
				'<td' . ( empty( $this->p->options[ 'buttons_css_' . $tab_key . ':disabled' ] ) ? '' : ' class="blank"' ) . '>' .
				$this->form->get_textarea( 'buttons_css_' . $tab_key, 'button_css code' ) . '</td>';

			return $table_rows;
		}
	}
}
