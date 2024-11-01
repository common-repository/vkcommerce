<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 * @var array $sections
 */
?>
<h2><?php _e( 'Basic integration settings', 'vkcommerce' ); ?></h2>
<p>
	<?php _e( 'To interact with the Vkontakte API, you need an <strong>application ID</strong>, a <strong>secret key</strong>, an <strong>access token</strong> and a <strong>group ID</strong>.', 'vkcommerce' ); ?>
	<?php _e( 'In fact, only an <strong>access token</strong> is required to send api requests, but an <strong>application ID</strong> and a <strong>secret key</strong> are needed to obtain token.', 'vkcommerce' ); ?>
	<?php _e( 'The <strong>group ID</strong> defines the group in which the products will be published.', 'vkcommerce' ); ?>
</p>

<p>
	<?php _e( '<strong>Application ID</strong> - ID of VKontakte application. Since the application was created, this value never changes.', 'vkcommerce' ); ?>
</p>

<p>
	<?php _e( '<strong>Secret key</strong> - secret key of VKontakte application. The secret key value can be regenerated anytime on VKontakte side. Do not forget to update the secret key value if you have regenerated it.', 'vkcommerce' ); ?>
</p>

<p>
	<?php _e( '<strong>Access token</strong> - a special parameter used to make api requests to VKontakte platform. Unlike the options above, you do not need to copy anything from the VKontakte application settings page.', 'vkcommerce' ); ?>
	<?php _e( 'To obtain the access token, you need to follow the link "<strong>Get access token</strong>". The application ID and secret key must already be specified.', 'vkcommerce' ); ?>
</p>
<p>
	<?php _e( '<strong>Group ID</strong> - ID of VKontakte group, where products will be published. The group should be active and have a created shop.', 'vkcommerce' ); ?>
	<?php _e( 'As with an access token, you do not need to copy anything. Once all the parameters above are specified, the plugin will load all the suitable groups and give you a choice.', 'vkcommerce' ); ?>
</p>

<h2><?php _e( 'Configuration process', 'vkcommerce' ); ?></h2>
<ol>
	<li>
		<?php _e( 'Create a new (or edit existing) VKontakte application with next parameters:', 'vkcommerce' ); ?><br/><br/>
		<ul>
			<li><?php echo sprintf( __( 'Website address: <strong>%s</strong>', 'vkcommerce' ), esc_url( site_url() ) ); ?></li>
			<li><?php echo sprintf( __( 'Base domain: <strong>%s</strong>', 'vkcommerce' ), parse_url( esc_url( site_url() ), PHP_URL_HOST ) ); ?></li>
		</ul>
		<br/>
	</li>
	<li><?php _e( 'Enter the <strong>application ID</strong> and <strong>secret key</strong> values and save settings. After saving these settings, a link "<strong>Get access token</strong>" will appear.', 'vkcommerce' ); ?></li>
	<li>
		<?php _e( 'Follow the link "<strong>Get access token</strong>". The first time you try, you will be prompted to grant the application the necessary permissions. Once you do this, an <strong>access token</strong> will be obtained.', 'vkcommerce' ); ?>
		<?php _e( 'If the token has already been obtained, a link "<strong>Refresh access token</strong>" will appear. Use it in case of problems with the API.', 'vkcommerce' ); ?>
		<?php _e( 'You can use a link "<strong>Verify status</strong>" anytime to check if API integration works.', 'vkcommerce' ); ?>
	</li>
	<li>
		<?php _e( 'Finally, select a VKontakte group and save settings. If you do not see any group, create a new one. The group is suitable if it has a store and you are the administrator of the group.', 'vkcommerce' ); ?>
	</li>
</ol>