<?php

namespace MailTree;

use MailTree\Loggers\WpMail;

class LoggerFactory {

	public static function Set() {
		/**
		 *  If and when more loggers are added, the logic
		 *  that determines which one to use will go here. Currently there
		 *  is only one.
		 */

		// Initiate our mail logger
		new WpMail();
	}
}
