<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuRrssbButtons' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuRrssbButtons extends WpssoAdmin {

		public $website = array();

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			$this->set_objects();
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['wpssorrssb']['lib']['website'] as $id => $name ) {
				$classname = WpssoRrssbConfig::load_lib( false, 'website/'.$id, 'wpssorrssbsubmenuwebsite'.$id );
				if ( $classname !== false && class_exists( $classname ) ) {
					$this->website[$id] = new $classname( $this->p );
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $classname.' class loaded' );
					}
				}
			}
		}

		/**
		 * Called by the extended WpssoAdmin class.
		 */
		protected function add_meta_boxes() {

			add_meta_box( $this->pagehook.'_rrssb_buttons',
				_x( 'Social Sharing Buttons', 'metabox title', 'wpsso-rrssb' ),
					array( $this, 'show_metabox_rrssb_buttons' ), $this->pagehook, 'normal' );

			$ids = $this->p->rrssb_sharing->get_website_object_ids( $this->website );

			foreach ( $ids as $id => $name ) {

				$name = $name == 'GooglePlus' ? 'Google+' : $name;
				$pos_id = 'normal';
				$prio = 'default';
				$args = array( 'id' => $id, 'name' => $name );

				add_meta_box( $this->pagehook.'_'.$id, $name, 
					array( $this, 'show_metabox_rrssb_website' ), $this->pagehook, $pos_id, $prio, $args );

				add_filter( 'postbox_classes_'.$this->pagehook.'_'.$this->pagehook.'_'.$id, 
					array( $this, 'add_class_postbox_rrssb_website' ) );
			}

			// close all website metaboxes by default
			WpssoUser::reset_metabox_prefs( $this->pagehook, array_keys( $ids ), 'closed' );
		}

		public function add_class_postbox_rrssb_website( $classes ) {
			$show_opts = WpssoUser::show_opts();
			$classes[] = 'postbox-rrssb_website';
			if ( ! empty( $show_opts ) ) {
				$classes[] = 'postbox-show_'.$show_opts;
			}
			return $classes;
		}

		public function show_metabox_rrssb_buttons() {

			$metabox_id = 'rrssb_buttons';

			$tabs = apply_filters( $this->p->lca.'_rrssb_buttons_tabs', array(
				'include' => _x( 'Include Buttons', 'metabox tab', 'wpsso-rrssb' ),
				'position' => _x( 'Buttons Position', 'metabox tab', 'wpsso-rrssb' ),
				'advanced' => _x( 'Advanced Settings', 'metabox tab', 'wpsso-rrssb' ),
			) );

			$table_rows = array();

			foreach ( $tabs as $tab_key => $title ) {
				$table_rows[$tab_key] = array_merge( $this->get_table_rows( $metabox_id, $tab_key ), 
					apply_filters( $this->p->lca.'_'.$metabox_id.'_'.$tab_key.'_rows', array(), $this->form ) );
			}

			$this->p->util->do_metabox_tabbed( $metabox_id, $tabs, $table_rows );
		}

		public function show_metabox_rrssb_website( $post, $callback ) {

			$args = $callback['args'];
			$metabox_id = 'rrssb_website';
			$tabs = apply_filters( $this->p->lca.'_'.$metabox_id.'_'.$args['id'].'_tabs', array() );

			if ( empty( $tabs ) ) {
				$this->p->util->do_metabox_table( apply_filters( $this->p->lca.'_'.$metabox_id.'_'.$args['id'].'_rows',
					array(), $this->form, $this ), 'metabox-'.$metabox_id.'-'.$args['id'], 'metabox-'.$metabox_id );
			} else {
				foreach ( $tabs as $tab => $title ) {
					$table_rows[$tab] = apply_filters( $this->p->lca.'_'.$metabox_id.'_'.$args['id'].'_'.$tab.'_rows',
						array(), $this->form, $this );
				}
				$this->p->util->do_metabox_tabbed( $metabox_id.'_'.$args['id'], $tabs, $table_rows );
			}
		}

		protected function get_table_rows( $metabox_id, $tab_key ) {

			$table_rows = array();

			switch ( $metabox_id.'-'.$tab_key ) {

				case 'rrssb_buttons-include':

					$table_rows[] = $this->form->get_th_html( _x( 'Include on Archive Webpages',
						'option label', 'wpsso-rrssb' ), '', 'buttons_on_index' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_index' ).'</td>';

					$table_rows[] = $this->form->get_th_html( _x( 'Include on Static Front Page',
						'option label', 'wpsso-rrssb' ), '', 'buttons_on_front' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_front' ).'</td>';

					$table_rows[] = $this->form->get_th_html( _x( 'Include on Post Types',
						'option label', 'wpsso-rrssb' ), '', 'buttons_add_to' ).
					'<td>'.$this->form->get_checklist_post_types( 'buttons_add_to' ).'</td>';

					break;

				case 'rrssb_buttons-position':

					$table_rows[] = $this->form->get_th_html( _x( 'Position in Content Text',
						'option label', 'wpsso-rrssb' ), '', 'buttons_pos_content' ).
					'<td>'.$this->form->get_select( 'buttons_pos_content', $this->p->cf['sharing']['position'] ).'</td>';

					$table_rows[] = $this->form->get_th_html( _x( 'Position in Excerpt Text',
						'option label', 'wpsso-rrssb' ), '', 'buttons_pos_excerpt' ).
					'<td>'.$this->form->get_select( 'buttons_pos_excerpt', $this->p->cf['sharing']['position'] ).'</td>';

					break;
			}

			return $table_rows;
		}

		public function show_on_checkboxes( $opt_prefix ) {

			$col     = 0;
			$max     = 6;
			$html    = '<table>';
			$has_pp  = $this->p->check->pp( 'wpssorrssb', true, $this->p->avail['*']['p_dir'] );
			$show_on = apply_filters( $this->p->lca.'_rrssb_buttons_show_on', $this->p->cf['sharing']['show_on'], $opt_prefix );

			foreach ( $show_on as $opt_suffix => $short_desc ) {

				$css_class = isset( $this->p->options[$opt_prefix.'_on_'.$opt_suffix.':is'] ) &&
					$this->p->options[$opt_prefix.'_on_'.$opt_suffix.':is'] === 'disabled' &&
						! $has_pp ? 'show_on blank' : 'show_on';

				$col++;

				if ( $col == 1 ) {
					$html .= '<tr><td class="'.$css_class.'">';
				} else {
					$html .= '<td class="'.$css_class.'">';
				}

				$html .= $this->form->get_checkbox( $opt_prefix.'_on_'.$opt_suffix ).
					_x( $short_desc, 'option value', 'wpsso-rrssb' ).'&nbsp; ';

				if ( $col == $max ) {
					$html .= '</td></tr>';
					$col = 0;
				} else {
					$html .= '</td>';
				}
			}

			$html .= $col < $max ? '</tr>' : '';
			$html .= '</table>';

			return $html;
		}
	}
}
