<?php

namespace MailTree\Models;

class Settings {

	public static $optionsName = 'mailtree_settings';
	private static $settings = null;
	private static $defaultSettings = [
		'mailtree_default_view_role' => 'manage_options',
		'mailtree_default_settings_role' => 'manage_options',
		// 'mailtree_auto_delete' => true,
		// 'mailtree_timescale' => 'oacs_mailtree_monthly',
		'mailtree_logpress_user' => '',
		'mailtree_logpress_key' => '',
		'mailtree_logpress_url' => '',
		'mailtree_logpress_enable' => 0,
	];


	public static function get( $key = null) {
		if (null == self::$settings) {
			self::$settings = unserialize(get_option(self::$optionsName, null));
		}

		if (null == self::$settings) {
			self::installOptions();
		}

		if (null != $key) {
			return isset(self::$settings[$key]) ? self::$settings[$key] : self::$defaultSettings[$key];
		}

		return self::$settings;
	}

	public static function update( $newValues) {
		$settings = self::get();

		foreach ($newValues as $key => $newValue) {
			$settings[$key] = $newValue;
		}

		self::$settings = $settings;
		return update_option(self::$optionsName, serialize($settings));
	}

	public static function installOptions( $force = false) {
		if (true == $force || get_option(self::$optionsName, false) == false) {
			add_option(self::$optionsName, serialize(self::$defaultSettings), '', 'no');
			self::$settings = self::$defaultSettings;
		}
	}


	public static function uninstallOptions() {
		delete_option(self::$optionsName);
	}
}
