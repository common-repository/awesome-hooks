<?php
/**
 * Show Hooks Post
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if ( ! class_exists( 'Awesome_Hooks_Post' ) ) :

	/**
	 * Show Hooks Post
	 */
	class Awesome_Hooks_Post {

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
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'edit_form_after_title', array( $this, 'after_title' ), 1, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'save_post', array( $this, 'save_post' ) );
			add_action( 'wp', array( $this, 'trigger_hook' ) );
		}

		/**
		 * Trigger Hook
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function trigger_hook() {
			$query_args = array(
				'post_type'     => 'awesome-hook',

				// Query performance optimization.
				'fields'        => 'ids',
				'no_found_rows' => true,
				'post_status'   => 'publish',
			);

			$meta_query = new WP_Query( $query_args );

			if ( $meta_query->posts ) {
				foreach ( $meta_query->posts as $key => $post_id ) {
					$action = get_post_meta( $post_id, 'current-hook', true );
					add_action(
						$action, function() use ( $post_id ) {
							$php_editor = get_post_meta( $post_id, 'default-editor', true );

							if ( $php_editor ) {
								$php_content = get_post_meta( $post_id, 'awesome-hooks-php-editor', true );
								if ( ! empty( $php_content ) ) {
									ob_start();
									// @codingStandardsIgnoreStart
									eval( '?>' . $php_content . '<?php ' );
									// @codingStandardsIgnoreEnd
									echo ob_get_clean();
								}
							} else {
								$content_post = get_post( $post_id );
								$content      = $content_post->post_content;
								$content      = apply_filters( 'the_content', $content );
								$content      = str_replace( ']]>', ']]&gt;', $content );
								echo $content;
							}

						}
					);
				}
			}
		}

		/**
		 * Save Post
		 *
		 * @param  int $post_id Post ID.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function save_post( $post_id ) {
			$data = isset( $_POST['awesome-hooks-php-editor'] ) ? $_POST['awesome-hooks-php-editor'] : get_post_meta( $post_id, 'awesome-hooks-php-editor', true );
			update_post_meta( $post_id, 'awesome-hooks-php-editor', $data );

			$data = isset( $_POST['current-hook'] ) ? $_POST['current-hook'] : get_post_meta( $post_id, 'current-hook', true );
			update_post_meta( $post_id, 'current-hook', $data );

			$default_editor = isset( $_POST['default-editor'] ) ? $_POST['default-editor'] : false;
			update_post_meta( $post_id, 'default-editor', $default_editor );
		}

		/**
		 * Admin Scripts
		 *
		 * @param  string $hook Current Hook.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function admin_scripts( $hook ) {
			global $pagenow;
			global $post;

			$screen = get_current_screen();

			if ( ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) && 'awesome-hook' == $screen->post_type ) {

				if ( ! function_exists( 'wp_enqueue_code_editor' ) ) {
					return;
				}

				$settings = wp_enqueue_code_editor(
					array(
						'type'       => 'application/x-httpd-php',
						'codemirror' => array(
							'indentUnit' => 2,
							'tabSize'    => 2,
						),
					)
				);

				wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery( function() { wp.codeEditor.initialize( "awesome-hooks-php-editor", %s ); } );',
						wp_json_encode( $settings )
					)
				);
			}
		}

		/**
		 * Add metaboxes
		 *
		 * @param  object $post Post object.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function after_title( $post ) {

			if ( 'awesome-hook' !== $post->post_type ) {
				return;
			}

			$default_editor = get_post_meta( get_the_ID(), 'default-editor', true );
			$content        = get_post_meta( get_the_ID(), 'awesome-hooks-php-editor', true );
			$content        = ( ! empty( $content ) ) ? $content : "<?php\n	// Content goes here.\n?>";
			$selected       = get_post_meta( get_the_ID(), 'current-hook', true );

			if ( empty( $selected ) ) {
				$selected = isset( $_GET['selected-hook'] ) ? $_GET['selected-hook'] : '';
			}
			?>

			<div id="postHook" class="postbox  hide-if-js" style="display: block;margin-top: 1em;">
				<div class="inside">
					<table>
						<tbody>
							<tr>
								<td>
									<strong><?php _e( 'Select Hook', 'awesome-hooks' ); ?></strong>
								</td>
								<td>
									<select name="current-hook" id="current-hook">
										<option value=""><?php _e( 'Select', 'awesome-hooks' ); ?></option>
										<?php
										$hooks = Awesome_Hooks_Admin::get_instance()->get_hooks();
										foreach ( $hooks as $key => $hook ) {
											$hook_name = apply_filters( 'awesome_hooks_hook_name_before', $hook );
											$hook_name = str_replace( '_', ' ', $hook_name );
											$hook_name = ucwords( $hook_name );
											$hook_name = apply_filters( 'awesome_hooks_hook_name_after', $hook_name );
											?>
											<option value="<?php echo esc_attr( $hook ); ?>" <?php selected( $selected, $hook ); ?>><?php echo esc_html( $hook_name ); ?></option>
										<?php } ?>
									</select>

								</td>
							</tr>
						</tbody>
					</table>				
					<input type="checkbox" id="default-editor" name="default-editor" value="1" <?php checked( $default_editor, 1 ); ?> style="display: none;" />
				</div>
			</div>

			<button type="button" class="awesome-hooks-switch-editor button button-primary button-hero" style="margin-bottom: 2em;">
				<span class="title"></span>
			</button>

			<div class="wp-editor-container awesome-hooks-php-editor">
				<textarea id="awesome-hooks-php-editor" name="awesome-hooks-php-editor" class="wp-editor-area"><?php echo $content; ?></textarea>
			</div>

			<script type="text/javascript">
				(function($) {
					$(function(){

						$( document ).on( 'click', '.awesome-hooks-switch-editor', toggle_switch_default_editor );

						function toggle_switch_default_editor() {
							var checkBoxes = $( '#default-editor' );
							checkBoxes.prop("checked", ! checkBoxes.prop("checked"));
							switch_default_editor();
						}

						function switch_default_editor() {
							var default_editor = $( '#default-editor' );
							var default_editor_val = $( '#default-editor:checked' ).val() || false;

							if( default_editor_val ) {
								$( 'body' ).addClass('awesome-hooks-default-php-editor').removeClass('awesome-hooks-default-wp-editor');
								$('.awesome-hooks-switch-editor .title').text( 'Switch to Text Editor' );
							} else {
								$( 'body' ).addClass('awesome-hooks-default-wp-editor').removeClass('awesome-hooks-default-php-editor');
								$('.awesome-hooks-switch-editor .title').text( 'Switch to Code Editor' );
							}
						}

						switch_default_editor();
					});

				})(jQuery);
			</script>
			<style type="text/css">
				.awesome-hooks-default-wp-editor .awesome-hooks-php-editor {
					display: none;
				}
				.awesome-hooks-default-php-editor #postdivrich {
					display: none;
				}
			</style>
			<?php
		}

		/**
		 * Registers Awesome Hook post type.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function register_post_type() {

			$labels = array(
				'name'               => __( 'Awesome Hooks', 'awesome-hook' ),
				'singular_name'      => __( 'Awesome Hook', 'awesome-hook' ),
				'add_new'            => _x( 'Add New Hook', 'Awesome Hook', 'awesome-hook' ),
				'add_new_item'       => __( 'Add New Hook', 'awesome-hook' ),
				'edit_item'          => __( 'Edit Hook', 'awesome-hook' ),
				'new_item'           => __( 'New Hook', 'awesome-hook' ),
				'view_item'          => __( 'View Hook', 'awesome-hook' ),
				'search_items'       => __( 'Search Hooks', 'awesome-hook' ),
				'not_found'          => __( 'No Hooks found', 'awesome-hook' ),
				'not_found_in_trash' => __( 'No Hooks found in Trash', 'awesome-hook' ),
				'parent_item_colon'  => __( 'Parent Hook:', 'awesome-hook' ),
				'menu_name'          => __( 'Awesome Hooks', 'awesome-hook' ),
				'all_items'          => __( 'All Hooks', 'awesome-hook' ),
			);

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array(),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => 'dashicons-randomize',
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'has_archive'         => true,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capability_type'     => 'post',
				'supports'            => array(
					'title',
					'editor',
				),
			);

			register_post_type( 'awesome-hook', $args );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Awesome_Hooks_Post::get_instance();

endif;
