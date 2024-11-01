<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 */
?>
<h2><?php _e( 'Products publishing settings', 'vkcommerce' ); ?></h2>
<p>
	<?php _e( '<b>Auto-publishing</b> - activates the automatic publishing of the product VKontakte each time the product data is saved on the site.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( '<b>Product description</b> - product description template for VKontakte publishing.', 'vkcommerce' ); ?>
	<?php _e( 'The value is optional, if the value is empty, then the product description on the site will be used.', 'vkcommerce' ); ?>
	<?php _e( 'Please note that only plain text can be used in the description, html is not supported.', 'vkcommerce' ); ?>
	<br/>
	<?php _e( 'The following data is available for use:', 'vkcommerce' ); ?>
</p>
<ul>
	<li>
		<code>[description]</code> - <?php _e( 'product description', 'vkcommerce' ); ?>
	</li>
	<li>
		<code>[short_description]</code> - <?php _e( 'product short description', 'vkcommerce' ); ?>
	</li>
	<li>
		<code>[url]</code> - <?php _e( 'product page url', 'vkcommerce' ); ?>
	</li>
</ul>
