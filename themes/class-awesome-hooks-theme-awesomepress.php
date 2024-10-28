<?php
/**
 * Show Hooks Init
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks_Theme_AwesomePress' ) ) :

	/**
	 * Show Hooks Init
	 */
	class Awesome_Hooks_Theme_AwesomePress {

		/**
		 * Instance
		 *
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'awesome_hooks', array( $this, 'add_hooks' ) );
			add_filter( 'awesome_hooks_hook_name_before', array( $this, 'hook_name' ) );
		}

		/**
		 * Filter Hook name from the Post Editor.
		 *
		 * @param  string $hook_name Hook name.
		 * @return string            Hook name.
		 */
		function hook_name( $hook_name = '' ) {

			$hook_name = str_replace( 'awesomepress', ' ', $hook_name );

			return $hook_name;
		}

		/**
		 * Add theme Hooks.
		 *
		 * @param array $hooks Hooks.
		 * @return array Hooks.
		 */
		function add_hooks( $hooks ) {
			$theme_hooks = array(
				'awesomepress_html_before',
				'awesomepress_head_top',
				'awesomepress_head_bottom',
				'awesomepress_body_top',
				'awesomepress_body_bottom',
				'awesomepress_header_before',
				'awesomepress_header_after',
				'awesomepress_header_top',
				'awesomepress_header_bottom',
				'awesomepress_content_before',
				'awesomepress_content_after',
				'awesomepress_content_top',
				'awesomepress_content_bottom',
				'awesomepress_entry_content_after',
				'awesomepress_entry_content_before',
				'awesomepress_entry_content_top',
				'awesomepress_entry_content_bottom',
				'awesomepress_entry_header_before',
				'awesomepress_entry_header_top',
				'awesomepress_entry_header_bottom',
				'awesomepress_entry_header_after',
				'awesomepress_entry_footer',
				'awesomepress_entry_footer_before',
				'awesomepress_entry_footer_top',
				'awesomepress_entry_footer_bottom',
				'awesomepress_entry_footer_after',
				'awesomepress_content_while_before',
				'awesomepress_content_while_after',
				'awesomepress_footer_before',
				'awesomepress_footer_after',
				'awesomepress_footer_top',
				'awesomepress_footer_bottom',
				'awesomepress_page_header_bottom',
				'awesomepress_page_header_top',
				'awesomepress_page_header_before',
				'awesomepress_page_header_after',
				'awesomepress_pagination_before',
				'awesomepress_pagination_after',
				'awesomepress_comments_template_before',
				'awesomepress_comments_template_after',
			);

			return wp_parse_args( $theme_hooks, $hooks );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks_Theme_AwesomePress::get_instance();

endif;
