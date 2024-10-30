<?php


namespace MailTree\Loggers;

use WP_Error;

interface LoggerContract {

	public function recordMail( $args);

	public function recordError( WP_Error $error);
}
