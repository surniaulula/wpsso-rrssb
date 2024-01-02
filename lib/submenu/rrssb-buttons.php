<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuRrssbButtons' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuRrssbButtons extends WpssoAdmin {

		public $share = array();

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
				'general' => _x( 'Social Sharing Buttons', 'metabox title', 'wpsso-rrssb' ),
			);

			$this->p->util->add_plugin_filters( $this, array( 'form_button_rows' => 2 ) );
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			switch ( $menu_id ) {

				case 'tools':

					$form_button_rows[ 2 ][ 'reload_default_rrssb_buttons' ] = _x( 'Reload Default Responsive Buttons', 'submit button', 'wpsso-rrssb' );

					break;
			}

			return $form_button_rows;
		}

		/*
		 * Add metaboxes for this settings page.
		 *
		 * See WpssoAdmin->load_settings_page().
		 */
		protected function add_settings_page_metaboxes( $callback_args = array() ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			parent::add_settings_page_metaboxes( $callback_args );

			$this->set_share_objects();

			$rrssb =& WpssoRrssb::get_instance();

			$share_ids = $rrssb->social->get_share_ids( $this->share );

			foreach ( $share_ids as $metabox_id => $metabox_title ) {

				$metabox_screen  = $this->pagehook;
				$metabox_context = 'normal';
				$metabox_prio    = 'default';
				$callback_args   = array(	// Second argument passed to the callback function / method.
					'page_id'       => $this->menu_id,
					'metabox_id'    => $metabox_id,
					'metabox_title' => $metabox_title,
				);

				add_meta_box( $this->pagehook . '_' . $metabox_id, $metabox_title,
					array( $this, 'show_metabox_rrssb_share' ), $metabox_screen,
						$metabox_context, $metabox_prio, $callback_args );

				add_filter( 'postbox_classes_' . $this->pagehook . '_' . $this->pagehook . '_' . $metabox_id,
					array( $this, 'add_class_postbox_rrssb_share' ) );
			}
		}

		private function set_share_objects() {

			foreach ( $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ] as $id => $name ) {

				$classname = WpssoRrssbConfig::load_lib( false, 'share/' . $id, 'wpssorrssbsubmenushare' . $id );

				if ( false !== $classname && class_exists( $classname ) ) {

					$this->share[ $id ] = new $classname( $this->p, $this );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $classname . ' class loaded' );
					}
				}
			}
		}

		public function show_metabox_rrssb_share( $obj, $mb ) {

			$this->show_metabox_tabbed( $obj, $mb, $tabs = array() );
		}

		public function add_class_postbox_rrssb_share( $classes ) {

			$show_opts = WpssoUser::show_opts();

			$classes[] = 'postbox-rrssb_share';

			if ( ! empty( $show_opts ) ) {

				$classes[] = 'postbox-show_' . $show_opts;
			}

			return $classes;
		}

		protected function get_table_rows( $page_id, $metabox_id, $tab_key = '', $args = array() ) {

			$table_rows = array();
			$match_rows = trim( $page_id . '-' . $metabox_id . '-' . $tab_key, '-' );

			switch ( $match_rows ) {

				case 'rrssb-buttons-general':

					$table_rows[ 'buttons_on_archive' ] = '' .
						$this->form->get_th_html( _x( 'Include on Archive Webpages', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_on_archive' ) .
						'<td>' . $this->form->get_checkbox( 'buttons_on_archive' ) . '</td>';

					$table_rows[ 'buttons_on_front' ] = '' .
						$this->form->get_th_html( _x( 'Include on Static Homepage', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_on_front' ) .
						'<td>' . $this->form->get_checkbox( 'buttons_on_front' ) . '</td>';

					$table_rows[ 'buttons_add_to' ] = '' .
						$this->form->get_th_html( _x( 'Include on Post Types', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_add_to' ) .
						'<td>' . $this->form->get_checklist_post_types( $name_prefix = 'buttons_add_to' ) . '</td>';

					$table_rows[ 'buttons_cta' ] = '' .
						$this->form->get_th_html_locale( _x( 'Call to Action', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_cta' ) .
						'<td>' . $this->form->get_input_locale( 'buttons_cta', $class_class = 'wide' ) . '</td>';

					$table_rows[ 'buttons_pos_content' ] = '' .
						$this->form->get_th_html( _x( 'Buttons Position in Content', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_pos_content' ) .
						'<td>' . $this->form->get_select( 'buttons_pos_content', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

					$table_rows[ 'buttons_pos_excerpt' ] = $this->form->get_tr_hide( $in_view = 'basic', 'buttons_pos_excerpt' ) .
						$this->form->get_th_html( _x( 'Buttons Position in Excerpt', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_pos_excerpt' ) .
						'<td>' . $this->form->get_select( 'buttons_pos_excerpt', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

					$filter_name = SucomUtil::sanitize_hookname( 'wpsso_' . $page_id . '_position_rows' );

					$table_rows = apply_filters( $filter_name, $table_rows, $this->form, $network = false );

					$table_rows[ 'buttons_force_prot' ] = $this->form->get_tr_hide( $in_view = 'basic', 'buttons_force_prot' ) .
						$this->form->get_th_html( _x( 'Force Protocol for Shared URLs', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_force_prot' ) .
						'<td>' . $this->form->get_select_none( 'buttons_force_prot', $this->p->cf[ 'sharing' ][ 'force_prot' ] ) . '</td>';

					$table_rows[ 'buttons_utm_medium' ] = $this->form->get_tr_hide( $in_view = 'basic', 'buttons_utm_medium' ) .
						$this->form->get_th_html( _x( 'UTM Medium for All Buttons', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_utm_medium' ) .
						'<td>' . $this->form->get_input( 'buttons_utm_medium' ) . '</td>';

					break;
			}

			return $table_rows;
		}

		public function get_show_on_checkboxes( $opt_pre ) {

			$col      = 0;
			$max_cols = 4;
			$show_on  = apply_filters( 'wpsso_rrssb_buttons_show_on', $this->p->cf[ 'sharing' ][ 'show_on' ] );
			$html     = '<table>';

			foreach ( $show_on as $opt_suffix => $short_desc ) {

				$col++;

				$css_class = 'rrssb_show_on' . ( empty( $this->p->options[ $opt_pre . '_on_' . $opt_suffix . ':disabled' ] ) ? '' : ' blank' );

				$html .= ( $col == 1 ? '<tr>' : '' ) . '<td class="' . $css_class . '">';
				$html .= $this->form->get_checkbox( $opt_pre . '_on_' . $opt_suffix ) . _x( $short_desc, 'option value', 'wpsso-rrssb' ) . '&nbsp; ';
				$html .= '</td>';

				if ( $col === $max_cols ) {

					$col = 0;

					$html .= '</tr>';
				}
			}

			$html .= $col < $max_cols ? '</tr>' : '';
			$html .= '</table>';

			return $html;
		}
	}
}
