<?php

use MailTree\GeneralHelper;

if (isset($log)) :
	?>
	<div id="<?php echo esc_attr($log['id']); ?>" class="modal">
		<div class="modal-content <?php echo esc_attr($log['is_html']) ? 'is-html' : 'is-not-html'; ?>">
			<div class="modal-body">
				<h2 class="nav-tab-wrapper">
					<a href="#" class="nav-tab nav-tab-active"><?php esc_html_e('Message', 'mailtree'); ?></a>
					<a href="#" class="nav-tab"><?php esc_html_e('Detail', 'mailtree'); ?></a>
					<a href="#" class="nav-tab"><?php esc_html_e('Debug', 'mailtree'); ?></a>
				</h2>
				<div class="content-container">
					<div class="content -active">
						<iframe class="html-preview"
								data-src="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>&action=single_mail&id=<?php echo esc_attr($log['id']); ?>"></iframe>
					</div>
					<div class="content">
						<?php if (empty($log['attachments']) && empty($log['additional_headers'])) : ?>
							<p>
								<?php esc_html_e('There aren\'t any details to show!', 'mailtree'); ?>
							</p>
						<?php else : ?>
							<?php if (!empty($log['attachments'])) : ?>
								<h3><?php esc_html_e('Attachments', 'mailtree'); ?></h3>
								<hr/>
								<?php
								if (is_string($log['attachments'])) :
									echo wp_kses_post('Attachment source URL: <a target="_blank" href="' . $log['attachments'] . '">Open attachment URL in new tab.</a>');
								endif;
								?>
								<ul>
									<?php foreach ($log['attachments'] as $attachment) : ?>
										<li class="attachment-container">
											<?php
											if (isset($attachment['note'])) :
												echo wp_kses_post($attachment['note']);
												continue;
											endif;
											?>

											<a href="<?php echo esc_url_raw($attachment['url']); ?>" target="_blank"
											   class="attachment-item"
											   style="background-image: url(<?php echo esc_url_raw($attachment['src']); ?>);"></a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if (empty(array_filter($log['additional_headers']))) : ?>
								<p><?php esc_html_e('No additional details to show', 'mailtree'); ?></p>
							<?php else : ?>
								<h3><?php esc_html_e('Additional Headers', 'mailtree'); ?></h3>
								<hr/>
								<ul>
									<?php foreach ($log['additional_headers'] as $additionalHeader) : ?>
										<li><?php echo esc_html($additionalHeader); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div class="content">
						<?php $debug = json_decode($log['backtrace_segment']); ?>
						<ul>
							<li><?php esc_html_e('Triggered from:', 'mailtree'); ?><strong><?php echo wp_kses_post($debug->file); ?></strong></li>
							<li><?php esc_html_e('On line:', 'mailtree'); ?> <strong><?php echo wp_kses_post($debug->line); ?></strong></li>
							<li><?php esc_html_e('Sent at:', 'mailtree'); ?> <strong><?php echo wp_kses_post(gmdate(GeneralHelper::$humanReadableDateFormat, $log['timestamp'])); ?> (<?php echo wp_kses_post($log['timestamp']); ?>)</strong></li>
						</ul>

						<?php if (!empty($log['error'])) : ?>
							<h3 class="subheading"><?php esc_html_e('Errors:', 'mailtree'); ?></h3>
							<hr/>
							<ul>
								<li><?php echo wp_kses_post($log['error']); ?></li>
							</ul>
						<?php endif; ?>

						<?php if (isset($log['is_html']) && true == $log['is_html']) : ?>
							<strong class="subheading"><?php esc_html_e('HTML Code', 'mailtree'); ?></strong>
							<pre><code><?php echo wp_kses_post(htmlspecialchars($log['message'])); ?></code></pre>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button-primary dismiss-modal">
				<?php
				esc_html_e('Close',
						'mailtree');
				?>
						</button>
			</div>
		</div>
		<div class="backdrop dismiss-modal"></div>
	</div>
<?php endif; ?>
