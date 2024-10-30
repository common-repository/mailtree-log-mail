<?php
namespace MailTree;

use MailTree\Loggers\Logger;
use MailTree\GeneralHelper;
use MailTree\PostCounter;

class RestApiEndpoint extends Logger {

	private static $instance = false;

	public function __construct() {
		add_action( 'rest_api_init', [$this, 'registerEndpointSuccess' ]);
		add_action( 'rest_api_init', [$this, 'registerEndpointError' ]);
	}

	public static function getInstance() {
		if (null == self::$instance) {
			self::$instance = new RestApiEndpoint();
		}

		return self::$instance;
	}

	public function registerEndpointSuccess () {
	  register_rest_route( 'mailtree/v1/record', '/mail', array(
	  array(
		'methods' => 'POST',
		'callback' => [$this, 'recordMailRestApi'],
		'permission_callback' => function () {
		  $a = get_option( 'mailtree_settings', false );
		  $b = unserialize($a);
		  $c = $b['mailtree_default_view_role'];
		  return current_user_can( $c );
		}
	  )
	  )
	  );
	}

	public function registerEndpointError () {
		register_rest_route( 'mailtree/v1/record', '/error', array(
		'methods' => 'POST',
		'callback' => [$this, 'recordErrorRestApi'],
		'permission_callback' => function () {
			$a = get_option( 'mailtree_settings', false );
			$b = unserialize($a);
			$c = $b['mailtree_default_view_role'];
			return current_user_can( $c );
		}
		) );
	}
}
