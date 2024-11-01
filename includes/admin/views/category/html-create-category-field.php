<?php
/**
 * @var boolean $is_api_ready
 * @var string $warning_message
 * @var array $category_field
 */
?>
<div id="vkcommerce-category-wrapper" class="form-field">
	<label for="<?php echo esc_attr( $category_field['id'] ); ?>">
		<?php echo esc_html( $category_field['label'] ); ?>
	</label>
	<?php if ( $is_api_ready ): ?>
		<?php if ( ! empty( $warning_message ) ): ?>
			<div class="vkcommerce-warning"><?php echo esc_html( $warning_message ); ?></div>
		<?php endif; ?>

		<?php VkCommerce_Admin_Form::output_field( $category_field ); ?>
	<?php else: ?>
		<p>
			<?php echo sprintf(
				__( 'VK API integration is not configured properly. Please, visit the <a href="%s">API Integration</a> page and follow the instructions to complete the settings.', 'vkcommerce' ),
				esc_url( VkCommerce_Admin_Settings_Page::get_url() )
			); ?>
		</p>
	<?php endif; ?>
</div>
