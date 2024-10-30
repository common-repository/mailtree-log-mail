<?php

namespace MailTree;

use MailTree\Models\Settings;
use MailTree\Models\Logs;
use MailTree\GeneralHelper;

class RestApiSender {

	private static $instance = false;
	private $username = '';
	private $password ='';
	private $url = '';
	private $settings;


	/**
	 * Hook into Logger action hooks.
	 *
	 */
	public function __construct() {
		add_action(GeneralHelper::$pluginPrefix . '_mail_success', [$this, 'recordApiMail'], 10);
		add_action(GeneralHelper::$pluginPrefix . '_mail_failed', [$this, 'recordApiError'], 10, 2);
		add_action(GeneralHelper::$pluginPrefix . '_mail_retry', [$this, 'getMailForRetry'], 10);
		$this->settings   = Settings::get();
		$this->enable     = isset($this->settings['mailtree_logpress_enable']) ? $this->settings['mailtree_logpress_enable'] : 0;
		if (1 == $this->enable) {
		$this->username   = $this->settings['mailtree_logpress_user'];
		$this->password   = $this->settings['mailtree_logpress_key'];
		$this->url        = $this->settings['mailtree_logpress_url'];
		}
	}

	public static function getInstance() {
		if (false == self::$instance) {
			self::$instance = new RestApiSender();
		}

		return self::$instance;
	}

	public function recordApiMail( $args) {

		if (1 == $this->enable) {

			global $wpdb;

			$args['rest_api'] = true;

			$response = wp_remote_post( $this->url . 'wp-json/mailtree/v1/record/mail',
			array(
				'body'    => $args,
				'timeout'     => 45,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
					),
				)
			);

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {

			$this->manageMailRetry($args, $response);

			}

		}

	}

	public function recordApiError( $args, $mail_error = []) {

		if (1 == $this->enable) {
			if ([] != $mail_error) {
				$args['error']    = $mail_error['error'];
			}

			$response = wp_remote_post( $this->url . 'wp-json/mailtree/v1/record/error', array(
				'body'    => $args,
				'timeout'     => 45,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
				),
			) );

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {

			// NOTE Causes double AS schedule so disable.
			// $this->manageMailRetry($args, $response);

			}
		}
	}


	public function getMailForRetry( $id) {

		$logs = new Logs();

		$mail = $logs->get([
			'post__in' => $id
		]);

		if ( true === $mail[0]['status']) {

			$this->recordApiMail($mail[0]);

		} else {

			$this->recordApiError($mail[0]);

		}
	}

	public function manageMailRetry( $args, $response) {

		global $wpdb;

		/** Catch error */
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {

			/** Record REST API send status = failed. */
			$wpdb->update(
				$wpdb->prefix . GeneralHelper::$tableName, [
				'rest_status' => 0,
			], [
					'id' => $args['id']
				]
			);

			/** NOTE: Use action scheduler to retry request in two hours. */
			$interval = rand(30, 500);
			$timestamp = time() + 60*$interval;
			$hook = GeneralHelper::$pluginPrefix . '_mail_retry';
			$as_args = [];
			array_push($as_args, $args['id']);
			as_schedule_single_action( $timestamp, $hook, $as_args, $group = '' );

		} elseif ( wp_remote_retrieve_response_code( $response ) === 200 ) {

			/** Once request is complete, update rest_status */
			$wpdb->update(
				$wpdb->prefix . GeneralHelper::$tableName, [
				'rest_status' => 1,
			], [
					'id' => $args['id']
				]
			);
			// And done.
			return true;
		}


	}
}
