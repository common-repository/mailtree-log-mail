<?php

namespace MailTree;

use MailTree\Models\Logs;
use MailTree\Models\Mail;
use MailTree\Models\Settings;
use MailTree\RestApiEndpoint;
use MailTree\RestApiSender;
class Bootstrap {

	private $screenOptions;

	public function __construct() {
		// NOTE load all default variables for settings.
		GeneralHelper::setSettings();
		// NOTE later when there might be more logging types this will be important.
		LoggerFactory::Set();
		// NOTE Disable auto delete.
		// $this->registerCronTasks();
		// NOTE Instantiate custom screen options.
		$this->screenOptions = ScreenOptions::getInstance();

		RestApiEndpoint::getInstance();
		RestApiSender::getInstance();

		// ensure that is_plugin_active_for_network() is defined, so we can use it in the followning control statement.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if (is_plugin_active_for_network(GeneralHelper::$pluginMainPhpFile)) {
			// If network activation is in play use the per site hook for older WordPress installations.
			$hook = 'wp_initialize_site';

			if (version_compare(get_bloginfo('version'), '5.1', '<')) {
			// If network activation is in play use the per site hook.
				$hook = 'wpmu_new_blog';
			}

			add_action($hook, [$this, 'install']);
		}

		// NOTE Identify the tables to drop when a site is deleted in multi site.
		add_filter('wpmu_drop_tables', function ( $tables) {
			$tables[] = $GLOBALS['wpdb']->prefix . GeneralHelper::$tableName;
			return $tables;
		});
		// NOTE Load plugin links, assets, textdomain and custom admin settings.
		add_action('admin_enqueue_scripts', [$this, 'enqueue']);
		add_action('plugins_loaded', function() {
			load_plugin_textdomain('mailtree', false, GeneralHelper::$adminPageSlug . '/languages/');
		});
		add_action('admin_menu', function() {
			$this->registerPages();
			$this->route();
		});
	}

	/** NOTE Disable the auto delete Cron task.
	*** TODO Implement Action Scheduler auto delete
	*/

	// public function registerCronTasks()
	// {
	//     if (Settings::get('mailtree_auto_delete') == true) {
	//         $cronManager = CronManager::getInstance();
	//         $cronManager->addTask('mailtree\Models\Logs::truncate', Settings::get('mailtree_timescale'), 'Truncate');
	//     }
	// }

	// NOTE Enqueue assets.
	public function enqueue() {

		$pluginMain = 'mailtree-log-mail';
		$pluginUrl = plugins_url($pluginMain);
		$pluginAssetsUrl = $pluginUrl . '/assets';

		wp_enqueue_style('dashicons', '', array(), GeneralHelper::$pluginVersion );
		wp_enqueue_style('admin_css', $pluginAssetsUrl . '/css/styles.min.css?v=' . GeneralHelper::$pluginVersion, array(), GeneralHelper::$pluginVersion);
		// wp_enqueue_style('admin_css', GeneralHelper::$pluginAssetsUrl . '/css/styles.min.css?v=' . GeneralHelper::$pluginVersion);
		wp_enqueue_script('admin_js', $pluginAssetsUrl . '/js/index.min.js?v=' . GeneralHelper::$pluginVersion, ['jquery'], array(), GeneralHelper::$pluginVersion);
		// wp_enqueue_script('admin_js', GeneralHelper::$pluginAssetsUrl . '/js/index.min.js?v=' . GeneralHelper::$pluginVersion, ['jquery']);
		wp_localize_script('admin_js', GeneralHelper::$tableName, [
			'plugin_url' => GeneralHelper::$pluginUrl,
		]);
	}

	public function registerPages() {
		$mainPageHook = add_menu_page('Logs', 'Mailtree', Settings::get('mailtree_default_view_role'),
			GeneralHelper::$adminPageSlug, function() {
				require GeneralHelper::$pluginViewDirectory . '/Log.php';
			}, 'dashicons-email-alt'
		);

		add_submenu_page(GeneralHelper::$adminPageSlug, __('Settings', 'mailtree'), __('Settings', 'mailtree'), Settings::get('mailtree_default_settings_role'),
			GeneralHelper::$settingsPageSlug, function() {
				require GeneralHelper::$pluginViewDirectory . '/Settings.php';
			}
		);
		// NOTE this loads the logs per page integer as default settings value
		$this->screenOptions->newOption($mainPageHook, 'per_page', [
			'default' => GeneralHelper::$logsPerPage
		]);
		// NOTE This callback contains an array for usefull parameters, but there is  no hook yet. For another time.
	//    $this->screenOptions->newHelpTab($mainPageHook, 'General', '<strong>blah</strong> blah');
	}

	public function route() {
	// NOTE This manages the requests and callbacks for all form requests.
		if (isset($_GET['page'])) {
			if (GeneralHelper::$adminPageSlug == $_GET['page'] ) {
				if (current_user_can(Settings::get('mailtree_default_view_role'))) {

					if (isset($_REQUEST['action']) && 'export-all' == $_REQUEST['action'] ) {
						if (isset ($_REQUEST['_wpnonce'])) {
							if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-logs')) {
								wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
							}
						}

						// Are we over the hardcoded limit?
						if (isset($_REQUEST['posts_per_page']) && isset($_REQUEST['paged'])) {
							$args = Logs::getTotalAmount() > GeneralHelper::$logLimitBeforeWarning ? [
							'posts_per_page' => sanitize_key($_REQUEST['posts_per_page']),
							'paged' => sanitize_key($_REQUEST['paged']),
							] : [
							'posts_per_page' => -1
							];
						} else {
							$args = ['posts_per_page' => -1];
						}


						Mail::export(wp_list_pluck(
							Logs::get($args),
							'id'
						));
					}

					/** Export message(s) */
					if (isset($_REQUEST['action']) && 'export' == $_REQUEST['action'] ||
						isset($_REQUEST['action2']) && 'export' == $_REQUEST['action2']) {
						if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-logs')) {
							wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
						}
						if (isset($_REQUEST['id'])) {
							if ( is_array( $_REQUEST['id'] ) ) {
								Mail::export(sanitize_term($_REQUEST['id'], 'id', 'db'));
							} else {
								Mail::export(sanitize_key($_REQUEST['id']));
							}
						}
					}

					/** Resend message(s) */
					if (( ( isset($_REQUEST['action']) && 'resend' == $_REQUEST['action'] ) || ( isset($_REQUEST['action2']) && 'resend' == $_REQUEST['action2'] ) ) &&
						isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
						if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-logs')) {
							wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
						}
						if ( is_array( $_REQUEST['id'] ) ) {
							Mail::resend(sanitize_term($_REQUEST['id'], 'id', 'db'));
						} else {
							Mail::resend(sanitize_key($_REQUEST['id']));
						}
						GeneralHelper::redirectToThisHomeScreen();
					}

					/** Delete message(s) */
					if (( ( isset($_REQUEST['action']) && 'delete' == $_REQUEST['action'] ) || ( isset($_REQUEST['action2']) && 'delete' == $_REQUEST['action2'] ) ) &&
						isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
						if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'bulk-logs')) {
							wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
						}
						if ( is_array( $_REQUEST['id'] ) ) {
							Logs::delete(sanitize_term($_REQUEST['id'], 'id', 'db'));
						} else {
							Logs::delete(sanitize_key($_REQUEST['id']));
						}
						GeneralHelper::redirectToThisHomeScreen();
					}

					/** Send mail */
					if (isset($_REQUEST['action']) && 'new_mail' == $_REQUEST['action']) {

						if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'new_mail')) {
							wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
						}

						if (isset($_POST['header_keys']) && isset($_POST['header_values']) && isset($_POST['attachment_ids']) && isset($_POST['subject']) && isset($_POST['message'])) {
							Mail::add(sanitize_key($_POST['header_keys']), sanitize_key($_POST['header_values']), sanitize_key($_POST['attachment_ids']),
							sanitize_key($_POST['subject']),
							sanitize_key($_POST['message']));
							GeneralHelper::redirectToThisHomeScreen();
						}
					}

					if (isset($_REQUEST['action']) && 'single_mail' == $_REQUEST['action'] &&
						isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
						$log = Logs::get(['post__in' => [sanitize_key($_REQUEST['id'])]])[0];
						$view = GeneralHelper::$pluginViewDirectory;
						$view .= true == $log['is_html'] ? '/HtmlMessage.php' : '/TextMessage.php';

						require $view;
						exit;
					}
				}

				/** NOTE Update settings */
				if (current_user_can(Settings::get('mailtree_default_settings_role'))) {
					if (isset($_REQUEST['action']) && 'update_settings' == $_REQUEST['action']) {
						if (!wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'update_settings')) {
							wp_die(wp_kses_post(GeneralHelper::$failedNonceMessage));
						}

						/** TODO Roadmap: Auto purge */
						// if (isset($_POST['mailtree_auto_delete'])) {
						// 	$_POST['mailtree_auto_delete'] = 'true' === $_POST['mailtree_auto_delete'];
						// }
						// CronManager::getInstance()->clearTasks();

						if (isset($_POST['mailtree_default_view_role'])) {
							$default_view_role_value = sanitize_key($_POST['mailtree_default_view_role']);
						}
						if (isset($_POST['mailtree_default_settings_role'])) {
							$default_settings_role_value = sanitize_key($_POST['mailtree_default_settings_role']);
						}
						if (isset($_POST['mailtree_auto_delete'])) {
							$auto_delete_value = sanitize_key($_POST['mailtree_auto_delete']);
						}
						/** TODO Roadmap: Auto purge */
						// if (isset($_POST['mailtree_timescale'])) {
						// 	$timescale_value = sanitize_key($_POST['mailtree_timescale']);
						// }
						if (isset($_POST['mailtree_logpress_user'])) {
							$logpress_user_value = sanitize_key($_POST['mailtree_logpress_user']);
						}
						if (isset($_POST['mailtree_logpress_key'])) {
							$logpress_key_value = sanitize_text_field($_POST['mailtree_logpress_key']);
						}
						if (isset($_POST['mailtree_logpress_url'])) {
							$logpress_url_value = sanitize_url($_POST['mailtree_logpress_url']);
						}
						if (isset($_POST['mailtree_logpress_enable'])) {
							$logpress_enable_value = sanitize_key($_POST['mailtree_logpress_enable']);
						}

						$updateSuccess = Settings::update([
							'mailtree_default_view_role' => $default_view_role_value,
							'mailtree_default_settings_role' => $default_settings_role_value,
							// 'mailtree_auto_delete' => $auto_delete_value,
							// 'mailtree_timescale' => true == $auto_delete_value ? null : $timescale_value,
							'mailtree_logpress_user' => $logpress_user_value,
							'mailtree_logpress_key' => $logpress_key_value,
							'mailtree_logpress_url' => $logpress_url_value,
							'mailtree_logpress_enable' => $logpress_enable_value
						]);

						GeneralHelper::redirectToThisHomeScreen([
							'mailtree_update_success' => $updateSuccess,
							'page' => GeneralHelper::$adminPageSlug . '-settings'
						]);
					}
				}
			}
		}
	}

	public function install( $newSite = null) {
		global $wpdb;

		if (null != $newSite) {
			// $new_site will only be passed when we're called via the wp_insert_site (WP >=5.1)
			// or wpmu_new_blog (WP < 5.1) actions being fired.  When wp_insert_site is fired,
			// it passes a WP_Site object; whereas, when wpmu_new_blog fires, it passes the
			// blog_id.
			if ('wp_initialize_site' === current_action()) {
				$newSite = $newSite->blog_id;
			}

			switch_to_blog($newSite);
		}

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . GeneralHelper::$tableName . ' (
                  id int NOT NULL AUTO_INCREMENT,
                  time int NOT NULL,
                  email_to text DEFAULT NULL,
                  subject text DEFAULT NULL,
                  message text DEFAULT NULL,
                  backtrace_segment text NOT NULL,
                  status bool DEFAULT 1 NOT NULL,
                  rest_status bool DEFAULT 1 NOT NULL,
                  error text DEFAULT NULL,
                  attachments text DEFAULT NULL,
                  additional_headers text DEFAULT NULL,
                  PRIMARY KEY  (id)
                ) ' . $wpdb->get_charset_collate() . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		Settings::installOptions();

		if (null != $newSite) {
			restore_current_blog();
		}
	}

	public static function deactivate() {
		$cronManager = CronManager::getInstance();
		$cronManager->clearTasks();
	}

	public static function uninstall() {
		self::deactivate();

		global $wpdb;

		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mailtree_logs");

		Settings::uninstallOptions();
	}
}
