<?php

use MailTree\GeneralHelper;

?>

<p class="leave-a-review">
	<a href="<?php echo esc_url_raw(GeneralHelper::$supportLink); ?>" target="_blank">
		<?php esc_html_e('Need help? Get support.', 'mailtree'); ?>
	</a>
	<span class="footer-dash">&mdash;</span>
	<a href="<?php echo esc_url_raw(GeneralHelper::$reviewLink); ?>" target="_blank">
		<?php esc_html_e('Useful? Please rate this', 'mailtree'); ?>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
		<span class="dashicons dashicons-star-filled"></span>
	</a>
	<span class="footer-dash">&mdash;</span>
	<a href="<?php // echo esc_url_raw(GeneralHelper::$logpressLink); ?>" target="_blank">
		<?php // esc_html_e('Save logs to external database? Visit Logpress.', 'mailtree'); ?>
	</a>
</p>
