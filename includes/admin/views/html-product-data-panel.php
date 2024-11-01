<?php
/**
 * @var WC_Product_Simple $product_object
 * @var bool $is_api_ready
 */
?>
<div id="vkontakte_product_data" class="panel woocommerce_options_panel hidden">
	<?php if ( $is_api_ready ): ?>
		<?php
		/**
		 * @var array $auto_publishing_field
		 * @var array $category_field
		 * @var string $categories_warning_message
		 */
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $auto_publishing_field['id'] ); ?>">
				<?php _e( 'Auto-publishing', 'vkcommerce' ); ?>
			</label>
			<?php VkCommerce_Admin_Form::output_field( $auto_publishing_field ); ?>
			<span class="general-settings">
			<input type="checkbox"
				   class="vkcommerce-use-general-settings"
				   data-for="<?php echo esc_attr( $auto_publishing_field['id'] ); ?>"
				   <?php if ( ! empty( $auto_publishing_field['is_general_value'] ) ): ?> checked="checked"<?php endif; ?>
			>
			<?php _e( 'use general settings', 'vkcommerce' ); ?>
			</span>
		</p>
		<?php if ( ! empty( $categories_warning_message ) ): ?>
			<p class="vkcommerce-red"><?php echo esc_html( $categories_warning_message ); ?></p>
		<?php endif; ?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $category_field['id'] ); ?>">
				<?php _e( 'VKontakte category', 'vkcommerce' ); ?>
			</label>
			<?php VkCommerce_Admin_Form::output_field( $category_field ); ?>
			<span class="general-settings">
			<input type="checkbox"
				   class="vkcommerce-use-general-settings"
				   data-for="<?php echo esc_attr( $category_field['id'] ); ?>"
			       <?php if ( ! empty( $category_field['is_general_value'] ) ): ?>checked="checked"<?php endif; ?>
			>
			<?php _e( 'use general settings', 'vkcommerce' ); ?>
			</span>
		</p>
		<p class="note">
			<?php _e( 'Please note that these settings are applied after the product is saved.', 'vkcommerce' ); ?>
			<?php if ( ! VkCommerce_Settings::is_products_auto_publish_enabled() ): ?>
				<?php _e( 'Manual publishing will not apply new values of these settings.', 'vkcommerce' ); ?>
			<?php endif; ?>
		</p>
	<?php else: ?>
		<p>
			<?php echo sprintf(
				__( 'VK API integration is not configured properly. Please, visit the <a href="%s">API Integration</a> page and follow the instructions to complete the settings.', 'vkcommerce' ),
				esc_url( VkCommerce_Admin_Settings_Page::get_url() )
			); ?>
		</p>
	<?php endif; ?>
</div>
