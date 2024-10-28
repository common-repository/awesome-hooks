<?php
/**
 * Show Hooks Admin
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks_Admin' ) ) :

	/**
	 * Show Hooks Admin
	 */
	class Awesome_Hooks_Admin {

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
			add_action( 'wp_enqueue_scripts', array( $this, 'hooks_css' ) );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 90 );
			add_action( 'plugin_action_links_' . AWESOME_HOOKS_BASE, array( $this, 'action_links' ) );
			add_action( 'init', array( $this, 'show_hooks' ), 9999 );
		}

		/**
		 * Admin Bar Menu
		 *
		 * @since 1.0.0
		 *
		 * @param  array $wp_admin_bar Admin bar menus.
		 * @return void
		 */
		function admin_bar_menu( $wp_admin_bar = array() ) {

			$href = add_query_arg( 'show-hooks', 'show' );

			if ( is_admin() ) {
				$href = str_replace( '/wp-admin', '', $href );
			}

			$title = __( 'Show Hooks', 'awesome-hooks' );

			if ( isset( $_GET['show-hooks'] ) && 'show' === $_GET['show-hooks'] ) {
				$title = __( 'Hide Hooks', 'show-hooks' );
				$href  = remove_query_arg( 'show-hooks' );
			}

			$wp_admin_bar->add_menu(
				array(
					'title'  => $title,
					'id'     => 'show-hooks-menu',
					'parent' => false,
					'href'   => $href,
				)
			);

		}

		/**
		 * Admin notice
		 *
		 * Show notice if AwesomePress theme is not install and activate.
		 *
		 * @return void
		 */
		function admin_notices() {
			?>
			<div class="notice notice-error">
				<?php /* translators: %1$s admin link */ ?>
				<p><?php printf( __( 'You need to install and activate <a href="%1$s">AwesomePress</a> theme to use the plugin "Awesome Hooks".', 'awesome-hooks' ), esc_url( admin_url( 'theme-install.php?theme=awesomepress' ) ) ); ?></p>
			</div>
			<?php
		}

		/**
		 * Show hooks
		 *
		 * Add `show-hooks` parameter in the URL to show the hooks.
		 *
		 * E.g. https://mysite.org/?show-hooks
		 *
		 * @since 1.0.0
		 * @return null If not Admin (wp-admin) or if not have the URL parameter `show-hooks`.
		 */
		function show_hooks() {
			if ( is_admin() ) {
				return;
			}

			if ( ! isset( $_GET['show-hooks'] ) ) {
				return;
			}

			if ( 'show' !== $_GET['show-hooks'] ) {
				return;
			}

			foreach ( self::get_hooks() as $key => $hook ) {
				add_action(
					$hook, function() {
						$current_hook = current_action();
						$hook_name    = str_replace( '_', ' ', current_action() );
						$hook_name    = str_replace( 'awesomepress', ' ', $hook_name );
						$hook_name    = ucwords( $hook_name );
						?>
					<div class="awesome-hooks hook-<?php echo esc_attr( $hook_name ); ?>">
						<span class="hook-name"><?php echo esc_html( $hook_name ); ?><small style="font-size: 10px;"> (<?php echo esc_html( $current_hook ); ?>)</small></span>
						<span class="add-new-hook"><a href="<?php echo esc_attr( admin_url( 'post-new.php?post_type=awesome-hook&selected-hook=' . $current_hook ) ); ?>" title="<?php _e( 'Add new Content', 'awesome-hooks' ); ?>"><?php _e( 'Add Content', 'awesome-hooks' ); ?></a></span>
						<?php
					}, 0
				);
				add_action(
					$hook, function() {
						?>
					</div><!-- awesome-hooks hook-<?php echo esc_attr( current_action() ); ?> -->
						<?php
					}, 99999
				);
			}
		}

		/**
		 * Get hooks
		 *
		 * Theme AwesomePress hooks from the file /themes/awesomepress/inc/hooks.php
		 *
		 * @since 1.0.0
		 */
		public static function get_hooks() {

			$default_hooks = apply_filters(
				'awesome_hooks_default_hooks', array(
					'wp_head',
					'loop_start',
					'dynamic_sidebar_before',
					'dynamic_sidebar_after',
					'loop_end',
					'wp_footer',
				)
			);

			return apply_filters( 'awesome_hooks', $default_hooks );
		}

		/**
		 * Show hooks CSS
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function hooks_css() {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_enqueue_style( 'awesome-hooks', AWESOME_HOOKS_URI . 'assets/awesome-hooks.css', array(), AWESOME_HOOKS_VERSION, 'all' );
			} else {
				wp_enqueue_style( 'awesome-hooks', AWESOME_HOOKS_URI . 'assets/awesome-hooks.min.css', array(), AWESOME_HOOKS_VERSION, 'all' );
			}
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . site_url( '?show-hooks=show' ) . '" aria-label="' . esc_attr__( 'Show Hooks', 'awesome-hooks' ) . '">' . esc_html__( 'Show Hooks', 'awesome-hooks' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks_Admin::get_instance();

endif;
