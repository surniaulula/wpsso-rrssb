<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingButtons' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuSharingButtons extends WpssoAdmin {

		public $website = array();

		protected $website_id = '';
		protected $website_name = '';

		public function __construct( &$plugin, $id, $name, $lib ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->set_objects();
			$this->p->util->add_plugin_filters( $this, array(
				'messages_tooltip' => 2,
			) );
		}

		private function set_objects() {
			foreach ( $this->p->cf['*']['lib']['website'] as $id => $name ) {
				$classname = WpssoRrssbConfig::load_lib( false, 'website/'.$id, 'wpssorrssbsubmenusharing'.$id );
				if ( $classname !== false && class_exists( $classname ) )
					$this->website[$id] = new $classname( $this->p, $id, $name );
			}
		}

		public function filter_messages_tooltip( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 )
				return $text;

			switch ( $idx ) {
				case ( strpos( $idx, 'tooltip-buttons_pos_' ) === false ? false : true ):
					$text = sprintf( __( 'Social sharing buttons can be added to the top, bottom, or both. Each sharing button must also be enabled below (see the <em>%s</em> options).', 'wpsso-rrssb' ), _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) );
					break;
				case 'tooltip-buttons_on_index':
					$text = __( 'Add the social sharing buttons to each entry of an index webpage (for example, <strong>non-static</strong> homepage, category, archive, etc.). Social sharing buttons are not included on index webpages by default.', 'wpsso-rrssb' );
					break;
				case 'tooltip-buttons_on_front':
					$text = __( 'If a static Post or Page has been selected for the homepage, you can add the social sharing buttons to that static homepage as well (default is unchecked).', 'wpsso-rrssb' );
					break;
				case 'tooltip-buttons_add_to':
					$text = __( 'Enabled social sharing buttons are added to the Post, Page, Media, and Product webpages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the appropriate option(s) here.', 'wpsso-rrssb' );
					break;
			}
			return $text;
		}

		protected function add_meta_boxes() {
			$col = 0;
			$row = 0;

			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_sharing_buttons',
				_x( 'Social Sharing Buttons', 'metabox title', 'wpsso-rrssb' ),
					array( &$this, 'show_metabox_sharing_buttons' ), $this->pagehook, 'normal' );

			foreach ( $this->p->cf['*']['lib']['website'] as $id => $name ) {
				$classname = 'wpssorrssbsubmenusharing'.$id;
				if ( class_exists( $classname ) ) {
					$pos_id = 'normal';
					$name = $name == 'GooglePlus' ? 'Google+' : $name;

					add_meta_box( $this->pagehook.'_'.$id, $name, 
						array( &$this->website[$id], 'show_metabox_website' ), $this->pagehook, $pos_id );

					$this->website[$id]->form = &$this->get_form_reference();
				}
			}

			// these metabox ids should be closed by default (array_diff() selects everything except those listed)
			$ids = array_diff( array_keys( $this->p->cf['plugin']['wpssorrssb']['lib']['website'] ), array() );
			$this->p->mods['util']['user']->reset_metabox_prefs( $this->pagehook, $ids, 'closed' );
		}

		public function show_metabox_sharing_buttons() {
			$metabox = 'sharing_buttons';
			$tabs = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_tabs', array(
				'include' => _x( 'Include Buttons', 'metabox tab', 'wpsso-rrssb' ),
				'position' => _x( 'Buttons Position', 'metabox tab', 'wpsso-rrssb' ),
			) );
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = array_merge( $this->get_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		public function show_metabox_website() {
			$metabox = 'website';
			$key = $this->website_id;
			$this->p->util->do_table_rows( 
				array_merge( 
					$this->get_rows( $metabox, $key ),
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form )
				),
				'metabox-'.$metabox.'-'.$key
			);
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();
			switch ( $metabox.'-'.$key ) {

				case 'sharing_buttons-include':

					$rows[] = $this->p->util->get_th( _x( 'Include on Index Webpages',
						'option label', 'wpsso-rrssb' ), null, 'buttons_on_index' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_index' ).'</td>';

					$rows[] = $this->p->util->get_th( _x( 'Include on Static Homepage',
						'option label', 'wpsso-rrssb' ), null, 'buttons_on_front' ).
					'<td>'.$this->form->get_checkbox( 'buttons_on_front' ).'</td>';

					$checkboxes = '';

					foreach ( $this->p->util->get_post_types() as $post_type )
						$checkboxes .= '<p>'.$this->form->get_checkbox( 'buttons_add_to_'.$post_type->name ).' '.
							$post_type->label.' '.( empty( $post_type->description ) ? '' :
								'('.$post_type->description.')' ).'</p>';

					$rows[] = $this->p->util->get_th( _x( 'Include on Post Types',
						'option label', 'wpsso-rrssb' ), null, 'buttons_add_to' ).
						'<td>'.$checkboxes.'</td>';

					break;

				case 'sharing_buttons-position':

					$rows[] = $this->p->util->get_th( _x( 'Position in Content Text',
						'option label', 'wpsso-rrssb' ), null, 'buttons_pos_content' ).
					'<td>'.$this->form->get_select( 'buttons_pos_content',
						$this->p->cf['sharing']['position'] ).'</td>';

					$rows[] = $this->p->util->get_th( _x( 'Position in Excerpt Text',
						'option label', 'wpsso-rrssb' ), null, 'buttons_pos_excerpt' ).
					'<td>'.$this->form->get_select( 'buttons_pos_excerpt', 
						$this->p->cf['sharing']['position'] ).'</td>';

					break;
			}
			return $rows;
		}

		// called by each website's settings class to display a list of checkboxes
		// Show Button in: Content, Excerpt, Admin Edit, etc.
		protected function show_on_checkboxes( $prefix ) {
			$col = 0;
			$max = 6;
			$html = '<table>';
			$show_on = apply_filters( $this->p->cf['lca'].'_sharing_show_on', 
				$this->p->cf['sharing']['show_on'], $prefix );
			foreach ( $show_on as $suffix => $desc ) {
				$col++;
				$class = isset( $this->p->options[$prefix.'_on_'.$suffix.':is'] ) &&
					$this->p->options[$prefix.'_on_'.$suffix.':is'] === 'disabled' &&
					! $this->p->check->aop( 'wpssorrssb' ) ? 'show_on blank' : 'show_on';
				if ( $col == 1 )
					$html .= '<tr><td class="'.$class.'">';
				else $html .= '<td class="'.$class.'">';
				$html .= $this->form->get_checkbox( $prefix.'_on_'.$suffix ).
					_x( $desc, 'option value', 'wpsso-rrssb' ).'&nbsp; ';
				if ( $col == $max ) {
					$html .= '</td></tr>';
					$col = 0;
				} else $html .= '</td>';
			}
			$html .= $col < $max ? '</tr>' : '';
			$html .= '</table>';
			return $html;
		}
	}
}

?>
