<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {

	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array() ) {

		$wpsso =& Wpsso::get_instance();
		$rrssb =& WpssoRrssb::get_instance();

		if ( $wpsso->debug->enabled ) {

			$wpsso->debug->mark();
		}

		$mtime_start = microtime( $get_float = true );
		$error_msg   = '';

		if ( ! is_array( $ids ) ) {

			$error_msg = 'sharing button ids must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( ! is_array( $atts ) ) {

			$error_msg = 'sharing button attributes must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( ! $wpsso->avail[ 'p_ext' ][ 'rrssb' ] ) {

			$error_msg = 'sharing buttons are disabled';

		} elseif ( empty( $ids ) ) {	// Nothing to do.

			$error_msg = 'no buttons requested';
		}

		if ( ! empty( $error_msg ) ) {

			if ( $wpsso->debug->enabled ) {

				$wpsso->debug->log( 'exiting early: ' . $error_msg );
			}

			return '<!-- ' . __FUNCTION__ . ' exiting early: ' . $error_msg . ' -->' . "\n";
		}

		$atts[ 'use_post' ] = SucomUtil::sanitize_use_post( $atts );

		if ( $wpsso->debug->enabled ) {

			$wpsso->debug->log( 'required call to WpssoPage->get_mod()' );
		}

		$mod = $wpsso->page->get_mod( $atts[ 'use_post' ] );

		$buttons_html = $rrssb->social->get_html( $ids, $atts, $mod );	// Returns html or an empty string.

		if ( $wpsso->debug->enabled ) {

			$buttons_html .= $wpsso->debug->get_html();
		}

		return $buttons_html;
	}
}
