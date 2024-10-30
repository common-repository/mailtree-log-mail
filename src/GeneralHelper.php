<?php

namespace MailTree;

class GeneralHelper {

	public static $csvItemDelimiter = ' | ';
	// NOTE This gets used in the Bootstrap.php
	public static $logsPerPage = 20;
	public static $pluginPath;
	public static $pluginUrl;
	public static $pluginVersion;
	public static $tableName;
	public static $csvExportLegalColumns;
	public static $csvExportFileName;
	public static $adminUrl;
	public static $adminPageSlug;
	public static $uploadsFolderInfo;
	public static $pluginAssetsUrl;
	public static $pluginViewDirectory;
	public static $attachmentNotInMediaLib;
	public static $attachmentNotImageThumbnail;
	public static $failedNonceMessage;
	public static $pluginMainPhpFile;
	public static $settingsPageSlug;
	public static $logLimitBeforeWarning;
	public static $humanReadableDateFormat;
	public static $namespacePrefix;
	public static $reviewLink;
	public static $logpressLink;
	public static $supportLink;
	public static $actionNameSpace;
	public static $pluginPrefix;

	public static function setSettings() {
		self::$csvExportFileName = 'mailtree_export_' . gmdate('d-m-Y_H-i-s') . '.csv';
		// NOTE legal columns are the ones with unserialised data.
		self::$csvExportLegalColumns = [
			'time',
			'subject',
			'email_to',
			'message',
			'attachments',
			'additional_headers',
			'status',
			'error'
		];
		self::$tableName = 'mailtree_logs';
		self::$adminUrl = admin_url();
		self::$pluginPath = __DIR__ . '/..';
		self::$pluginMainPhpFile = self::$pluginPath . '/mailtree.php';
		self::$pluginUrl = plugins_url('..', self::$pluginPath);
		self::$adminPageSlug = 'oacs_mailtree';
		self::$uploadsFolderInfo = wp_upload_dir();
		self::$pluginAssetsUrl = self::$pluginUrl . '/assets';
		self::$pluginViewDirectory = __DIR__ . '/Views';
		self::$attachmentNotInMediaLib = 'An attachment was sent but it was not in the media library';
		self::$attachmentNotImageThumbnail = self::$pluginAssetsUrl . '/file-icon.png';
		self::$failedNonceMessage = 'Failed security check';
		self::$pluginVersion = get_file_data(self::$pluginMainPhpFile, ['Version'], 'plugin')[0];
		self::$settingsPageSlug = self::$adminPageSlug . '-settings';
		// TODO Add setting for export limit.
		self::$logLimitBeforeWarning = 100;
		self::$humanReadableDateFormat = get_option('date_format') . ' H:i:s';
		self::$namespacePrefix = self::$adminPageSlug . '_';
		self::$reviewLink = 'https://wordpress.org/support/plugin/mailtree-log-mail/reviews/#new-post';
		self::$logpressLink = 'https://logpress.app/';
		self::$supportLink = 'https://wordpress.org/support/plugin/mailtree-log-mail/';
		self::$actionNameSpace = 'MailTree';
		self::$pluginPrefix = 'oacs_mailtree';

	}

	/**
	 * Flattens an array to dot notation.
	 *
	 * @param array $array An array
	 * @param string $separator The character to flatten with
	 * @param string $parent The parent passed to the child (private)
	 *
	 * @return array Flattened array to one level
	 */
	public static function flatten( $array, $separator = '.', $parent = null) {
		if (!is_array($array)) {
			return $array;
		}

		$_flattened = [];

		// Rewrite keys
		foreach ($array as $key => $value) {
			if ($parent) {
				$key = $parent . $separator . $key;
			}
			$_flattened[$key] = self::flatten($value, $separator, $key);
		}

		// Flatten
		$flattened = [];
		foreach ($_flattened as $key => $value) {
			if (is_array($value)) {
				$flattened = array_merge($flattened, $value);
			} else {
				$flattened[$key] = $value;
			}
		}

		return $flattened;
	}

	public static function arrayToString( $pieces, $glue = ', ') {
		$result = self::flatten($pieces);

		if (is_array($result)) {
			$result = implode($glue, $pieces);
		}

		return $result;
	}

	public static function slugToLabel( $slug) {
		$illegalChars = [
			'-',
			'_'
		];

		foreach ($illegalChars as $illegalChar) {
			$slug = str_replace($illegalChar, ' ', $slug);
		}

		return mb_convert_case($slug, MB_CASE_TITLE, 'UTF-8');
	}

	public static function labelToSlug( $label) {
		$label = str_replace(' ', '-', $label);
		return strtolower($label);
	}

	public static function sanitiseForQuery( $value) {
		switch (gettype($value)) {
			case ( 'array' ):
				array_walk_recursive($value, function ( &$value) {
					$value = sanitize_text_field($value);
				});
				break;
			default:
				$value = sanitize_text_field($value);
				break;

		}

		return $value;
	}

	public static function getAttachmentIdsFromUrl( $urls) {
		if (empty($urls)) {
			return [];
		}

		global $wpdb;

		$urls = self::sanitiseForQuery($urls);

		$sql = $wpdb->prepare(
			"SELECT DISTINCT
				post_id
            FROM
				`{$wpdb->prefix}postmeta`
			WHERE
				meta_value LIKE %s", '%' . $urls[0] . '%');

		if (is_array($urls) && count($urls) > 1) {
			array_shift($urls);
			foreach ($urls as $url) {
				$sql .= $wpdb->prepare(
				' OR
				meta_value
				LIKE %s', '%' . $url . '%');
			}
		}

		$sql .= $wpdb->prepare(
					' AND meta_key
					= %s', '_wp_attached_file');

		$results = $wpdb->get_results($sql, ARRAY_N);

		if (isset($results[0])) {
			return array_column($results, 0);
		}

		return [];
	}

	public static function redirectToThisHomeScreen( $params = []) {
		if (!isset($params['page'])) {
			$params['page'] = self::$adminPageSlug;
		}

		// header('Location: ' . self::$adminUrl . 'admin.php?' . $params);
		header('Location: ' . self::$adminUrl . 'admin.php?' . http_build_query($params));
		exit;
	}

	public static function doesArrayContainSubString( $array = [], $subString) {
		foreach ($array as $element) {
			if (stripos($element, $subString) !== false) {
				return true;
			}
		}

		return false;
	}

	public static function searchForSubStringInArray( $array, $subString) {
		foreach ($array as $element) {
			if (stripos($element, $subString) !== false) {
				return $element;
			}
		}

		return false;
	}

	public static function getHumanReadableTime( $from, $to, $suffix = ' ago') {
		/** TODO Add time format setting */
		return sprintf(
		/* translators: This prints out the "2 hours ago" string */
			_x('%s' . $suffix, '%s = human-readable time difference', 'mailtree'),
			human_time_diff($from, $to)
		);
	}

	/**
	 * Retrieves current timestamp using WPs native functions and translation
	 *
	 * @param $from @type timestamp
	 * @param string $suffix
	 * @return string
	 */
	public static function getHumanReadableTimeFromNow( $from, $suffix = ' ago') {
		return self::getHumanReadableTime($from, time(), $suffix);
	}

	/**
	 * Generates a near unique, replicable key based on a string value
	 *
	 * @param $slugOrLabel
	 * @return string
	 */
	public static function getPrefixedSlug( $slugOrLabel) {
		return self::$namespacePrefix . self::labelToSlug($slugOrLabel);
	}
}


