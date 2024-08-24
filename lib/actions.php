<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbActions' ) ) {

	class WpssoRrssbActions {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/*
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_actions( $this, array(
				'pre_apply_filters_text'   => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_actions( $this, array(
					'load_settings_page_reload_default_rrssb_buttons' => 4,
					'load_settings_page_reload_default_rrssb_styles'  => 4,
				) );
			}
		}

		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array(
					'filter_name' => $filter_name,
				) );
			}

			/*
			 * If a buttons filter is removed, then re-add it when the text filter is finished executing.
			 */
			if ( $this->a->social->remove_buttons_filter( $filter_name ) ) {

				$this->p->util->add_plugin_actions( $this, array(
					'after_apply_filters_text' => 1,
				) );
			}
		}

		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array(
					'filter_name' => $filter_name,
				) );
			}

			$this->a->social->add_buttons_filter( $filter_name );
		}

		public function action_load_settings_page_reload_default_rrssb_buttons( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			foreach ( $this->a->social->get_share_objets() as $id => $share_obj ) {

				if ( method_exists( $share_obj, 'filter_get_defaults' ) ) {

					$this->p->options = $share_obj->filter_get_defaults( $this->p->options );
				}
			}

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default responsive button options have been reloaded and saved.', 'wpsso-rrssb' ) );
		}

		public function action_load_settings_page_reload_default_rrssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$defs = $this->p->opt->get_defaults();

			$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

			foreach ( $styles as $id => $name ) {

				if ( isset( $this->p->options[ 'buttons_css_' . $id ] ) && isset( $defs[ 'buttons_css_' . $id ] ) ) {

					$this->p->options[ 'buttons_css_' . $id ] = $defs[ 'buttons_css_' . $id ];
				}
			}

			WpssoRrssbSocial::update_sharing_css( $this->p->options );

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default responsive styles CSS has been reloaded and saved.', 'wpsso-rrssb' ) );
		}
	}
}
