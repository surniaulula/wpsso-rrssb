<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
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
			$short = $this->p->cf['plugin']['wpssorrssb']['short'];
			$name = $this->p->cf['plugin']['wpssorrssb']['name'];
			$widget_name = $short;
			$widget_class = $lca.'-rrssb-widget';
			$widget_ops = array( 
				'classname' => $widget_class,
				'description' => 'The '.$name.' widget.'
			);
			$this->WP_Widget( $widget_class, $widget_name, $widget_ops );
		}
	
		public function widget( $args, $instance ) {
			if ( is_feed() )
				return;	// nothing to do in the feeds

			if ( ! empty( $_SERVER['WPSSORRSSB_DISABLE'] ) )
				return;

			if ( ! is_object( $this->p ) )
				return;

			if ( is_object( $this->p->rrssb ) && 
				$this->p->rrssb->is_post_buttons_disabled() ) {
				$this->p->debug->log( 'widget buttons skipped: sharing buttons disabled' );
				return;
			}
			extract( $args );

			$lca = $this->p->cf['lca'];
			if ( $this->p->is_avail['cache']['transient'] ) {
				$sharing_url = $this->p->util->get_sharing_url();
				$cache_salt = __METHOD__.'(lang:'.SucomUtil::get_locale().'_widget:'.$this->id.'_url:'.$sharing_url.')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				$cache_type = 'object cache';
				$this->p->debug->log( $cache_type.': transient salt '.$cache_salt );
				$html = get_transient( $cache_id );
				if ( $html !== false ) {
					$this->p->debug->log( $cache_type.': html retrieved from transient '.$cache_id );
					echo $html;
					$this->p->debug->show_html();
					return;
				}
			}

			// sort enabled sharing buttons by their preferred order
			$sorted_ids = array();
			foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
				if ( array_key_exists( $id, $instance ) && (int) $instance[$id] )
					$sorted_ids[ zeroise( $this->p->options[$pre.'_order'], 3 ).'-'.$id] = $id;
			ksort( $sorted_ids );

			$atts = array( 
				'css_id' => $args['widget_id'],
				'filter_id' => 'widget',	// used by get_html() to filter atts and opts
				'use_post' => false,		// don't use the post ID on indexes
			);
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			$html = '<!-- '.$lca.' '.$args['widget_id']." begin -->\n".
				$before_widget.
				( empty( $title ) ? '' : $before_title.$title.$after_title."\n" ).
				$this->p->rrssb->get_html( $sorted_ids, $atts ).
				$after_widget.
				'<!-- '.$lca.' '.$args['widget_id']." end -->\n";

			if ( $this->p->is_avail['cache']['transient'] ) {
				set_transient( $cache_id, $html, $this->p->options['plugin_object_cache_exp'] );
				$this->p->debug->log( $cache_type.': html saved to transient '.
					$cache_id.' ('.$this->p->options['plugin_object_cache_exp'].' seconds)');
			}
			echo $html;
			$this->p->debug->show_html();
		}
	
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			foreach ( $this->p->rrssb->get_defined_website_names() as $id => $name )
				$instance[$id] = empty( $new_instance[$id] ) ? 0 : 1;
			return $instance;
		}
	
		public function form( $instance ) {
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Share It';
			echo "\n", '<p><label for="', $this->get_field_id( 'title' ), '">Title (Leave Blank for No Title):</label>',
				'<input class="widefat" id="', $this->get_field_id( 'title' ), 
					'" name="', $this->get_field_name( 'title' ), 
					'" type="text" value="', $title, '" /></p>', "\n";
	
			foreach ( $this->p->rrssb->get_defined_website_names() as $id => $name ) {
				$name = $name == 'GooglePlus' ? 'Google+' : $name;
				echo '<p><label for="'.$this->get_field_id( $id ).'">'.
					'<input id="'.$this->get_field_id( $id ).
					'" name="'.$this->get_field_name( $id ).
					'" value="1" type="checkbox" ';
				if ( ! empty( $instance[$id] ) )
					echo checked( 1, $instance[$id] );
				echo ' /> '.$name.'</label></p>'."\n";
			}
		}

	}
}

?>
