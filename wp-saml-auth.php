<?php
/**
 * Plugin Name: WP SAML Auth
 * Version: 0.2.1
 * Description: SAML authentication for WordPress, using SimpleSAMLphp.
 * Author: Pantheon
 * Author URI: https://pantheon.io
 * Plugin URI: https://wordpress.org/plugins/wp-saml-auth/
 * Text Domain: wp-saml-auth
 * Domain Path: /languages
 *
 * @package Wp_Saml_Auth
 */

/**
 * Provides default options for WP SAML Auth.
 *
 * @param mixed  $value       Configuration value.
 * @param string $option_name Configuration option name.
 */
function wpsa_filter_option( $value, $option_name ) {
	$defaults = array(
		/**
		 * Path to SimpleSAMLphp autoloader.
		 *
		 * Follow the standard implementation by installing SimpleSAMLphp
		 * alongside the plugin, and provide the path to its autoloader.
		 * Alternatively, this plugin will work if it can find the
		 * `SimpleSAML_Auth_Simple` class.
		 *
		 * @param string
		 */
		'simplesamlphp_autoload' => dirname( __FILE__ ) . '/simplesamlphp/lib/_autoload.php',
		/**
		 * Authentication source to pass to SimpleSAMLphp
		 *
		 * This must be one of your configured identity providers in
		 * SimpleSAMLphp. If the identity provider isn't configured
		 * properly, the plugin will not work properly.
		 *
		 * @param string
		 */
		'auth_source'            => 'default-sp',
		/**
		 * Whether or not to automatically provision new WordPress users.
		 *
		 * When WordPress is presented with a SAML user without a
		 * corresponding WordPress account, it can either create a new user
		 * or display an error that the user needs to contact the site
		 * administrator.
		 *
		 * @param bool
		 */
		'auto_provision'         => true,
		/**
		 * Whether or not to permit logging in with username and password.
		 *
		 * If this feature is disabled, all authentication requests will be
		 * channeled through SimpleSAMLphp.
		 *
		 * @param bool
		 */
		'permit_wp_login'        => true,
		/**
		 * Attribute by which to get a WordPress user for a SAML user.
		 *
		 * @param string Supported options are 'email' and 'login'.
		 */
		'get_user_by'            => 'email',
		/**
		 * SAML attribute which includes the user_login value for a user.
		 *
		 * @param string
		 */
		'user_login_attribute'   => 'uid',
		/**
		 * SAML attribute which includes the user_email value for a user.
		 *
		 * @param string
		 */
		'user_email_attribute'   => 'mail',
		/**
		 * SAML attribute which includes the display_name value for a user.
		 *
		 * @param string
		 */
		'display_name_attribute' => 'display_name',
		/**
		 * SAML attribute which includes the first_name value for a user.
		 *
		 * @param string
		 */
		'first_name_attribute' => 'first_name',
		/**
		 * SAML attribute which includes the last_name value for a user.
		 *
		 * @param string
		 */
		'last_name_attribute' => 'last_name',
		/**
		 * Default WordPress role to grant when provisioning new users.
		 *
		 * @param string
		 */
		'default_role'           => get_option( 'default_role' ),
	);
	$value = isset( $defaults[ $option_name ] ) ? $defaults[ $option_name ] : $value;
	return $value;
}
add_filter( 'wp_saml_auth_option', 'wpsa_filter_option', 0, 2 );

/**
 * Initialize the WP SAML Auth plugin.
 *
 * Core logic for the plugin is in the WP_SAML_Auth class.
 */
require_once dirname( __FILE__ ) . '/inc/class-wp-saml-auth.php';
WP_SAML_Auth::get_instance();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/inc/class-wp-saml-auth-cli.php';
	WP_CLI::add_command( 'saml-auth', 'WP_SAML_Auth_CLI' );
}

/**
 * Password_Reset_Removed.
 */
class Password_Reset_Removed
{

    function __construct()
    {
        add_filter( 'show_password_fields', array( $this, 'wpsa_disable' ) );
        add_filter( 'allow_password_reset', array( $this, 'wpsa_disable' ) );
        add_filter( 'lostpassword_url', array( $this, 'custom_password_reset_url' ) );
    }

    function wpsa_disable()
    {
        if ( is_admin() ) {
            $userdata = wp_get_current_user();
            $user = new WP_User($userdata->ID);
            if ( !empty( $user->roles ) && is_array( $user->roles ) && $user->roles[0] == 'administrator' )
                return true;
        }
        return false;
    }

    function custom_password_reset_url() 
	{
		//Add custom Passord Reset URL
        $password_reset_url = '';
        if(!empty($password_reset_url) ){
            return $password_reset_url;
            exit;
        } else {
            return '?action=lostpassword';
        }
    }
}

$pass_reset_removed = new Password_Reset_Removed();
