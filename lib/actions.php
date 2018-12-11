<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbActions' ) ) {

	class WpssoRrssbActions {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_actions( $this, array( 
				'pre_apply_filters_text'   => 1,
				'after_apply_filters_text' => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_actions( $this, array(
					'load_setting_page_reload_default_rrssb_buttons' => 4,
					'load_setting_page_reload_default_rrssb_styles'       => 4,
				) );
			}
		}

		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$rrssb =& WpssoRrssb::get_instance();

			$rrssb->social->remove_buttons_filter( $filter_name );
		}

		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$rrssb =& WpssoRrssb::get_instance();

			$rrssb->social->add_buttons_filter( $filter_name );
		}

		public function action_load_setting_page_reload_default_rrssb_buttons( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$opts =& $this->p->options;

			$def_opts = $this->p->opt->get_defaults();

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {
				if ( isset( $this->p->options[ $opt_pre . '_rrssb_html' ] ) && isset( $def_opts[ $opt_pre . '_rrssb_html' ] ) ) {
					$this->p->options[ $opt_pre . '_rrssb_html' ] = $def_opts[ $opt_pre . '_rrssb_html' ];
				}
			}

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default responsive buttons HTML has been reloaded and saved.', 'wpsso-rrssb' ) );
		}

		public function action_load_setting_page_reload_default_rrssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$def_opts = $this->p->opt->get_defaults();

			$styles = apply_filters( $this->p->lca . '_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

			foreach ( $styles as $id => $name ) {
				if ( isset( $this->p->options[ 'buttons_css_' . $id ] ) && isset( $def_opts[ 'buttons_css_' . $id ] ) ) {
					$this->p->options[ 'buttons_css_' . $id ] = $def_opts[ 'buttons_css_' . $id ];
				}
			}

			WpssoRrssbSocial::update_sharing_css( $this->p->options );

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default responsive styles CSS has been reloaded and saved.', 'wpsso-rrssb' ) );
		}
	}
}
