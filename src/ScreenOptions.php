<?php

namespace MailTree;

class ScreenOptions {

	private static $instance;
	private $options = [];
	private $helpTabs = [];
	private $currentScreen = null;
	public static $optionIdsToWatch = [
		'logs_per_page' => 'toplevel_page_mailtree_per_page',
		// NOTE This saves which screen options column have been hidden.
		'logs_hidden_table_columns' => 'managetoplevel_page_mailtreecolumnshidden'
	];

	private function __construct() {
		add_filter('set-screen-option', [$this, 'saveOption'], 10, 3);

		foreach (self::$optionIdsToWatch as $key => $value) {
			add_filter('set_screen_option_' . $value, [$this, 'saveOption'], 10, 3);
		}
	}

	public static function getInstance() {
		if (null == self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function saveOption( $keep, $option, $value) {
		return in_array($option, self::$optionIdsToWatch) ? $value : $keep;
	}

	public function newOption( $pageHook, $type, $args) {
		add_action('load-' . $pageHook, [$this, 'addToScreen']);

		$this->options[] = [
			'type' => $type,
			$args
		];
	}

	public function newHelpTab( $pageHook, $title, $content) {
		// NOTE this is unhooked still. No output.
		$this->helpTabs[] = [
			'id' => $pageHook . count($this->helpTabs),
			'title' => $title,
			'content' => $content
		];
	}

	/**
	 * Forces WP to handle dynamic column visibility tick boxes
	 * inside of "Screen Options" tab. Because they're singletons
	 * initialising them now won't cause any extra overhead
	 */
	private function initTables() {
		MailAdminTable::getInstance();
	}

	public function addToScreen() {
		$this->currentScreen = get_current_screen();
		$this->initTables();

		foreach ($this->helpTabs as $helpTab) {
			$this->currentScreen->add_help_tab($helpTab);
		}

		foreach ($this->options as $option) {
			add_screen_option($option['type'], $option);
		}
	}
}
