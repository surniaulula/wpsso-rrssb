<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {
	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array(), $cache_exp_secs = false ) {

		$wpsso =& Wpsso::get_instance();

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->mark();
		}

		$error_msg = false;

		if ( ! is_array( $ids ) ) {
			$error_msg = 'sharing button ids must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! is_array( $atts ) ) {
			$error_msg = 'sharing button attributes must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! $wpsso->avail['p_ext']['rrssb'] ) {
			$error_msg = 'sharing buttons are disabled';
		} elseif ( empty( $ids ) ) {	// nothing to do
			$error_msg = 'no buttons requested';
		}

		if ( $error_msg !== false ) {
			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( 'exiting early: '.$error_msg );
			}
			return '<!-- '.__FUNCTION__.' exiting early: '.$error_msg.' -->' . "\n";
		}

		$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->log( 'required call to get_page_mod()' );
		}
		$mod = $wpsso->util->get_page_mod( $atts['use_post'] );

		$lca = $wpsso->cf['lca'];
		$type = __FUNCTION__;
		$sharing_url = $wpsso->util->get_sharing_url( $mod );
		$buttons_array = array();

		$cache_md5_pre = $lca.'_b_';
		$cache_exp_secs = false === $cache_exp_secs ? $wpsso->rrssb_sharing->get_buttons_cache_exp() : $cache_exp_secs;
		$cache_index = 0;	// redefined if $cache_exp_secs > 0

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->log( 'sharing url = '.$sharing_url );
			$wpsso->debug->log( 'cache expire = '.$cache_exp_secs );
		}

		if ( $cache_exp_secs > 0 ) {

			$cache_salt = __FUNCTION__.'('.SucomUtil::get_mod_salt( $mod, $sharing_url ).')';
			$cache_id = $cache_md5_pre.md5( $cache_salt );
			$cache_index = $wpsso->rrssb_sharing->get_buttons_cache_index( $type, $atts, $ids );

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( 'cache salt = '.$cache_salt );
				$wpsso->debug->log( 'cache index = '.$cache_index );
			}

			$buttons_array = get_transient( $cache_id );

			if ( isset( $buttons_array[$cache_index] ) ) {
				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( $type.' cache index found in array from transient '.$cache_id );
				}
			} else {
				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( $type.' cache index not in array from transient '.$cache_id );
				}
				if ( ! is_array( $buttons_array ) ) {	// just in case
					$buttons_array = array();
				}
			}
		} else {
			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( $type.' buttons array transient cache is disabled' );
			}
		}

		if ( ! isset( $buttons_array[$cache_index] ) ) {

			// returns html or an empty string
			$buttons_array[$cache_index] = $wpsso->rrssb_sharing->get_html( $ids, $atts, $mod );

			if ( ! empty( $buttons_array[$cache_index] ) ) {
				$buttons_array[$cache_index] = '
<!-- '.$lca.' '.__FUNCTION__.' function begin -->
<!-- generated on '.date( 'c' ).' -->' . "\n" . 
$buttons_array[$cache_index] . "\n" . 	// buttons html is trimmed, so add newline
'<!-- '.$lca.' '.__FUNCTION__.' function end -->' . "\n\n";

				if ( $cache_exp_secs > 0 ) {
					// update the cached array and maintain the existing transient expiration time
					$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $buttons_array, $cache_exp_secs );
					if ( $wpsso->debug->enabled ) {
						$wpsso->debug->log( $type.' buttons html saved to transient cache (expires in '.$expires_in_secs.' secs)' );
					}
				}
			}
		}

		return $buttons_array[$cache_index];
	}
}

