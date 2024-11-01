<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 */
?>
<h3><?php _e( 'VKontakte categories', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'The VKontakte catalog supports two-level categories. At the same time, WordPress allows to create more nested categories.', 'vkcommerce' ); ?>
	<?php _e( 'Every WordPress category can be associated with a VKontakte category (this can be done in the category settings).', 'vkcommerce' ); ?>
</p>

<h3><?php _e( 'Publishing in VKontakte category', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'The category is required to place a product in the VKontakte catalog, and a product can only be placed in one VKontakte category, so the plugin must decide which category to use.', 'vkcommerce' ); ?>
	<?php _e( 'To correctly place a product in the VKontakte catalog, the plugin searches for the WordPress category (associated with the VKontakte category) with the highest nesting.', 'vkcommerce' ); ?>
	<?php _e( 'If a product is placed in several categories of the same nesting, the first one will be used.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( 'The VKontakte category can be specified individually in the product settings. In this case, WordPress categories do not matter.', 'vkcommerce' ); ?>
</p>