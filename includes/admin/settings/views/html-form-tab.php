<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 * @var string $settings_group
 */
?>
<form action="options.php" method="post">
	<?php
	settings_fields( $settings_group );
	do_settings_sections( $settings_group );
	submit_button( __( 'Save Settings', 'vkcommerce' ) );
	?>
</form>

