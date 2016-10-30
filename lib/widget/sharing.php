<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbWidgetSharing' ) && class_exists( 'WP_Widget' ) ) {

	class WpssoRrssbWidgetSharing extends WP_Widget {

		protected $p;

		public function __construct() {
			$this->p =& Wpsso::get_instance();
			if ( ! is_object( $this->p ) )
				return;

			$lca = $this->p->cf['lca'];
			$short = $this->p->cf['plugin']['wpssorrssb']['short'];
			$name = $this->p->cf['plugin']['wpssorrssb']['name'];
			$widget_name = $short;
			$widget_class = $lca.'-rrssb-widget';
			$widget_ops = array( 
				'classname' => $widget_class,
				'description' => sprintf( __( 'The %s widget.', 'wpsso-rrssb' ), $name ),
			);

			parent::__construct( $widget_class, $widget_name, $widget_ops );
		}
	
		public function widget( $args, $instance ) {
			if ( ! is_object( $this->p ) )
				return;
			elseif ( ! $this->p->is_avail['rrssb'] )
				return;
			elseif ( is_feed() )
				return;	// nothing to do in the feeds

			extract( $args );

			$atts = array( 
				'use_post' => false,		// don't use the post ID on indexes
				'css_id' => $args['widget_id'],
			);

			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			$lca = $this->p->cf['lca'];
			$type = 'sharing_widget_'.$this->id;
			$buttons_index = $this->p->rrssb_sharing->get_buttons_cache_index( $type, $atts );
			$buttons_array = array();
			$cache_exp = (int) apply_filters( $lca.'_cache_expire_sharing_buttons', 
				$this->p->options['plugin_sharing_buttons_cache_exp'], $buttons_index );

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'buttons index = '.$buttons_index );
				$this->p->debug->log( 'cache expire = '.$cache_exp );
			}

			if ( $cache_exp > 0 ) {
				$cache_salt = __METHOD__.'(locale:'.SucomUtil::get_locale().
					( empty( $mod['id'] ) ? '_url:'.$this->p->util->get_sharing_url( $atts['use_post'] ) : '' ).')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'transient cache salt '.$cache_salt );
				$buttons_array = get_transient( $cache_id );
				if ( isset( $buttons_array[$buttons_index] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' buttons array retrieved from transient '.$cache_id );
				}
			}

			if ( ! isset( $buttons_array[$buttons_index] ) ) {

				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
					if ( array_key_exists( $id, $instance ) && (int) $instance[$id] )
						$sorted_ids[ zeroise( $this->p->options[$pre.'_order'], 3 ).'-'.$id] = $id;
				ksort( $sorted_ids );

				// returns html or an empty string
				$buttons_array[$buttons_index] = $this->p->rrssb_sharing->get_html( $sorted_ids, $atts );

				if ( ! empty( $buttons_array[$buttons_index] ) ) {
					$buttons_array[$buttons_index] = '
<!-- '.$lca.' sharing widget '.$args['widget_id'].' begin -->'."\n".
$before_widget.
( empty( $title ) ? '' : $before_title.$title.$after_title ).
$buttons_array[$buttons_index]."\n".	// buttons html is trimmed, so add newline
$after_widget.
'<!-- '.$lca.' sharing widget '.$args['widget_id'].' end -->'."\n\n";

					if ( $cache_exp > 0 ) {
						set_transient( $cache_id, $buttons_array, $cache_exp );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $type.' buttons html saved to transient '.
								$cache_id.' ('.$cache_exp.' seconds)' );
					}
				}
			}

			echo $buttons_array[$buttons_index];
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html();
		}
	
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			foreach ( $this->p->rrssb_sharing->get_website_object_ids() as $id => $name )
				$instance[$id] = empty( $new_instance[$id] ) ? 0 : 1;
			return $instance;
		}
	
		public function form( $instance ) {
			$title = isset( $instance['title'] ) ?
				esc_attr( $instance['title'] ) : 
				_x( 'Share It', 'option value', 'wpsso-rrssb' );

			echo "\n".'<p><label for="'.$this->get_field_id( 'title' ).'">'.
			_x( 'Widget Title (leave blank for no title)', 'option label', 'wpsso-rrssb' ).':</label>'.
			'<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.
				$this->get_field_name( 'title' ).'" type="text" value="'.$title.'"/></p>'."\n";
	
			foreach ( $this->p->rrssb_sharing->get_website_object_ids() as $id => $name ) {
				$name = $name == 'GooglePlus' ? 'Google+' : $name;
				echo '<p><label for="'.$this->get_field_id( $id ).'">'.
					'<input id="'.$this->get_field_id( $id ).
					'" name="'.$this->get_field_name( $id ).
					'" value="1" type="checkbox" ';
				if ( ! empty( $instance[$id] ) )
					echo checked( 1, $instance[$id] );
				echo '/> '.$name.'</label></p>'."\n";
			}
		}

	}
}

?>
