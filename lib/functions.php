<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

/*
 * $use_post can be true, false, or a post ID.
 */
if ( ! function_exists( 'wpssorrssb_get_post_sharing_buttons' ) ) {

	function wpssorrssb_get_post_sharing_buttons( $use_post = true, $ids = array(), $atts = array() ) {

		$wpsso =& Wpsso::get_instance();

		if ( $wpsso->debug->enabled ) {

			$wpsso->debug->log( 'use_post is ' . SucomUtil::get_use_post_string( $use_post ) );
		}

		$atts[ 'use_post' ] = $use_post;

		return wpssorrssb_get_sharing_buttons( $ids, $atts );
	}
}

if ( ! function_exists( 'wpssorrssb_get_sharing_buttons' ) ) {

	function wpssorrssb_get_sharing_buttons( $ids = array(), $atts = array() ) {

		$wpsso =& Wpsso::get_instance();

		if ( $wpsso->debug->enabled ) {

			$wpsso->debug->log( 'required call to WpssoPage->get_mod()' );
		}

		$atts[ 'use_post' ] = SucomUtil::sanitize_use_post( $atts, $default = true );

		$mod = $wpsso->page->get_mod( $atts[ 'use_post' ] );

		return wpssorrssb_get_mod_sharing_buttons( $mod, $ids, $atts );
	}
}

if ( ! function_exists( 'wpssorrssb_get_mod_sharing_buttons' ) ) {

	function wpssorrssb_get_mod_sharing_buttons( array $mod, $ids = array(), $atts = array() ) {

		$wpsso =& Wpsso::get_instance();

		$rrssb =& WpssoRrssb::get_instance();

		if ( $wpsso->debug->enabled ) {

			$wpsso->debug->mark();
		}

		$error_msg = false;

		if ( ! is_array( $ids ) ) {

			$error_msg = 'ids must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( ! is_array( $atts ) ) {

			$error_msg = 'attributes must be an array';

			error_log( __FUNCTION__ . '() error: ' . $error_msg );

		} elseif ( empty( $ids ) ) {	// Nothing to do.

			$error_msg = 'no buttons requested';
		}

		if ( $error_msg ) {

			if ( $wpsso->debug->enabled ) {

				$wpsso->debug->log( 'exiting early: ' . $error_msg );
			}

			return '<!-- ' . __FUNCTION__ . ' exiting early: ' . $error_msg . ' -->' . "\n";
		}

		$atts[ 'use_post' ] = false;	// Just in case.

		$buttons_html = $rrssb->social->get_html( $ids, $mod, $atts );

		if ( $wpsso->debug->enabled ) {

			$buttons_html .= $wpsso->debug->get_html();
		}

		return $buttons_html;
	}
}
