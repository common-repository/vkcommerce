<?php
/**
 * Plugin Name: VkCommerce
 * Description: The plugin exports photos and descriptions of products from your online store to the storefront in a VKontakte group.
 * Version: 1.1.1
 * Requires at least: 5.1
 * Requires PHP: 7.0
 * Author: Yaroslav Bogutsky
 * License: GPL v2 or later
 * Text Domain: vkcommerce
 * Domain Path: /i18n/
 *
 * @package VkCommerce
 */

if ( ! defined( 'VKCOMMERCE_PLUGIN_FILE' ) ) {
	define( 'VKCOMMERCE_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'VkCommerce', false ) ) {
	require_once __DIR__ . '/includes/class-vkcommerce.php';
}

VkCommerce::instance();
