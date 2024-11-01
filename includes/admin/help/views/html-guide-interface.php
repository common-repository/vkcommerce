<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 */
?>
<h3><?php _e( 'Product categories management interface', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'The categories table has a column <b>"VKontakte category"</b>, using which you can easily identify categories that are not yet associated.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( 'On the product category creating/editing form there is a field <b>"VKontakte category"</b>. By filling in this field, you can associate a category on the site with a VKontakte category.', 'vkcommerce' ); ?>
</p>

<h3><?php _e( 'Products management interface', 'vkcommerce' ); ?></h3>
<p>
	<?php _e( 'The products table has a column <b>"VKontakte"</b>, using which you can easily identify product publishing status.', 'vkcommerce' ); ?>
	<?php _e( 'The table also has a filter by VKontakte publishing status.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( 'On the product creating/editing form there are a few additional elements: <b>"VKontakte publishing" box</b> and <b>"VKontakte" product data tab</b>.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( '<b>"VKontakte publishing" box</b> is a box in right sidebar. The box contains information about status of product publication, or about reasons why the product can not be published.', 'vkcommerce' ); ?>
	<?php _e( 'It also contains a button to manually publish the product and a link to unpublish if the product is already published.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( '<b>"VKontakte" product data tab</b> is a tab in a product data section.', 'vkcommerce' ); ?>
	<?php _e( 'This tab contains custom product settings. Each setting field is disabled and marked "use general settings" by default.', 'vkcommerce' ); ?>
	<?php _e( 'You can uncheck and set a custom setting value for the product.', 'vkcommerce' ); ?>
</p>
