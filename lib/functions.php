<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {

	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array(), $cache_exp = 86400 ) {

		$wpsso =& Wpsso::get_instance();
		if ( $wpsso->debug->enabled )
			$wpsso->debug->mark();
		$lca = $wpsso->cf['lca'];
		$html = false;

		if ( ! is_array( $ids ) ) {
			error_log( __FUNCTION__.'() error: sharing button ids must be an array' );
			if ( $wpsso->debug->enabled )
				$wpsso->debug->log( 'sharing button ids must be an array' );
		} elseif ( ! is_array( $atts ) ) {
			error_log( __FUNCTION__.'() error: sharing button attributes must be an array' );
			if ( $wpsso->debug->enabled )
				$wpsso->debug->log( 'sharing button attributes must be an array' );
		} elseif ( ! $wpsso->is_avail['rrssb'] ) {
			$html = '<!-- '.$wpsso->cf['lca'].' sharing buttons are disabled -->';
			if ( $wpsso->debug->enabled )
				$wpsso->debug->log( 'sharing buttons are disabled' );
		} else {
			$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 
			$cache_salt = __FUNCTION__.'(locale:'.SucomUtil::get_locale().
				'_url:'.$wpsso->util->get_sharing_url( $atts['use_post'] ).
				'_ids:'.( implode( '_', $ids ) ).
				'_atts:'.( implode( '_', $atts ) ).')';
			$cache_id = $lca.'_'.md5( $cache_salt );

			// clear the cache if cache_exp is 0 (empty)
			if ( empty( $cache_exp ) ) {
				if ( $wpsso->is_avail['cache']['transient'] )
					delete_transient( $cache_id );
				elseif ( $wpsso->is_avail['cache']['object'] )
					wp_cache_delete( $cache_id, __FUNCTION__ );
				return $wpsso->debug->get_html().$html;
			} elseif ( ! isset( $atts['read_cache'] ) || $atts['read_cache'] ) {
				if ( $wpsso->is_avail['cache']['transient'] ) {
					if ( $wpsso->debug->enabled )
						$wpsso->debug->log( 'transient cache salt '.$cache_salt );
					$html = get_transient( $cache_id );
				} elseif ( $wpsso->is_avail['cache']['object'] ) {
					if ( $wpsso->debug->enabled )
						$wpsso->debug->log( 'wp_cache salt '.$cache_salt );
					$html = wp_cache_get( $cache_id, __FUNCTION__ );
				} else $html = false;
			} else $html = false;

			if ( $html !== false ) {
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( 'html retrieved from cache '.$cache_id );
				return $wpsso->debug->get_html().$html;
			}

			$html = '<!-- '.$lca.' '.__FUNCTION__.' function begin -->'."\n".
				$wpsso->rrssb->get_html( $ids, $atts ).
				'<!-- '.$lca.' '.__FUNCTION__.' function end -->';
	
			if ( $wpsso->is_avail['cache']['transient'] ||
				$wpsso->is_avail['cache']['object'] ) {

				if ( $wpsso->is_avail['cache']['transient'] )
					set_transient( $cache_id, $html, $cache_exp );
				elseif ( $wpsso->is_avail['cache']['object'] )
					wp_cache_set( $cache_id, $html, __FUNCTION__, $cache_exp );
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( 'html saved to cache '.$cache_id.' ('.$cache_exp.' seconds)');
			}
		}
		return $wpsso->debug->get_html().$html;
	}
}

?>
