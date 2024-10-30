<?php

namespace MailTree;

use MailTree\Models\Settings;

$settings = Settings::get();
$logs = MailAdminTable::getInstance();
$logs->prepare_items();
?>

<div class="mailtree-page">
	<?php
	require GeneralHelper::$pluginViewDirectory . '/NewMessageModal.php';
	require GeneralHelper::$pluginViewDirectory . '/ExportWarningDialog.php';
	?>

	<div class="wrap
	<?php
	if (count($logs->items) == 0) :
		?>
		 -empty<?php endif; ?>">
		<h2 class="heading"><?php esc_html_e('Mailtree - Logs', 'mailtree'); ?></h2>

		<!-- <?php if ($logs->totalItems > GeneralHelper::$logLimitBeforeWarning && false == $settings['mailtree_auto_delete']) : ?>
			<div class="notice notice-warning">
				<p>
					<?php
					/* translators: This warns that auto delete is turned off. */
					printf(wp_kses_post(__('You have <strong>over %1$s</strong> messages stored and <a href="%2$s">auto-delete is disabled</a>. As a result your database can become very large, please either allow auto-delete or delete some logs.',
						'mailtree')),
						wp_kses_post(GeneralHelper::$logLimitBeforeWarning),
						'?page=' . esc_url_raw(GeneralHelper::$settingsPageSlug)
					);
					?>
				</p>
			</div>
		<?php endif; ?> -->

		<div class="button-container">
			<!-- <button class="btn button-primary" data-toggle="modal" data-target="#new-message">
			// NOTE Disable new message for now.
				<?php // _e('New Message', 'mailtree'); ?>
			</button> -->

			<?php if ($logs->totalItems > GeneralHelper::$logLimitBeforeWarning) : ?>
				<button class="btn button-secondary" data-toggle="modal" data-target="#export-warning-dialog">
					<?php esc_html_e('Download CSV of all messages', 'mailtree'); ?>
				</button>
			<?php else : ?>
				<a href="
				<?php
				echo esc_url_raw(wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=export-all',
					'bulk-logs'));
				?>
					" class="btn button-secondary">
					<?php esc_html_e('Download CSV of all messages', 'mailtree'); ?>
				</a>
			<?php endif; ?>
		</div>

		<ul class="subsubsub">
			<li>
				<a href="?page=<?php echo wp_kses_post(GeneralHelper::$adminPageSlug); ?>"
					<?php
					if (!isset($_GET['post_status']) || 'any' == $_GET['post_status']) :
						?>
						 class="current"<?php endif; ?>>
					<?php esc_html_e('All', 'mailtree'); ?> <span class="count">(<?php echo esc_attr($logs->totalItems); ?>)</span>
				</a> |
			</li>
			<li>
				<a href="?page=<?php echo wp_kses_post(GeneralHelper::$adminPageSlug); ?>&post_status=successful"
					<?php
					if (isset($_GET['post_status']) && 'successful' == $_GET['post_status']) :
						?>
						 class="current"<?php endif; ?>>
					<?php esc_html_e('Successful', 'mailtree'); ?>
				</a> |
			</li>
			<li>
				<a href="?page=<?php echo wp_kses_post(GeneralHelper::$adminPageSlug); ?>&post_status=failed"
					<?php
					if (isset($_GET['post_status']) && 'failed' == $_GET['post_status']) :
						?>
						 class="current"<?php endif; ?>>
					<?php esc_html_e('Failed', 'mailtree'); ?>
				</a>
			</li>
		</ul>

		<form action="?page=<?php echo wp_kses_post(GeneralHelper::$adminPageSlug); ?>" method="post">
			<?php $logs->search_box(__('Search Logs', 'mailtree'), 'search_id'); ?>
			<?php $logs->display(); ?>
		</form>

		<?php require wp_kses_post(GeneralHelper::$pluginViewDirectory) . '/Footer.php'; ?>
	</div>

	<?php
	/** $log is used in LogModal.php  */
	foreach ($logs->items as $log) :
		require wp_kses_post(GeneralHelper::$pluginViewDirectory) . '/LogModal.php';
	endforeach;
	?>
</div>
