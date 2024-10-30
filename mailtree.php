<?php
/*
Plugin Name: Mailtree Log Mail
Plugin URI: https://wordpress.org/plugins/mailtree-log-mail/
Text Domain: mailtree
Domain Path: /languages
Description: A solid mail logger with additional REST API support to backup your messages to an external WordPress automatically.
Author: oacstudio
Author URI: https://oacstudio.de/
Version: 1.0.1
*/
use MailTree\Bootstrap;
require_once __DIR__ . '/vendor/autoload.php';
// Load action scheduler which is used for the retry function for API calls.
require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );

/** Engage! */
$bootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$bootstrap, 'install']);
register_deactivation_hook(__FILE__, ['mailtree\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['mailtree\Bootstrap', 'uninstall']);

// Friendly advice:  Namespace declarations in root plugin file can eat plugin settings links functions that don't use namespaces ;).

function oacs_mtr_myplugin_settings_link( $links ) {
	$url = get_admin_url() . 'admin.php?page=oacs_mailtree-settings';
	$settings_link = '<a href="' . $url . '">' . __('Settings', 'mailtree') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'oacs_mtr_myplugin_settings_link');



/** You can use https://github.com/ExodiusStudios/vscode-comment-anchors comment anchors to navigate to certain code comment blocks. */
