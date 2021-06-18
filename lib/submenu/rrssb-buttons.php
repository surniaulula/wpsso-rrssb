<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
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

			$this->set_objects();

			$this->p->util->add_plugin_filters( $this, array(
				'form_button_rows' => 2,	// Filter form buttons for all settings pages.
			) );
		}

		private function set_objects() {

			foreach ( $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ] as $id => $name ) {

				$classname = WpssoRrssbConfig::load_lib( false, 'share/' . $id, 'wpssorrssbsubmenushare' . $id );

				if ( false !== $classname && class_exists( $classname ) ) {

					$this->share[ $id ] = new $classname( $this->p );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $classname . ' class loaded' );
					}
				}
			}
		}

		public function filter_form_button_rows( $form_button_rows, $menu_id ) {

			$row_num = null;

			switch ( $menu_id ) {

				case 'tools':

					$row_num = 2;

					break;
			}

			if ( null !== $row_num ) {

				$form_button_rows[ $row_num ][ 'reload_default_rrssb_buttons' ] = _x( 'Reload Default Responsive Buttons',
					'submit button', 'wpsso-rrssb' );
			}

			return $form_button_rows;
		}

		/**
		 * Called by the extended WpssoAdmin class.
		 */
		protected function add_meta_boxes() {

			$rrssb =& WpssoRrssb::get_instance();

			$metabox_id      = 'general';
			$metabox_title   = _x( 'Social Sharing Buttons', 'metabox title', 'wpsso-rrssb' );
			$metabox_screen  = $this->pagehook;
			$metabox_context = 'normal';
			$metabox_prio    = 'default';
			$callback_args   = array(	// Second argument passed to the callback function / method.
				'page_id'    => SucomUtil::sanitize_hookname( $this->menu_id ),
				'metabox_id' => $metabox_id,
			);

			add_meta_box( $this->pagehook . '_' . $metabox_id, $metabox_title,
				array( $this, 'show_metabox_table' ), $metabox_screen,
					$metabox_context, $metabox_prio, $callback_args );

			$share_ids = $rrssb->social->get_share_ids( $this->share );

			foreach ( $share_ids as $share_id => $share_title ) {

				$share_title     = $share_title == 'GooglePlus' ? 'Google+' : $share_title;
				$metabox_screen  = $this->pagehook;
				$metabox_context = 'normal';
				$metabox_prio    = 'default';
				$callback_args   = array(	// Second argument passed to the callback function / method.
					'share_id'    => $share_id,
					'share_title' => $share_title,
				);

				add_meta_box( $this->pagehook . '_' . $share_id, $share_title,
					array( $this, 'show_metabox_rrssb_share' ), $metabox_screen,
						$metabox_context, $metabox_prio, $callback_args );

				add_filter( 'postbox_classes_' . $this->pagehook . '_' . $this->pagehook . '_' . $share_id, array( $this, 'add_class_postbox_rrssb_share' ) );
			}
		}

		public function add_class_postbox_rrssb_share( $classes ) {

			$show_opts = WpssoUser::show_opts();

			$classes[] = 'postbox-rrssb_share';

			if ( ! empty( $show_opts ) ) {

				$classes[] = 'postbox-show_' . $show_opts;
			}

			return $classes;
		}

		public function show_metabox_rrssb_share( $post, $callback ) {

			$callback_args = $callback[ 'args' ];

			$metabox_id = 'rrssb_share';

			$metabox_tabs  = apply_filters( 'wpsso_' . $metabox_id . '_' . $callback_args[ 'share_id' ] . '_tabs', array() );

			if ( empty( $metabox_tabs ) ) {

				$this->p->util->metabox->do_table( apply_filters( 'wpsso_' . $metabox_id . '_' . $callback_args[ 'share_id' ] . '_rows',
					array(), $this->form, $this ), 'metabox-' . $metabox_id . '-' . $callback_args[ 'share_id' ], 'metabox-' . $metabox_id );

			} else {

				foreach ( $metabox_tabs as $tab => $title ) {

					$table_rows[ $tab ] = apply_filters( 'wpsso_' . $metabox_id . '_' . $callback_args[ 'share_id' ] . '_' . $tab . '_rows',
						array(), $this->form, $this );
				}

				$this->p->util->metabox->do_tabbed( $metabox_id . '_' . $callback_args[ 'share_id' ], $metabox_tabs, $table_rows );
			}
		}

		protected function get_table_rows( $page_id, $metabox_id ) {

			$table_rows = array();

			switch ( $page_id . '-' . $metabox_id ) {

				case 'rrssb_buttons-general':

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

					$table_rows[ 'buttons_pos_content' ] = '' .
						$this->form->get_th_html( _x( 'Position in Content', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_pos_content' ) . 
						'<td>' . $this->form->get_select( 'buttons_pos_content', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

					$table_rows[ 'buttons_pos_excerpt' ] = $this->form->get_tr_hide( 'basic', 'buttons_pos_excerpt' ) .
						$this->form->get_th_html( _x( 'Position in Excerpt', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_pos_excerpt' ) . 
						'<td>' . $this->form->get_select( 'buttons_pos_excerpt', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

					$table_rows[ 'buttons_force_prot' ] = $this->form->get_tr_hide( 'basic', 'buttons_force_prot' ) .
						$this->form->get_th_html( _x( 'Force Protocol for Shared URLs', 'option label', 'wpsso-rrssb' ),
							$css_class = '', $css_id = 'buttons_force_prot' ) . 
						'<td>' . $this->form->get_select( 'buttons_force_prot',
							array_merge( array( '' => 'none' ), $this->p->cf[ 'sharing' ][ 'force_prot' ] ) ) . '</td>';

					break;
			}

			return $table_rows;
		}

		public function show_on_checkboxes( $opt_pre ) {

			$col = 0;

			$max_cols = 6;

			$show_on = apply_filters( 'wpsso_rrssb_buttons_show_on', $this->p->cf[ 'sharing' ][ 'show_on' ], $opt_pre );

			$html = '<table>';

			foreach ( $show_on as $opt_suffix => $short_desc ) {

				$css_class = isset( $this->p->options[ $opt_pre . '_on_' . $opt_suffix . ':is' ] ) &&
					$this->p->options[ $opt_pre . '_on_' . $opt_suffix . ':is' ] === 'disabled' ?
						'rrssb_show_on blank' : 'rrssb_show_on';

				$col++;

				if ( $col == 1 ) {

					$html .= '<tr><td class="' . $css_class . '">';

				} else {

					$html .= '<td class="' . $css_class . '">';
				}

				$html .= $this->form->get_checkbox( $opt_pre . '_on_' . $opt_suffix ) . 
					_x( $short_desc, 'option value', 'wpsso-rrssb' ) . '&nbsp; ';

				if ( $col == $max_cols ) {

					$html .= '</td></tr>';

					$col = 0;

				} else {

					$html .= '</td>';
				}
			}

			$html .= $col < $max_cols ? '</tr>' : '';

			$html .= '</table>';

			return $html;
		}
	}
}
