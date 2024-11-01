<?php
/**
 * @var bool $has_access_token
 * @var bool $get_token_link
 * @var bool $auth_link
 * @var bool $user_link
 * @var bool $verify_link
 */
?>

<?php if ( $has_access_token ): ?>
	<div>
		<?php echo sprintf( __( 'The token is obtained on behalf of <a target="_blank" href="%s">this user</a>.', 'vkcommerce' ), esc_url( $user_link ) ); ?>
		<a href="<?php echo esc_url( $get_token_link ); ?>"><?php _e( 'Refresh access token', 'vkcommerce' ); ?></a>
	</div>
	<div>
		<?php echo sprintf( __( '<a href="%s">Verify status</a> of integration.', 'vkcommerce' ), esc_url( $verify_link ) ); ?>
	</div>
<?php else: ?>
	<a href="<?php echo esc_url( $get_token_link ); ?>"><?php _e( 'Get access token', 'vkcommerce' ); ?></a>
	<p class="description">
		<?php _e( 'The access token has not yet been obtained. Click on the link above to get it.', 'vkcommerce' ); ?>
	</p>
<?php endif; ?>
