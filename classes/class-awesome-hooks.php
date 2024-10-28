<?php
/**
 * Show Hooks Init
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks' ) ) :

	/**
	 * Show Hooks Init
	 */
	class Awesome_Hooks {

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
			require_once AWESOME_HOOKS_DIR . 'classes/class-awesome-hooks-admin.php';
			require_once AWESOME_HOOKS_DIR . 'classes/class-awesome-hooks-post.php';
			require_once AWESOME_HOOKS_DIR . 'classes/class-awesome-hooks-theme-supports.php';
			require_once AWESOME_HOOKS_DIR . 'classes/class-awesome-hooks-license.php';
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks::get_instance();

endif;
