<?php use MailTree\GeneralHelper; ?>

<div id="new-message" class="modal">
	<div class="modal-content">
		<form class="form-horizontal" action="?page=<?php echo esc_html_e(GeneralHelper::$adminPageSlug); ?>&action=new_mail"
			  method="POST">
			<div class="modal-body">
				<div class="content-container">
					<div class="content -active">
						<div>
							<h2><?php esc_html_e('Headers', 'mailtree'); ?></h2>
							<hr/>

							<div class="cloneable">
								<div class="field-block">
									<a href="#" class="add-field">
										<span class="dashicons dashicons-plus-alt -icon"></span>
									</a>

									<a href="#" class="remove-field -disabled">
										<span class="dashicons dashicons-dismiss -icon"></span>
									</a>

									<select name="header_keys[]" class="field -select">
										<option value="to"><?php esc_html_e('To', 'mailtree'); ?></option>
										<option value="cc"><?php esc_html_e('Cc', 'mailtree'); ?></option>
										<option value="bcc"><?php esc_html_e('Bcc', 'mailtree'); ?></option>
										<option value="from"><?php esc_html_e('From', 'mailtree'); ?></option>
										<option value="custom"><?php esc_html_e('Custom', 'mailtree'); ?></option>
									</select>

									<input name="header_values[]" type="text" class="field -input"/>
								</div>
							</div>

							<label class="is-html-email"><input type="checkbox" value="Content-Type: text/html"
																name="header_keys[]"/>
								<?php esc_html_e('Is HTML email?', 'mailtree'); ?> </label>
						</div>
						<div>
							<h2><?php esc_html_e('Subject', 'mailtree'); ?></h2>
							<hr/>

							<input name="subject" type="text" class="field -input"/>
						</div>
						<div>
							<h2><?php esc_html_e('Attachments', 'mailtree'); ?></h2>
							<hr/>

							<div class="attachments-container">
								<div class="attachment-clones">
									<span class="attachment-item -original">
										<span class="dashicons dashicons-dismiss remove"></span>
										<input type="hidden" name="attachment_ids[]" value="" class="attachment-input"/>
									</span>
								</div>

								<div class="attachment-button-container">
									<a href="#" class="button-primary" id="add_attachments">
										<?php esc_html_e('Add Attachments', 'mailtree'); ?>
									</a>
								</div>
							</div>
						</div>
						<div>
							<h2><?php esc_html_e('Message', 'mailtree'); ?></h2>
							<hr />

							<?php wp_editor(__('My Message', 'mailtree'), 'message'); ?>
						</div>
					</div>
				</div>
			</div>

			<?php wp_nonce_field('new_mail'); ?>

			<div class="modal-footer">
				<button type="submit" class="button-primary">
					<?php esc_html_e('Send Message', 'mailtree'); ?>
				</button>
				<button type="button" class="button-secondary dismiss-modal">
					<?php esc_html_e('Close', 'mailtree'); ?>
				</button>
			</div>
		</form>
	</div>
	<div class="backdrop dismiss-modal"></div>
</div>
