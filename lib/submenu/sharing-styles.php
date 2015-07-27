<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuSharingStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->p->util->add_plugin_filters( $this, array( 
				'messages_info' => 2,		// info messages filter
				'messages' => 2,		// default messages filter
			) );
		}

		public function filter_messages_info( $text, $idx ) {
			$lca =  $this->p->cf['lca'];
			$short = $this->p->cf['plugin'][$lca]['short'];
			$short_pro = $short.' Pro';
			switch ( $idx ) {

				case 'info-style-sharing':

					$notes_url = $this->p->cf['plugin'][$lca]['url']['notes'];
					$text = '<p>'.$short.' uses the \''.$lca.'-buttons\' class to wrap all its 
					sharing buttons, and each button has it\'s own individual class name as well. 
					Refer to the <a href="'.$notes_url.'" target="_blank">Notes</a> 
					webpage for additional stylesheet information, including how to hide the sharing 
					buttons for specific Posts, Pages, categories, tags, etc.</p>';
					break;

				case 'info-style-content':

					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.
					$this->p->util->get_admin_url( 'sharing', 'Buttons settings page' ).
					', are assigned the \''.$lca.'-content-buttons\' class, which itself contains the 
					\''.$lca.'-buttons\' class -- a common class for all the sharing buttons 
					(see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.'.$lca.'-rrssb .'.$lca.'-rrssb-content
    ul.rrssb-buttons
        li.rrssb-facebook { }</pre>';
					break;

				case 'info-style-excerpt':
					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.
					$this->p->util->get_admin_url( 'sharing', 'Buttons settings page' ).
					', are assigned the \''.$lca.'-excerpt-buttons\' class, which itself contains the 
					\''.$lca.'-buttons\' class -- a common class for all the sharing buttons 
					(see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.'.$lca.'-excerpt-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;

				case 'info-style-sidebar':
					$text = '<p>Social sharing buttons added to the sidebar are assigned the 
					\'#'.$lca.'-sidebar\' CSS id, which itself contains \'#'.$lca.'-sidebar-header\',
					\'#'.$lca.'-sidebar-buttons\', and the \''.$lca.'-buttons\' class -- 
					a common class for all the sharing buttons (see the All Buttons tab).</p> 
					<p>Example:</p><pre>
#'.$lca.'-sidebar
    #'.$lca.'-sidebar-header { }

#'.$lca.'-sidebar
    #'.$lca.'-sidebar-buttons
        .'.$lca.'-buttons
	    .facebook-button { }</pre>';
					break;

				case 'info-style-shortcode':
					$text = '<p>Social sharing buttons added from a shortcode are assigned the 
					\''.$lca.'-shortcode-buttons\' class, which itself contains the 
					\''.$lca.'-buttons\' class -- a common class for all the sharing buttons 
					(see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.'.$lca.'-shortcode-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;

				case 'info-style-widget':
					$text = '<p>Social sharing buttons within the '.$this->p->cf['menu'].
					' Sharing Buttons widget are assigned the 
					\''.$lca.'-widget-buttons\' class, which itself contains the 
					\''.$lca.'-buttons\' class -- a common class for all the sharing buttons 
					(see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.'.$lca.'-widget-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>
					<p>The '.$this->p->cf['menu'].' Sharing Buttons widget also has an id of 
					\''.$lca.'-widget-buttons-<em>#</em>\', and the buttons have an id of 
					\'<em>name</em>-'.$lca.'-widget-buttons-<em>#</em>\'.</p>
					<p>Example:</p><pre>
#'.$lca.'-widget-buttons-2
    .'.$lca.'-buttons
        #facebook-'.$lca.'-widget-buttons-2 { }</pre>';
					break;

				case 'info-style-admin_edit':
					$text = '<p>Social sharing buttons within the Admin Post / Page Edit metabox
					are assigned the \''.$lca.'-admin_edit-buttons\' class, which itself contains the 
					\''.$lca.'-buttons\' class -- a common class for all the sharing buttons 
					(see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.'.$lca.'-admin_edit-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;
			}
			return $text;
		}

		public function filter_messages( $text, $idx ) {
			switch ( $idx ) {
				/*
				 * 'Social Style' settings
				 */
				case ( strpos( $idx, 'tooltip-buttons_' ) !== false ? true : false ):
					switch ( $idx ) {
						case 'tooltip-buttons_use_social_css':
							$text = 'Add the CSS from all style tabs to webpages (default is checked).
							The CSS will be <strong>minimized</strong>, and saved to a single 
							stylesheet with the URL of <a href="'.WpssoRrssbSharing::$sharing_css_url.'">'.
							WpssoRrssbSharing::$sharing_css_url.'</a>. The minimized stylesheet can be 
							enqueued by WordPress, or included directly in the webpage header.';
							break;
		
						case 'tooltip-buttons_js_sidebar':
							$text = 'JavaScript that is added to the webpage for the social sharing sidebar.';
							break;
		
						case 'tooltip-buttons_enqueue_social_css':
							$text = 'Have WordPress enqueue the social stylesheet instead of including the 
							CSS directly in the webpage header (default is unchecked). Enqueueing the stylesheet
							may be desirable if you use a plugin to concatenate all enqueued styles
							into a single stylesheet URL.';
							break;
					}
					break;
			}
			return $text;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_sharing_styles', 'Social Sharing Styles', 
				array( &$this, 'show_metabox_sharing_styles' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_sharing_styles() {
			$metabox = 'sharing-styles';

			if ( file_exists( WpssoRrssbSharing::$sharing_css_file ) &&
				( $fsize = filesize( WpssoRrssbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' css is '.$fsize.' bytes minimized';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->p->util->get_th( 'Use the Social Stylesheet', 'highlight', 'buttons_use_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',

				$this->p->util->get_th( 'Enqueue the Stylesheet', null, 'buttons_enqueue_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
			) );

			$tabs = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_tabs', WpssoRrssbSharing::$cf['sharing']['style'] );
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = array_merge( $this->get_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {

			$rows[] = '<th class="textinfo">'.$this->p->msgs->get( 'info-style-'.$key ).'</th>'.
			'<td>'.$this->form->get_textarea( 'buttons_css_'.$key, 'tall code' ).'</td>';

			return $rows;
		}
	}
}

?>
