<?php
/**
 * License
 *
 * @package Awesome Hooks
 * @since 1.0.0
 */

if( ! class_exists( 'Awesome_Hooks_License' ) ) :

	/**
	 * Awesome_Hooks_License
	 *
	 * @since 1.0.0
	 */
	class Awesome_Hooks_License {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
			// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
			define( 'EDD_SAMPLE_STORE_URL', 'http://store.surror.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

			// the name of your product. This should match the download name in EDD exactly
			define( 'EDD_SAMPLE_ITEM_NAME', 'Awesome Hooks' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

			// the name of the settings page for the license input to be displayed
			define( 'EDD_SAMPLE_PLUGIN_LICENSE_PAGE', 'license' );

			if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater
				include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
			}
		
			add_action( 'admin_init'    , array( $this, 'edd_sl_sample_plugin_updater' ), 0 );
			add_action( 'admin_menu'    , array( $this, 'edd_sample_license_menu' ) );
			add_action( 'admin_init'    , array( $this, 'edd_sample_register_option') );
			add_action( 'admin_init'    , array( $this, 'edd_sample_activate_license') );
			add_action( 'admin_init'    , array( $this, 'edd_sample_deactivate_license') );
			add_action( 'admin_notices' , array( $this, 'edd_sample_admin_notices'  ) );
		}

		function edd_sl_sample_plugin_updater() {

			// retrieve our license key from the DB
			$license_key = trim( get_option( 'edd_sample_license_key' ) );

			// setup the updater
			$edd_updater = new EDD_SL_Plugin_Updater( EDD_SAMPLE_STORE_URL, AWESOME_HOOKS_FILE, array(
				'version' 	=> AWESOME_HOOKS_VERSION, 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => EDD_SAMPLE_ITEM_NAME, 	// name of this plugin
				'author' 	=> 'Surror',  // author of this plugin
				'beta'		=> false
			));

		}

		/************************************
		* the code below is just a standard
		* options page. Substitute with
		* your own.
		*************************************/

		function edd_sample_license_menu() {
			add_submenu_page( 'edit.php?post_type=awesome-hook', 'License', 'License', 'manage_options', EDD_SAMPLE_PLUGIN_LICENSE_PAGE, array( $this, 'edd_sample_license_page' ) );
			// add_plugins_page( 'License', 'License', 'manage_options', EDD_SAMPLE_PLUGIN_LICENSE_PAGE, 'edd_sample_license_page' );
		}

		function edd_sample_license_page() {
			$license = get_option( 'edd_sample_license_key' );
			$status  = get_option( 'edd_sample_license_status' );
			?>
			<div class="wrap">
				<h2><?php _e('License Options'); ?></h2>
				<form method="post" action="options.php">

					<?php settings_fields('edd_sample_license'); ?>

					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('License Key'); ?>
								</th>
								<td>
									<input id="edd_sample_license_key" name="edd_sample_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
									<label class="description" for="edd_sample_license_key"><?php _e('Enter your license key'); ?></label>
								</td>
							</tr>
							<?php if( false !== $license ) { ?>
								<tr valign="top">
									<th scope="row" valign="top">
										<?php _e('Activate License'); ?>
									</th>
									<td>
										<?php if( $status !== false && $status == 'valid' ) { ?>
											<span style="color:green;"><?php _e('active'); ?></span>
											<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
											<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
										<?php } else {
											wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
											<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<?php submit_button(); ?>

				</form>
			<?php
		}

		function edd_sample_register_option() {
			// creates our settings in the options table
			register_setting('edd_sample_license', 'edd_sample_license_key', 'edd_sanitize_license' );
		}

		function edd_sanitize_license( $new ) {
			$old = get_option( 'edd_sample_license_key' );
			if( $old && $old != $new ) {
				delete_option( 'edd_sample_license_status' ); // new license has been entered, so must reactivate
			}
			return $new;
		}


		/************************************
		* this illustrates how to activate
		* a license key
		*************************************/

		function edd_sample_activate_license() {

			// listen for our activate button to be clicked
			if( isset( $_POST['edd_license_activate'] ) ) {

				// run a quick security check
			 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( 'edd_sample_license_key' ) );


				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_name'  => urlencode( EDD_SAMPLE_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}

				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					if ( false === $license_data->success ) {

						switch( $license_data->error ) {

							case 'expired' :

								$message = sprintf(
									__( 'Your license key expired on %s.' ),
									date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
								);
								break;

							case 'revoked' :

								$message = __( 'Your license key has been disabled.' );
								break;

							case 'missing' :

								$message = __( 'Invalid license.' );
								break;

							case 'invalid' :
							case 'site_inactive' :

								$message = __( 'Your license is not active for this URL.' );
								break;

							case 'item_name_mismatch' :

								$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), EDD_SAMPLE_ITEM_NAME );
								break;

							case 'no_activations_left':

								$message = __( 'Your license key has reached its activation limit.' );
								break;

							default :

								$message = __( 'An error occurred, please try again.' );
								break;
						}

					}

				}

				// Check if anything passed on a message constituting a failure
				if ( ! empty( $message ) ) {
					$base_url = admin_url( 'edit.php?post_type=awesome-hook&page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// $license_data->license will be either "valid" or "invalid"

				update_option( 'edd_sample_license_status', $license_data->license );
				wp_redirect( admin_url( 'edit.php?post_type=awesome-hook&page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE ) );
				exit();
			}
		}

		/***********************************************
		* Illustrates how to deactivate a license key.
		* This will decrease the site count
		***********************************************/

		function edd_sample_deactivate_license() {

			// listen for our activate button to be clicked
			if( isset( $_POST['edd_license_deactivate'] ) ) {

				// run a quick security check
			 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( 'edd_sample_license_key' ) );


				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_name'  => urlencode( EDD_SAMPLE_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}

					$base_url = admin_url( 'edit.php?post_type=awesome-hook&page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"
				if( $license_data->license == 'deactivated' ) {
					delete_option( 'edd_sample_license_status' );
				}

				wp_redirect( admin_url( 'edit.php?post_type=awesome-hook&page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE ) );
				exit();

			}
		}

		/************************************
		* this illustrates how to check if
		* a license key is still valid
		* the updater does this for you,
		* so this is only needed if you
		* want to do something custom
		*************************************/

		function edd_sample_check_license() {

			global $wp_version;

			$license = trim( get_option( 'edd_sample_license_key' ) );

			$api_params = array(
				'edd_action' => 'check_license',
				'license' => $license,
				'item_name' => urlencode( EDD_SAMPLE_ITEM_NAME ),
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( $license_data->license == 'valid' ) {
				echo 'valid'; exit;
				// this license is still valid
			} else {
				echo 'invalid'; exit;
				// this license is no longer valid
			}
		}

		/**
		 * This is a means of catching errors from the activation method above and displaying it to the customer
		 */
		function edd_sample_admin_notices() {
			if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

				switch( $_GET['sl_activation'] ) {

					case 'false':
						$message = urldecode( $_GET['message'] );
						?>
						<div class="error">
							<p><?php echo $message; ?></p>
						</div>
						<?php
						break;

					case 'true':
					default:
						// Developers can put a custom success message here for when activation is successful if they way.
						break;

				}
			}
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Awesome_Hooks_License::get_instance();

endif;