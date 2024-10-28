<?php
/**
 * Plugin Name: Awesome Hooks
 * Plugin URI: https://wordpress.org/plugins/awesome-hooks/
 * Description: Customizer your theme by adding the custom HTML though WordPress text editor or Code editor. Added in-build hook support for theme 'AwesomePress, Twenty Sixteen'.
 * Version: 1.0.1
 * Author: Surror
 * Author URI: https://surror.com/
 * Text Domain: awesome-hooks
 *
 * @package Awesome Hooks
 */

define( 'AWESOME_HOOKS_VERSION', '1.0.1' );
define( 'AWESOME_HOOKS_FILE', __FILE__ );
define( 'AWESOME_HOOKS_BASE', plugin_basename( AWESOME_HOOKS_FILE ) );
define( 'AWESOME_HOOKS_DIR', plugin_dir_path( AWESOME_HOOKS_FILE ) );
define( 'AWESOME_HOOKS_URI', plugins_url( '/', AWESOME_HOOKS_FILE ) );

require_once( 'classes/class-awesome-hooks.php' );
