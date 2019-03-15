<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {

	function wpssorrssb_get_sharing_buttons( $share_ids = array(), $atts = array(), $cache_exp_secs = false ) {

		$wpsso =& Wpsso::get_instance();
		$rrssb =& WpssoRrssb::get_instance();

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->mark();
		}

		$error_msg = false;

		if ( ! is_array( $share_ids ) ) {

			$error_msg = 'sharing button ids must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( ! is_array( $atts ) ) {

			$error_msg = 'sharing button attributes must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( ! $wpsso->avail['p_ext']['rrssb'] ) {

			$error_msg = 'sharing buttons are disabled';

		} elseif ( empty( $share_ids ) ) {	// nothing to do

			$error_msg = 'no buttons requested';
		}

		if ( false !== $error_msg ) {

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( 'exiting early: ' . $error_msg );
			}

			return '<!-- ' . __FUNCTION__ . ' exiting early: ' . $error_msg . ' -->' . "\n";
		}

		$atts[ 'use_post' ] = SucomUtil::sanitize_use_post( $atts ); 

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->log( 'required call to get_page_mod()' );
		}

		$mod         = $wpsso->util->get_page_mod( $atts[ 'use_post' ] );
		$type        = __FUNCTION__;
		$sharing_url = $wpsso->util->get_sharing_url( $mod );

		$cache_md5_pre  = $wpsso->lca . '_b_';
		$cache_exp_secs = false === $cache_exp_secs ? $rrssb->social->get_buttons_cache_exp() : $cache_exp_secs;	// Returns 0 for 404 and search pages.
		$cache_salt     = __FUNCTION__ . '(' . SucomUtil::get_mod_salt( $mod, $sharing_url ) . ')';
		$cache_id       = $cache_md5_pre . md5( $cache_salt );
		$cache_index    = $rrssb->social->get_buttons_cache_index( $type, $atts, $share_ids );	// Returns salt with locale, mobile, wp_query, etc.
		$cache_array    = array();

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->log( 'sharing url = ' . $sharing_url );
			$wpsso->debug->log( 'cache expire = ' . $cache_exp_secs );
			$wpsso->debug->log( 'cache salt = ' . $cache_salt );
			$wpsso->debug->log( 'cache id = ' . $cache_id );
			$wpsso->debug->log( 'cache index = ' . $cache_index );
		}

		if ( $cache_exp_secs > 0 ) {

			$cache_array = SucomUtil::get_transient_array( $cache_id );

			if ( isset( $cache_array[ $cache_index ] ) ) {	// can be an empty string

				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( 'exiting early: ' . $type . ' cache index found in transient cache' );
				}

				return $cache_array[ $cache_index ];	// stop here

			} else {

				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( $type . ' cache index not in transient cache' );
				}

				if ( ! is_array( $cache_array ) ) {	// Just in case.
					$cache_array = array();
				}
			}

		} else {

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( $type . ' buttons array transient cache is disabled' );
			}

			if ( SucomUtil::delete_transient_array( $cache_id ) ) {
				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( 'deleted transient cache id ' . $cache_id );
				}
			}
		}

		/**
		 * Returns html or an empty string.
		 */
		$cache_array[ $cache_index ] = $rrssb->social->get_html( $share_ids, $atts, $mod );

		if ( ! empty( $cache_array[ $cache_index ] ) ) {

			$cache_array[ $cache_index ] = '
<!-- ' . $wpsso->lca . ' ' . __FUNCTION__ . ' function begin -->
<!-- generated on ' . date( 'c' ) . ' -->' . "\n" . 
$cache_array[ $cache_index ] . "\n" . 	// buttons html is trimmed, so add newline
'<!-- ' . $wpsso->lca . ' ' . __FUNCTION__ . ' function end -->' . "\n\n";
		}

		if ( $cache_exp_secs > 0 ) {

			/**
			 * Update the cached array and maintain the existing transient expiration time.
			 */
			$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->log( $type . ' buttons html saved to transient cache (expires in ' . $expires_in_secs . ' secs)' );
			}
		}

		return $cache_array[ $cache_index ] . ( $wpsso->debug->enabled ? $wpsso->debug->get_html() : '' );
	}
}
