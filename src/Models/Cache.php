<?php

namespace MailTree\Models;

class Cache {

	private static $cache = [];

	public static function get( $key) {
		return isset(self::$cache[self::getHashedKey($key)]) ? self::$cache[self::getHashedKey($key)] : null;
	}

	public static function set( $key, $value) {
		self::$cache[self::getHashedKey($key)] = $value;

		/** Return value to allow chaining on $value */
		return $value;
	}

	public static function flush() {
		self::$cache = [];
	}

	private static function getHashedKey( $entry) {
		return md5(serialize($entry));
	}
}
