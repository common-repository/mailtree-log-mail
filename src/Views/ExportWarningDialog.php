<?php use MailTree\GeneralHelper; ?>

<div id="export-warning-dialog" class="modal">
	<div class="modal-content">
<form class="form-horizontal" action="?page=<?php echo wp_kses_post(GeneralHelper::$adminPageSlug); ?>&action=export-all"
			  method="POST">
			<div class="modal-body">
				<div class="content-container">
					<div class="content -active">
						<div>
							<h2><?php esc_html_e('Warning', 'mailtree'); ?></h2>
							<hr/>
							<p>
								<?php
								/* translators: This warns of a possible timeout when exporting many emails */
								printf(wp_kses_post(__('You are trying to export <strong>%1$s</strong> messages when the recommended limit is no more than <strong>%2$s</strong>, this can cause the server to timeout before the export is complete, we recommend reducing the amount of messages exported, or exporting them in batches.',
									'mailtree')),
									wp_kses_post($logs->totalItems),
									wp_kses_post(GeneralHelper::$logLimitBeforeWarning)
								);
								?>
							</p>

							<table class="form-table">
								<tbody>
								<tr>
									<th scope="row">
										<label>
											<?php esc_html_e('Number of logs to export', 'mailtree'); ?>
										</label>
									</th>s
									<td>
										<input data-update-format name="posts_per_page" type="text"
											   value="<?php echo wp_kses_post(GeneralHelper::$logLimitBeforeWarning); ?>"
											   class="field -input"/>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label>
											<?php esc_html_e('Batch number', 'mailtree'); ?>
										</label>
									</th>
									<td>
										<input data-update-format name="paged" type="text" value="1"
											   class="field -input"/>
										<p class="description" data-text-format="
										<?php
										/* translators: This shows the amount of messages that will be exported. */
										esc_html_e('This will export messages <strong>%1$s-%2$s</strong>', 'mailtree');
										?>
											   "></p>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<?php wp_nonce_field('bulk-logs'); ?>

			<div class="modal-footer">
				<button type="submit" class="button-primary">
					<?php esc_html_e('Export', 'mailtree'); ?>
				</button>
				<button type="button" class="button-secondary dismiss-modal">
					<?php esc_html_e('Cancel', 'mailtree'); ?>
				</button>
			</div>
		</form>
	</div>
	<div class="backdrop dismiss-modal"></div>
</div>
