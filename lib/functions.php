<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {
	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array() ) {
		global $wpsso;
		if ( $wpsso->is_avail['rrssb'] ) {
			$cache_salt = __METHOD__.'(lang:'.SucomUtil::get_locale().
				'_url:'.$wpsso->util->get_sharing_url().
				'_ids:'.( implode( '_', $ids ) ).
				'_atts:'.( implode( '_', $atts ) ).')';
			$cache_id = $wpsso->cf['lca'].'_'.md5( $cache_salt );
			$cache_type = 'object cache';

			if ( $wpsso->is_avail['cache']['transient'] ) {
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( $cache_type.': transient salt '.$cache_salt );
				$html = get_transient( $cache_id );
			} elseif ( $wpsso->is_avail['cache']['object'] ) {
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( $cache_type.': wp_cache salt '.$cache_salt );
				$html = wp_cache_get( $cache_id, __METHOD__ );
			} else $html = false;

			if ( $html !== false ) {
				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( $cache_type.': html retrieved from cache '.$cache_id );
				return $wpsso->debug->get_html().$html;
			}

			$html = '<!-- '.$wpsso->cf['lca'].' sharing buttons begin -->' .
				$wpsso->rrssb->get_html( $ids, $atts ) .
				'<!-- '.$wpsso->cf['lca'].' sharing buttons end -->';
	
			if ( $wpsso->is_avail['cache']['transient'] ||
				$wpsso->is_avail['cache']['object'] ) {

				if ( $wpsso->is_avail['cache']['transient'] )
					set_transient( $cache_id, $html, $wpsso->cache->object_expire );
				elseif ( $wpsso->is_avail['cache']['object'] )
					wp_cache_set( $cache_id, $html, __METHOD__, $wpsso->cache->object_expire );

				if ( $wpsso->debug->enabled )
					$wpsso->debug->log( $cache_type.': html saved to cache '.
						$cache_id.' ('.$wpsso->cache->object_expire.' seconds)');
			}
		} else $html = '<!-- '.$wpsso->cf['lca'].' sharing sharing buttons disabled -->';

		return $wpsso->debug->get_html().$html;
	}
}

?>
