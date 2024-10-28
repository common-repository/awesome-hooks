<?php
/**
 * Show Hooks Theme Supports
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks_Theme_Supports' ) ) :

	/**
	 * Show Hooks Theme Supports
	 */
	class Awesome_Hooks_Theme_Supports {

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
			add_action( 'init', array( $this, 'theme_support' ) );
		}

		/**
		 * Add support theme Hooks.
		 *
		 * @return void
		 */
		function theme_support() {

			if ( 'awesomepress' === wp_get_theme()->template ) {
				require_once AWESOME_HOOKS_DIR . 'themes/class-awesome-hooks-theme-awesomepress.php';
			}

			if ( 'twentysixteen' === wp_get_theme()->template ) {
				require_once AWESOME_HOOKS_DIR . 'themes/class-awesome-hooks-theme-twentysixteen.php';
			}
		}


	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks_Theme_Supports::get_instance();

endif;
