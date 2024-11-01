<?php
/**
 * @var int $product_id
 * @var bool $is_published_on_site
 * @var bool $is_api_ready
 * @var VkCommerce_Product $vk_product
 * @var array $no_publish_reasons
 */

$can_be_published = $is_published_on_site && $is_api_ready && empty( $no_publish_reasons );

?>
<div class="vkcommerce-meta-box">
    <div class="meta-box-body">
		<?php if ( ! $is_api_ready ): ?>
            <div class="vkcommerce-red">
				<?php echo sprintf(
					__( 'VK API integration is not configured properly. Please, visit the <a href="%s">API Integration</a> page and follow the instructions to complete the settings.', 'vkcommerce' ),
					esc_url( VkCommerce_Admin_Settings_Page::get_url() )
				); ?>
            </div>
		<?php endif; ?>

		<?php if ( $vk_product ): ?>
			<?php if ( $vk_product->is_synced() ): ?>
				<?php _e( 'Product is published VKontakte.', 'vkcommerce' ); ?>
			<?php elseif ( $vk_product->is_outdated() ): ?>
				<?php _e( 'Product is published VKontakte, but outdated.', 'vkcommerce' ); ?>
			<?php elseif ( $vk_product->is_queued_to_publish() ): ?>
				<?php echo $vk_product->is_exported()
                    ? __( 'Product is queued to update VKontakte.', 'vkcommerce' )
                    : __( 'Product is queued to publish VKontakte.', 'vkcommerce' );
                ?>
			<?php elseif ( $vk_product->is_queued_to_unpublish() ): ?>
				<?php _e( 'Product is queued to unpublish VKontakte.', 'vkcommerce' ); ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $is_published_on_site ): ?>
			<?php if ( ! empty( $no_publish_reasons ) ): ?>
                <div class="vkcommerce-warning">
					<?php echo $vk_product && $vk_product->is_exported()
						? __( 'Product can not be updated VKontakte due next reasons:', 'vkcommerce' )
						: __( 'Product can not be published VKontakte due next reasons:', 'vkcommerce' );
					?>
                </div>
                <ol>
					<?php foreach ( $no_publish_reasons as $reason ): ?>
                        <li><?php echo esc_html( $reason ); ?></li>
					<?php endforeach; ?>
                </ol>
			<?php elseif ( $can_be_published && ( ! $vk_product || ! $vk_product->get_status() ) ): ?>
				<?php _e( 'Product is ready to be published VKontakte.', 'vkcommerce' ); ?>
			<?php endif; ?>
		<?php elseif ( $is_api_ready ): ?>
			<?php echo $vk_product && $vk_product->is_exported()
				? __( 'Product can not be updated VKontakte until it is published on the site.', 'vkcommerce' )
				: __( 'Product can not be published VKontakte until it is published on the site.', 'vkcommerce' );
			?>
		<?php endif; ?>
    </div>
    <div class="meta-box-actions">
		<?php if ( $vk_product && $vk_product->is_exported() ): ?>
            <div id="vkcommerce-delete-action">
                <a href="#"
                   id="vkcommerce-delete"
                   class="vkcommerce-action-lnk vkcommerce-red"
                   data-product-id="<?php echo esc_attr( $product_id ); ?>"
				   <?php if ( $vk_product->get_status() ): ?>data-product-status="<?php echo esc_attr( $vk_product->get_status() ); ?>"<?php endif; ?>
                >
					<?php _e( 'Unpublish', 'vkcommerce' ); ?>
                </a>
            </div>
		<?php endif; ?>
        <div id="vkcommerce-publish-action">
            <span id="vkcommerce-publish-box-spinner" class="spinner"></span>
            <button type="button"
                    id="vkcommerce-publish"
                    class="button button-primary button-large vkcommerce-action-btn"
                    data-product-id="<?php echo esc_attr( $product_id ); ?>"
					<?php if ( $vk_product && $vk_product->get_status() ): ?>data-product-status="<?php echo esc_attr( $vk_product->get_status() ); ?>"<?php endif; ?>
					<?php if ( ! $can_be_published ): ?>disabled="disabled"<?php endif; ?>
            >
				<?php echo $vk_product && $vk_product->is_exported() ? __( 'Update', 'vkcommerce' ) : __( 'Publish', 'vkcommerce' ); ?>
            </button>
        </div>
        <div class="clear"></div>
    </div>
</div>
