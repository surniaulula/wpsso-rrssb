<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {

	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array(), $cache_exp = false ) {

		$wpsso =& Wpsso::get_instance();
		if ( $wpsso->debug->enabled )
			$wpsso->debug->mark();

		$error_msg = false;
		if ( ! is_array( $ids ) ) {
			$error_msg = 'sharing button ids must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! is_array( $atts ) ) {
			$error_msg = 'sharing button attributes must be an array';
			error_log( __FUNCTION__.'() error: '.$error_msg );
		} elseif ( ! $wpsso->is_avail['rrssb'] ) {
			$error_msg = 'sharing buttons are disabled';
		} elseif ( empty( $ids ) ) {	// nothing to do
			$error_msg = 'no buttons requested';
		}

		if ( $error_msg !== false ) {
			if ( $wpsso->debug->enabled )
				$wpsso->debug->log( 'exiting early: '.$error_msg );
			return '<!-- '.__FUNCTION__.' exiting early: '.$error_msg.' -->'."\n".
				( $wpsso->debug->enabled ? $wpsso->debug->get_html() : '' );
		}

		$lca = $wpsso->cf['lca'];
		$type = __FUNCTION__;
		$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 
		$mod = $wpsso->util->get_page_mod( $atts['use_post'] );
		$sharing_url = $wpsso->util->get_sharing_url( $mod );
		$buttons_array = array();
		$buttons_index = $wpsso->rrssb_sharing->get_buttons_cache_index( $type, $atts, $ids );
		$cache_salt = __FUNCTION__.'('.SucomUtil::get_mod_salt( $mod, $sharing_url ).')';
		$cache_id = $lca.'_'.md5( $cache_salt );
		$cache_exp = (int) apply_filters( $lca.'_cache_expire_sharing_buttons', 
			( $cache_exp === false ? $wpsso->options['plugin_sharing_buttons_cache_exp'] : $cache_exp ) );

		if ( $wpsso->debug->enabled ) {
			$wpsso->debug->log( 'sharing url = '.$sharing_url );
			$wpsso->debug->log( 'buttons index = '.$buttons_index );
			$wpsso->debug->log( 'transient expire = '.$cache_exp );
			$wpsso->debug->log( 'transient salt = '.$cache_salt );
		}

		if ( $cache_exp > 0 ) {
			$buttons_array = get_transient( $cache_id );
			if ( isset( $buttons_array[$buttons_index] ) ) {
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( $type.' buttons index found in array from transient '.$cache_id );
			} elseif ( $wpsso->debug->enabled )
				$wpsso->debug->log( $type.' buttons index not in array from transient '.$cache_id );
		} elseif ( $wpsso->debug->enabled )
			$wpsso->debug->log( $type.' buttons array transient is disabled' );

		if ( ! isset( $buttons_array[$buttons_index] ) ) {

			// returns html or an empty string
			$buttons_array[$buttons_index] = $wpsso->rrssb_sharing->get_html( $ids, $atts, $mod );

			if ( ! empty( $buttons_array[$buttons_index] ) ) {
				$buttons_array[$buttons_index] = '
<!-- '.$lca.' '.__FUNCTION__.' function begin -->
<!-- generated on '.date( 'c' ).' -->'."\n".
$buttons_array[$buttons_index]."\n".	// buttons html is trimmed, so add newline
'<!-- '.$lca.' '.__FUNCTION__.' function end -->'."\n\n";

				if ( $cache_exp > 0 ) {
					// update the transient array and keep the original expiration time
					$cache_exp = SucomUtil::update_transient_array( $cache_id, $buttons_array, $cache_exp );
					if ( $wpsso->debug->enabled )
						$wpsso->debug->log( $type.' buttons html saved to transient '.
							$cache_id.' ('.$cache_exp.' seconds)' );
				}
			}
		}

		return $buttons_array[$buttons_index].
			( $wpsso->debug->enabled ? $wpsso->debug->get_html() : '' );
	}
}

?>
