<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 */
?>
<h3><?php _e( 'Auto-publishing', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'Auto-publishing allows you to publish products VKontakte every time you save a product on the site.', 'vkcommerce' ); ?>
	<?php _e( 'A product publishing can be disabled/enabled individually in the product settings.', 'vkcommerce' ); ?>
</p>

<h3><?php _e( 'Manual publishing', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'Manual publishing can be done on a product edit page at any time.', 'vkcommerce' ); ?>
	<?php _e( 'Please note that if you have made any changes to the product, you must first save those changes.', 'vkcommerce' ); ?>
</p>

<h3><?php _e( 'Manual unpublishing', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'A product can be removed VKontakte at any time manually.', 'vkcommerce' ); ?>
	<?php _e( 'Please note that if you have enabled auto-publishing the product will be published VKontakte again once you save product on the site.', 'vkcommerce' ); ?>
	<?php _e( 'If you do not want to publish a particular product you can disable auto-publishing for it individually in the product settings.', 'vkcommerce' ); ?>
</p>

<h3><?php _e( 'Product type limitations', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'VKontakte currently supports variable product using particular properties, like size or color, but the VKontakte API does not provide control over these properties. Therefore, at the moment, the plugin only supports "simple" and "external" products.', 'vkcommerce' ); ?>
	<?php _e( 'We are considering options for publishing variable products and this will be implemented in future releases.', 'vkcommerce' ); ?>
</p>
