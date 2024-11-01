<?php
/**
 * @var string $groups_error
 * @var array $groups
 * @var array $field
 * @var string $current_group_id
 */
?>

<?php if ( ! empty( $groups_error ) ): ?>
	<?php _e( 'An error occurred while getting the list of groups.', 'vkcommerce' ); ?>
	<?php if ( ! empty( $current_group_id ) ): ?>
		<?php echo sprintf( __( 'But you have configured group ID <strong>%s</strong>.', 'vkcommerce' ), esc_html( $current_group_id ) ); ?>
	<?php endif; ?>
	<p class="description">
		<?php _e( 'If the error occurs again and again, then try to verify the status of the integration.', 'vkcommerce' ); ?>
	</p>
<?php elseif ( empty( $groups ) ): ?>
	<?php _e( "You don't have any suitable group created.", 'vkcommerce' ); ?>
	<?php if ( ! empty( $current_group_id ) ): ?>
		<?php echo sprintf( __( 'But you have configured group ID <strong>%s</strong>.', 'vkcommerce' ), esc_html( $current_group_id ) ); ?>
	<?php endif; ?>
	<p class="description">
		<?php _e( 'The group must be active and have a store.', 'vkcommerce' ); ?>
	</p>
<?php else: ?>
	<?php VkCommerce_Admin_Form::output_field( $field ); ?>
<?php endif; ?>
