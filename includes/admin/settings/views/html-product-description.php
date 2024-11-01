<?php
/**
 * @var array $field
 * @var string $current_description_template
 */
?>

<?php VkCommerce_Admin_Form::output_field( $field ); ?>
<p class="description">
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

