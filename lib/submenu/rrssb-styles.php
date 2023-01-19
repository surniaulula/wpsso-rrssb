<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2023 Jean-Sebastien Morisset (https://wpsso.com/)
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

			$this->p->util->add_plugin_filters( $this, array(
				'form_button_rows' => 2,	// Form buttons for all settings pages.
			) );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			switch ( $menu_id ) {

				case 'rrssb-styles':

					if ( isset( $form_button_rows[ 0 ] ) ) {

						$form_button_rows[ 0 ] = SucomUtil::preg_grep_keys( '/^change_show_options/', $form_button_rows[ 0 ], $invert = true );
					}

					break;

				case 'tools':

					$form_button_rows[ 2 ][ 'reload_default_rrssb_styles' ] = _x( 'Reload Default Responsive Styles', 'submit button', 'wpsso-rrssb' );

					break;
			}

			return $form_button_rows;
		}

		/*
		 * Called by the extended WpssoAdmin class.
		 */
		protected function add_meta_boxes() {

			$metabox_id      = 'sharing_styles';
			$metabox_title   = _x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' );
			$metabox_screen  = $this->pagehook;
			$metabox_context = 'normal';
			$metabox_prio    = 'default';
			$callback_args   = array(	// Second argument passed to the callback function / method.
			);

			add_meta_box( $this->pagehook . '_' . $metabox_id, $metabox_title,
				array( $this, 'show_metabox_sharing_styles' ), $metabox_screen,
					$metabox_context, $metabox_prio, $callback_args );
		}

		public function show_metabox_sharing_styles() {

			$metabox_id = 'rrssb_styles';

			$sharing_css_path = WpssoRrssbSocial::get_sharing_css_path();
			$sharing_css_url  = WpssoRrssbSocial::get_sharing_css_url();

			if ( file_exists( $sharing_css_path ) && false !== ( $sharing_css_fsize = filesize( $sharing_css_path ) ) ) {

				$css_min_msg = ' <a href="' . $sharing_css_url . '">minified css is ' . $sharing_css_fsize . ' bytes</a>';

			} else {

				$css_min_msg = '';
			}

			/*
			 * Responsive styles options.
			 */
			$table_rows  = array();

			$table_rows[] = ''.
				$this->form->get_th_html( _x( 'Use the Social Stylesheet', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_use_social_style' ) .
				'<td>' . $this->form->get_checkbox( 'buttons_use_social_style' ) . $css_min_msg . '</td>';

			$table_rows[] = ''.
				$this->form->get_th_html( _x( 'Enqueue the Stylesheet', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_enqueue_social_style' ) .
				'<td>' . $this->form->get_checkbox( 'buttons_enqueue_social_style' ) . '</td>';

			$this->p->util->metabox->do_table( $table_rows, $class_href_key = 'metabox-info metabox-' . $metabox_id . '-info' );

			/*
			 * Responsive styles tabs.
			 */
			$table_rows  = array();

			$tabs = apply_filters( 'wpsso_' . $metabox_id . '_tabs', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

			foreach ( $tabs as $tab_key => $title ) {

				$tabs[ $tab_key ] = _x( $title, 'metabox tab', 'wpsso-rrssb' );	// Translate the tab title.

				$filter_name = SucomUtil::sanitize_hookname( 'wpsso_' . $metabox_id . '_' . $tab_key . '_rows' );

				$table_rows[ $tab_key ] = $this->get_table_rows( $metabox_id, $tab_key );

				$table_rows[ $tab_key ] = apply_filters( $filter_name, $table_rows[ $tab_key ], $this->form, $network = false );
			}

			$this->p->util->metabox->do_tabbed( $metabox_id, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox_id, $tab_key ) {

			$table_rows[ 'buttons_css_' . $tab_key ] = '' .
				'<th class="textinfo">' . $this->p->msgs->get( 'info-styles-' . $tab_key ) . '</th>' .
				'<td' . ( empty( $this->p->options[ 'buttons_css_' . $tab_key . ':disabled' ] ) ? '' : ' class="blank"' ) . '>' .
				$this->form->get_textarea( 'buttons_css_' . $tab_key, 'button_css code' ) . '</td>';

			return $table_rows;
		}
	}
}
