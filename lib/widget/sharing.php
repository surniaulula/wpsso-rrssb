<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbWidgetSharing' ) && class_exists( 'WP_Widget' ) ) {

	class WpssoRrssbWidgetSharing extends WP_Widget {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/*
		 * Registered by Wpsso->register_widgets() and instantiated by WordPress.
		 */
		public function __construct() {

			if ( ! class_exists( 'Wpsso' ) || ! class_exists( 'WpssoRrssb' ) ) {	// Just in case.

				return;
			}

			$this->p =& Wpsso::get_instance();
			$this->a =& WpssoRrssb::get_instance();

			$short        = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'short' ];
			$name         = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'name' ];
			$widget_name  = $short;
			$widget_class = 'wpsso-rrssb-widget';
			$widget_ops   = array(
				'classname'   => $widget_class,
				'description' => sprintf( __( 'The %s widget.', 'wpsso-rrssb' ), $name ),
			);

			parent::__construct( $widget_class, $widget_name, $widget_ops );
		}

		public function widget( $args, $instance ) {

			if ( is_feed() ) {

				return;
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'required call to WpssoPage->get_mod()' );
			}

			$atts = array( 'use_post' => false );	// Don't use the post ID on archive pages.

			$mod = $this->p->page->get_mod( $atts[ 'use_post' ] );

			/*
			 * Sort enabled sharing buttons by their preferred order.
			 */
			$sorted_ids = array();

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				if ( array_key_exists( $id, $instance ) && (int) $instance[ $id ] ) {

					$sorted_ids[ zeroise( $this->p->options[ $opt_pre . '_button_order' ], 3 ) . '-' . $id ] = $id;
				}
			}

			ksort( $sorted_ids );

			$atts[ 'utm_content' ] = 'wpsso-rrssb-widget-' . sanitize_title_with_dashes( $this->id_base );

			$buttons_html = $this->a->social->get_html( $sorted_ids, $mod, $atts );

			if ( ! empty( $buttons_html ) ) {

				$widget_title = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );

				$buttons_html = "\n" . '<!-- wpsso sharing widget ' . $args[ 'widget_id' ] . ' begin -->' . "\n" .
					$before_widget .
					( empty( $widget_title ) ? '' : $before_title . $widget_title . $after_title ) .
					$buttons_html . "\n" . 	// Buttons html is trimmed, so add a newline.
					$after_widget .
					'<!-- wpsso sharing widget ' . $args[ 'widget_id' ] . ' end -->' . "\n\n";
			}

			echo $buttons_html;
		}

		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

			$share_ids = $this->a->social->get_share_ids();

			foreach ( $share_ids as $share_id => $share_title ) {

				$instance[ $share_id ] = empty( $new_instance[ $share_id ] ) ? 0 : 1;
			}

			return $instance;
		}

		public function form( $instance ) {

			$widget_title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : _x( 'Share It', 'option value', 'wpsso-rrssb' );

			echo "\n" . '<p><label for="' . $this->get_field_id( 'title' ) . '">' .
				_x( 'Widget Title (leave blank for no title)', 'option label', 'wpsso-rrssb' ) . ':</label>' .
				'<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' .
					$this->get_field_name( 'title' ) . '" type="text" value="' . $widget_title . '"/></p>' . "\n";

			$share_ids = $this->a->social->get_share_ids();

			foreach ( $share_ids as $share_id => $share_title ) {

				$share_title = $share_title === 'GooglePlus' ? 'Google+' : $share_title;

				echo '<p><label for="' . $this->get_field_id( $share_id ) . '">' .
					'<input id="' . $this->get_field_id( $share_id ) .
					'" name="' . $this->get_field_name( $share_id ) .
					'" value="1" type="checkbox" ';

				if ( ! empty( $instance[ $share_id ] ) ) {

					echo checked( 1, $instance[ $share_id ] );
				}

				echo '/> ' . $share_title . '</label></p>' . "\n";
			}
		}
	}
}
