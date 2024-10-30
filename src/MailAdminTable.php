<?php
namespace MailTree;

use MailTree\Models\Logs;
class MailAdminTable extends \WP_List_Table {

	public $totalItems;
	private static $instance = false;

	public function __construct( $args = array()) {
		parent::__construct([
			'singular' => 'log',
			'plural' => 'logs',
			'ajax' => false
		]);
	}

	public static function getInstance() {
		if (false == self::$instance) {
			self::$instance = new MailAdminTable();
		}

		return self::$instance;
	}

	public function column_default( $item, $column_name) {
		switch ($column_name) {
			case 'time':
			case 'subject':
			case 'status':
				return $item[$column_name];
				break;
			case 'email_to':
			// case 'email_from':
			//     return esc_html($item[$column_name]);
			default:
				return print_r($item, true);
				break;
		}
	}

	public function column_time( $item) {
		return '<span data-hover-message="' . gmdate(GeneralHelper::$humanReadableDateFormat, $item['timestamp']) . '">' . $item['time'] . '</span>';
	}

	public function column_cb( $item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'id',
			$item['id']
		);
	}

	public function column_more_info( $item) {
		return '<a href="#" class="button button-secondary" data-toggle="modal" data-target="#' . $item['id'] . '">' . __('More Info' , 'mailtree') . '</a>';
	}

	public function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'status' => '',
			'email_to' => __('To', 'mailtree'),
			'subject' => __('Subject', 'mailtree'),
			// 'email_from' => __('From', 'mailtree'),
			'time' => __('Sent', 'mailtree'),
			'more_info' => ''
		];

		return $columns;
	}

	public function column_email_to( $item) {
		$actions = [
			'delete' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=delete&id=' . $item['id'], 'bulk-logs') . '">' . __('Delete', 'mailtree') . '</a>',
			'resend' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=resend&id=' . $item['id'], 'bulk-logs') . '">' . __('Resend', 'mailtree') . '</a>',
			'export' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=export&id=' . $item['id'], 'bulk-logs') . '">' . __('Download CSV', 'mailtree') . '</a>',
		];

		return sprintf('%1$s %2$s', $item['email_to'], $this->row_actions($actions));
	}

	public function column_status( $item) {
		return $item['status'] ? '<div class="status-indicator"></div>' : '<div class="-right" data-hover-message="' . $item['error'] . '"><div class="status-indicator -error"></div></div>';
	}

	public function column_subject( $item ) {
		return esc_html( $item['subject'] );
	}


	public function get_hidden_columns() {
		$userSaved = get_user_meta(
			get_current_user_id(),
			ScreenOptions::$optionIdsToWatch['logs_hidden_table_columns'],
			true
		);

		return !empty($userSaved) ? $userSaved : [
			// 'email_from'
		];
	}

	public function get_sortable_columns() {
		$sortable_columns = [
			'time' => ['time', false],
			'email_to' => ['email_to', false],
			'subject' => ['subject', false],
		];

		return $sortable_columns;
	}

	public function get_bulk_actions() {
		$actions = [
			'delete' => __('Delete', 'mailtree'),
			'resend' => __('Resend', 'mailtree'),
			'export' => __('Download CSV', 'mailtree')
		];

		return $actions;
	}

	public function process_bulk_action() {
	}

	public function getLogsPerPage() {
		$userSaved = get_user_meta(
			get_current_user_id(),
			ScreenOptions::$optionIdsToWatch['logs_per_page'],
			true
		);

		return !empty($userSaved) ? (int) $userSaved : GeneralHelper::$logsPerPage;
	}

	public function prepare_items() {
		$per_page = $this->getLogsPerPage();

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [$columns, $hidden, $sortable];
		$this->process_bulk_action();

		/** Can pass $_REQUEST because we whitelist and sanitize it at the model level */
		$this->items = Logs::get(array_merge([
			'paged' => $this->get_pagenum(),
			'post_status' => isset($_GET['post_status']) && in_array( $_GET['post_status'], array( 'successful', 'failed' ), true ) ? sanitize_text_field($_GET['post_status']) : 'any',
			'posts_per_page' => $per_page,
		], $_REQUEST));

		$this->totalItems = Logs::getTotalAmount();

		$this->set_pagination_args([
			'total_items' => $this->totalItems,
			'per_page' => $per_page,
			'total_pages' => Logs::getTotalPages($per_page)
		]);
	}
}
