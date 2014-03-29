<div class="anh_message <?php esc_attr_e( $class ); ?>">
	<?php foreach ( $this->notices[$type] as $messageData ) : ?>
		<?php if ( $messageData['mode'] == 'user' || $this->debugMode ) : ?>
			<p><?php esc_html_e( $messageData['message'] ); ?></p>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
