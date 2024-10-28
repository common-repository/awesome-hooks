<?php
/**
 * Show Hooks Init
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks_Theme_TwentySixteen' ) ) :

	/**
	 * Show Hooks Init
	 */
	class Awesome_Hooks_Theme_TwentySixteen {

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
		}

		/**
		 * Add theme Hooks.
		 *
		 * @param array $hooks Hooks.
		 * @return array Hooks.
		 */
		function add_hooks( $hooks ) {

			$theme_hooks = array(
				'twentysixteen_credits',
			);

			return wp_parse_args( $theme_hooks, $hooks );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks_Theme_TwentySixteen::get_instance();

endif;
