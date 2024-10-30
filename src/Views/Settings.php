<?php

namespace MailTree;

use MailTree\Models\Settings;

$settings = Settings::get();
// Load all capabilities an admin may have at their disposal.
$capabilities = $GLOBALS['wp_roles']->roles['administrator']['capabilities'];
$cronJobs = CronManager::getInstance()->getTasks();
?>

<div class="mailtree-page">
	<div class="wrap">
		<?php if (isset($_GET['mailtree_update_success'])) : ?>
			<?php if (1 == $_GET['mailtree_update_success']) : ?>
				<div class="notice notice-success">
					<p>
						<?php esc_html_e('Settings were successfully updated!', 'mailtree'); ?>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-error">
					<p>
						<?php esc_html_e('You didn\'t change any settings :)', 'mailtree'); ?>
					</p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<h2 class="heading"><?php esc_html_e('Mailtree - Settings', 'mailtree'); ?></h2>
		<form action="?page=<?php echo sanitize_text_field(GeneralHelper::$adminPageSlug); ?>&action=update_settings" method="post">
			<?php wp_nonce_field('update_settings'); ?>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label>
								<?php esc_html_e('User capability needed to see logs (and access REST API endpoints)', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<select name="mailtree_default_view_role">
								<?php foreach ($capabilities as $capability => $value) : ?>
									<option
									<?php
									if ($settings['mailtree_default_view_role'] == $capability) :
										?>
										 selected<?php endif; ?>>
										<?php echo esc_html($capability); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label>
								<?php esc_html_e('User capability needed to edit settings', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<select name="mailtree_default_settings_role">
								<?php foreach ($capabilities as $capability => $value) : ?>
									<option
									<?php
									if ($settings['mailtree_default_settings_role'] == $capability) :
										?>
										 selected<?php endif; ?>>
										<?php echo esc_html($capability); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<!-- <tr>
						<th scope="row">
							<label for="blogname">
								<?php esc_html_e('Auto delete logs?', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="radio" name="mailtree_auto_delete"
									   value="false"
									   <?php
										if (false == $settings['mailtree_auto_delete']) :
											?>
											 checked<?php endif; ?>>
								<span class="date-time-text date-time-custom-text">
									<?php esc_html_e('No', 'mailtree'); ?>
								</span>
							</label>
							<fieldset>
								<label>
									<input type="radio" name="mailtree_auto_delete"
										   value="true"
										   <?php
											if (true == $settings['mailtree_auto_delete']) :
												?>
												 checked<?php endif; ?>>
									<span class="date-time-text date-time-custom-text">
										<?php esc_html_e('Yes', 'mailtree'); ?>
									</span>
								</label>
								<span class="example">
									<select name="mailtree_timescale">
										<?php foreach (wp_get_schedules() as $key => $cronSchedule) : ?>
											<option value="<?php echo esc_attr($key); ?>"
																	  <?php
																		if (isset($settings['mailtree_timescale']) && $settings['mailtree_timescale'] == $key) :
																			?>
												 selected<?php endif; ?>>
												<?php echo esc_attr($cronSchedule['display']); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</span>
								<?php if (isset($cronJobs[0])) : ?>
									<p class="description">
										<?php
										/* translators: This displays when the next CRON will run. */
										printf(wp_kses_post(__('Will next run in: %s.', 'mailtree'), $cronJobs[0]['nextRun']));
										?>
									</p>
								<?php endif; ?>
							<label for="logpress">
								<?php esc_html_e('Logs can quickly take up much database space. Purge or export regularily. Or use <br><a href="#" target="_blank">another WordPress site</a> for saving log entries externally.', 'mailtree'); ?>
							</label>
							</fieldset>
						</td>
					</tr> -->
					<fieldset>
					<tr>
						<th scope="row">
							<label>
								<?php esc_html_e('Enable Backup to another WordPress', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<input size="45" type="checkbox" name="mailtree_logpress_enable" id="mailtree_logpress_enable" value="1"<?php checked( 1 == $settings['mailtree_logpress_enable'] ); ?> />
							<label for="logpress_enable">
								<?php wp_kses_post(_e('Send your log entries to another WordPress site? This must be active otherwise the credentials will be ignored. <br>PLEASE NOTE: Deinstalling Mailtree will remove all database entries.', 'mailtree')); ?>
						</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label>
								<?php esc_html_e('WordPress User', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<input size="45" type="text" name="mailtree_logpress_user" id="mailtree_logpress_user" value="<?php echo esc_attr($settings['mailtree_logpress_user'] ); ?>" />
							<label for="logpress_user">
								<?php esc_html_e('The WordPress username of your site.', 'mailtree'); ?>
						</label>
						</td>
					</tr>
						<th scope="row">
							<label>
								<?php esc_html_e('WordPress Application Password', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<input size="45" type="password" name="mailtree_logpress_key" id="mailtree_logpress_key" value="<?php echo esc_attr($settings['mailtree_logpress_key'] ); ?>" />
							<label for="logpress_password">
								<?php esc_html_e('The WordPress Application password of your WordPress site user.', 'mailtree'); ?>
						</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label>
								<?php esc_html_e('WordPress site URL', 'mailtree'); ?>
							</label>
						</th>
						<td>
							<input size="45" type="text" name="mailtree_logpress_url" id="mailtree_logpress_url" value="<?php echo esc_url_raw($settings['mailtree_logpress_url'] ); ?>" />
							<label for="logpress_url">
								<?php wp_kses_post(_e('The WordPress site URL with trailing slash at the end i.e.: https://oacstudio<b style="color:red;">/</b>', 'mailtree')); ?>
						</label>
						</td>
					</tr>
					</fieldset>
				</tbody>
			</table>
			<?php submit_button(esc_attr(__( 'Save Changes', 'mailtree' ))); ?>
		</form>
		<?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
	</div>
</div>
