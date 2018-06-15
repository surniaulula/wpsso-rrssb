<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbShortcodeSharing' ) ) {

	class WpssoRrssbShortcodeSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! is_admin() ) {
				if ( $this->p->avail['p_ext']['rrssb'] ) {

					$this->check_wpautop();
					$this->add_shortcode();

					$this->p->util->add_plugin_actions( $this, array( 
						'text_filter_before' => 1,
						'text_filter_after' => 1,
					) );
				}
			}
		}

		public function check_wpautop() {
			// make sure wpautop() does not have a higher priority than 10, otherwise it will 
			// format the shortcode output (shortcode filters are run at priority 11).
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				$default_priority = 10;
				foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {
					$filter_priority = has_filter( $filter_name, 'wpautop' );
					if ( $filter_priority !== false && $filter_priority > $default_priority ) {
						remove_filter( $filter_name, 'wpautop' );
						add_filter( $filter_name, 'wpautop' , $default_priority );
						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'wpautop() priority changed from '.$filter_priority.' to '.$default_priority );
						}
					}
				}
			}
		}

		public function action_pre_apply_filters_text( $filter_name ) {
			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}
			$this->remove_shortcode();	// remove before applying a text filter
		}

		public function action_after_apply_filters_text( $filter_name ) {
			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}
			$this->add_shortcode();		// re-add after applying a text filter
		}

		public function add_shortcode() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				if ( ! shortcode_exists( WPSSORRSSB_SHARING_SHORTCODE_NAME ) ) {
        				add_shortcode( WPSSORRSSB_SHARING_SHORTCODE_NAME, array( $this, 'do_shortcode' ) );
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( '['.WPSSORRSSB_SHARING_SHORTCODE_NAME.'] sharing shortcode added' );
					}
					return true;
				} elseif ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'cannot add ['.WPSSORRSSB_SHARING_SHORTCODE_NAME.'] sharing shortcode - shortcode already exists' );
				}
			}
			return false;
		}

		public function remove_shortcode() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				if ( shortcode_exists( WPSSORRSSB_SHARING_SHORTCODE_NAME ) ) {
					remove_shortcode( WPSSORRSSB_SHARING_SHORTCODE_NAME );
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( '['.WPSSORRSSB_SHARING_SHORTCODE_NAME.'] sharing shortcode removed' );
					}
					return true;
				} elseif ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'cannot remove ['.WPSSORRSSB_SHARING_SHORTCODE_NAME.'] sharing shortcode - shortcode does not exist' );
				}
			}
			return false;
		}

		public function do_shortcode( $atts = array(), $content = null, $tag = '' ) { 

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! is_array( $atts ) ) {	// empty string if no shortcode attributes
				$atts = array();
			}

			if ( SucomUtil::is_amp() ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				}
				return $content;
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				}
				return $content;
			}

			$lca = $this->p->cf['lca'];
			$atts = (array) apply_filters( $lca.'_rrssb_sharing_shortcode_atts', $atts, $content );

			if ( empty( $atts['buttons'] ) ) {	// nothing to do
				return '<!-- '.$lca.' sharing shortcode: no buttons defined -->' . "\n\n";
			}

			$atts['use_post'] = SucomUtil::sanitize_use_post( $atts, true );	// $default = true
			$atts['css_class'] = empty( $atts['css_class'] ) ? 'rrssb-shortcode' : $atts['css_class'];

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'required call to get_page_mod()' );
			}
			$mod = $this->p->util->get_page_mod( $atts['use_post'] );

			$type = 'sharing_shortcode_'.WPSSORRSSB_SHARING_SHORTCODE_NAME;
			$atts['url'] = empty( $atts['url'] ) ? $this->p->util->get_sharing_url( $mod ) : $atts['url'];

			$cache_md5_pre  = $lca . '_b_';
			$cache_exp_secs = $this->p->rrssb_sharing->get_buttons_cache_exp();
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $atts['url'] ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $this->p->rrssb_sharing->get_buttons_cache_index( $type, $atts );	// returns salt with locale, mobile, wp_query, etc.
			$cache_array    = array();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = ' . $atts['url'] );
				$this->p->debug->log( 'cache expire = ' . $cache_exp_secs );
				$this->p->debug->log( 'cache salt = ' . $cache_salt );
				$this->p->debug->log( 'cache id = ' . $cache_id );
				$this->p->debug->log( 'cache index = ' . $cache_index );
			}

			if ( $cache_exp_secs > 0 ) {

				$cache_array = get_transient( $cache_id );

				if ( isset( $cache_array[$cache_index] ) ) {	// can be an empty string
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' cache index found in transient cache' );
					}
					return $cache_array[$cache_index];	// stop here
				} else {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' cache index not in transient cache' );
					}
					if ( ! is_array( $cache_array ) ) {
						$cache_array = array();
					}
				}
			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( $type . ' buttons transient cache is disabled' );
			}

			$ids = array_map( 'trim', explode( ',', $atts['buttons'] ) );
			unset ( $atts['buttons'] );

			// returns html or an empty string
			$cache_array[$cache_index] = $this->p->rrssb_sharing->get_html( $ids, $atts, $mod );

			if ( ! empty( $cache_array[$cache_index] ) ) {
				$cache_array[$cache_index] = '
<!-- '.$lca.' '.$type.' begin -->
<!-- generated on '.date( 'c' ).' -->
<div class="'.$lca.'-rrssb '.$lca.'-'.$atts['css_class'].'">' . "\n" . 
$cache_array[$cache_index] . "\n" . 	// buttons html is trimmed, so add newline
'</div><!-- .'.$lca.'-'.$atts['css_class'].' -->' . "\n" . 
'<!-- '.$lca.' '.$type.' end -->' . "\n\n";
			}

			if ( $cache_exp_secs > 0 ) {
				// update the cached array and maintain the existing transient expiration time
				$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( $type.' buttons saved to transient cache (expires in '.$expires_in_secs.' secs)' );
				}
			}

			return $cache_array[$cache_index];
		}
	}
}
