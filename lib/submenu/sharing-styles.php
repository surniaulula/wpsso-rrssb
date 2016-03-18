<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuSharingStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array( 
				'messages_tooltip' => 2,	// tooltip messages filter
				'messages_info' => 2,		// info messages filter
			) );
		}

		public function filter_messages_tooltip( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 )
				return $text;

			switch ( $idx ) {
				case 'tooltip-buttons_use_social_css':
					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minimized</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minimized stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-rrssb' ), WpssoRrssbSharing::$sharing_css_url, WpssoRrssbSharing::$sharing_css_url );
					break;

				case 'tooltip-buttons_enqueue_social_css':
					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );
					break;
			}
			return $text;
		}

		public function filter_messages_info( $text, $idx ) {
			if ( strpos( $idx, 'info-style-rrssb-' ) !== 0 )
				return $text;
			$short = $this->p->cf['plugin']['wpsso']['short'];
			switch ( $idx ) {

				case 'info-style-rrssb-sharing':
					$text = '<p>'.$short.' uses the \'wpsso-rrssb\' class to wrap all sharing buttons, and each button has it\'s own individual class name as well. This tab can be used to edit the CSS common to all sharing button locations.</p>';
					break;

				case 'info-style-rrssb-content':
					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-content\' class.</p>'.
					$this->get_css_example( 'content', true );
					break;

				case 'info-style-rrssb-excerpt':
					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-excerpt\' class.</p>'.
					$this->get_css_example( 'excerpt', true );
					break;

				case 'info-style-rrssb-sidebar':
					$text = '<p>Social sharing buttons, enabled / added to the CSS sidebar from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-sidebar\' ID.</p> 
					<p>In order to achieve a vertical display, each un-ordered list (UL) contains a single list item (LI).</p>
					<p>Example:</p><pre>
div.wpsso-rrssb 
  #wpsso-rrssb-sidebar
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-shortcode':
					$text = '<p>Social sharing buttons added from a shortcode are assigned the \'wpsso-rrssb-shortcode\' class by default.</p>'.
					$this->get_css_example( 'admin_edit', true );
					break;

				case 'info-style-rrssb-widget':
					$text = '<p>Social sharing buttons enabled in the '.$short.' widget are assigned the \'wpsso-rrssb-widget\' class (along with additional unique CSS ID names).</p> 
					<p>Example:</p><pre>
aside.widget 
  .wpsso-rrssb-widget 
    ul.rrssb-buttons
        li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-admin_edit':
					$text = '<p>Social sharing buttons, enabled / added to the admin editing pages from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-admin_edit\' class.</p>'.
					$this->get_css_example( 'admin_edit', true );
					break;

				case 'info-style-rrssb-woo_short': 
					$text = '<p>Social sharing buttons, enabled / added to the WooCommerce Short Description text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-woo_short\' class.</p>'.
					$this->get_css_example( 'woo_short' );
      					break;

				case 'info-style-rrssb-bbp_single': 
					$text = '<p>Social sharing buttons, enabled / added at the top of bbPress Single Templates from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-bbp_single\' class.</p>'.
					$this->get_css_example( 'bbp_single' );
      					break;

				case 'info-style-rrssb-bp_activity': 
					$text = '<p>Social sharing buttons, enabled / added to BuddyPress Activities from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-bp_activity\' class.</p>'.
					$this->get_css_example( 'bp_activity' );
      					break;
			}
			return $text;
		}

		protected function get_css_example( $type ) {
			$text = '<p>Example:</p><pre>
div.wpsso-rrssb
  .wpsso-rrssb-'.$type.'
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
			return $text;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_sharing_styles',
				_x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' ),
					array( &$this, 'show_metabox_sharing_styles' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_sharing_styles() {
			$metabox = 'sharing-styles';

			if ( file_exists( WpssoRrssbSharing::$sharing_css_file ) &&
				( $fsize = filesize( WpssoRrssbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' <a href="'.WpssoRrssbSharing::$sharing_css_url.'">minimized css is '.$fsize.' bytes</a>';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->form->get_th_html( _x( 'Use the Social Stylesheet',
					'option label', 'wpsso-rrssb' ), 'highlight', 'buttons_use_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',

				$this->form->get_th_html( _x( 'Enqueue the Stylesheet',
					'option label', 'wpsso-rrssb' ), null, 'buttons_enqueue_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
			) );

			$table_rows = array();
			$tabs = apply_filters( $this->p->cf['lca'].'_sharing_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-style'] );

			foreach ( $tabs as $key => $title ) {
				$tabs[$key] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// translate the tab title
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			}
			$this->p->util->do_metabox_tabs( $metabox, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows['buttons_css_'.$key] = '<th class="textinfo">'.$this->p->msgs->get( 'info-style-'.$key ).'</th>'.
			'<td'.( isset( $this->p->options['buttons_css_'.$key.':is'] ) &&
				$this->p->options['buttons_css_'.$key.':is'] === 'disabled' ? ' class="blank"' : '' ).'>'.
			$this->form->get_textarea( 'buttons_css_'.$key, 'tall code' ).'</td>';
			return $table_rows;
		}
	}
}

?>
