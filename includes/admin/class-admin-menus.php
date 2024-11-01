<?php

class VkCommerce_Admin_Menus {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );
		add_action( 'admin_menu', array( $this, 'help_menu' ) );
	}

	public function admin_menu() {
		$vkcommerce_icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHg9IjBweCIgeT0iMHB4Igp3aWR0aD0iMjQiIGhlaWdodD0iMjQiCnZpZXdCb3g9IjAgMCAyNCAyNCIKc3R5bGU9IiBmaWxsOiMwMDAwMDA7Ij48cGF0aCBkPSJNIDIxLjgwMDc4MSAwIEwgMi4xOTkyMTkgMCBDIDAuOTg0Mzc1IDAgMCAwLjk4NDM3NSAwIDIuMTk5MjE5IEwgMCAyMS44MDA3ODEgQyAwIDIzLjAxNTYyNSAwLjk4NDM3NSAyNCAyLjE5OTIxOSAyNCBMIDIxLjgwMDc4MSAyNCBDIDIzLjAxNTYyNSAyNCAyNCAyMy4wMTU2MjUgMjQgMjEuODAwNzgxIEwgMjQgMi4xOTkyMTkgQyAyNCAwLjk4NDM3NSAyMy4wMTU2MjUgMCAyMS44MDA3ODEgMCBaIE0gMTkuMDUwNzgxIDE0LjA4OTg0NCBDIDIwLjQ5MjE4OCAxNS40Mjk2ODggMjAuNzg5MDYzIDE2LjA3ODEyNSAyMC44Mzk4NDQgMTYuMTYwMTU2IEMgMjEuNDM3NSAxNy4xNDg0MzggMjAuMTc5Njg4IDE3LjIyNjU2MyAyMC4xNzk2ODggMTcuMjI2NTYzIEwgMTcuNzczNDM4IDE3LjI2MTcxOSBDIDE3Ljc3MzQzOCAxNy4yNjE3MTkgMTcuMjU3ODEzIDE3LjM2MzI4MSAxNi41NzgxMjUgMTYuODk0NTMxIEMgMTUuNjc5Njg4IDE2LjI3NzM0NCAxNC44MjgxMjUgMTQuNjcxODc1IDE0LjE3MTg3NSAxNC44ODI4MTMgQyAxMy41IDE1LjA5Mzc1IDEzLjUxOTUzMSAxNi41MzkwNjMgMTMuNTE5NTMxIDE2LjUzOTA2MyBDIDEzLjUxOTUzMSAxNi41MzkwNjMgMTMuNTI3MzQ0IDE2Ljc5Mjk2OSAxMy4zNzUgMTYuOTYwOTM4IEMgMTMuMjA3MDMxIDE3LjEzNjcxOSAxMi44ODI4MTMgMTcuMTE3MTg4IDEyLjg4MjgxMyAxNy4xMTcxODggTCAxMS44MDg1OTQgMTcuMTE3MTg4IEMgMTEuODA4NTk0IDE3LjExNzE4OCA5LjQzMzU5NCAxNy4zMTY0MDYgNy4zNDM3NSAxNS4xNDA2MjUgQyA1LjA2MjUgMTIuNzY1NjI1IDMuMDUwNzgxIDguMDc4MTI1IDMuMDUwNzgxIDguMDc4MTI1IEMgMy4wNTA3ODEgOC4wNzgxMjUgMi45MzM1OTQgNy43ODUxNTYgMy4wNTg1OTQgNy42MzY3MTkgQyAzLjE5OTIxOSA3LjQ2ODc1IDMuNTg1OTM4IDcuNDY0ODQ0IDMuNTg1OTM4IDcuNDY0ODQ0IEwgNi4xNTYyNSA3LjQ0OTIxOSBDIDYuMTU2MjUgNy40NDkyMTkgNi4zOTg0MzggNy40OTIxODggNi41NzQyMTkgNy42MTcxODggQyA2LjcxODc1IDcuNzIyNjU2IDYuNzk2ODc1IDcuOTIxODc1IDYuNzk2ODc1IDcuOTIxODc1IEMgNi43OTY4NzUgNy45MjE4NzUgNy4yMTQ4NDQgOC45NzI2NTYgNy43NjE3MTkgOS45MjU3ODEgQyA4LjgzOTg0NCAxMS43ODEyNSA5LjMzOTg0NCAxMi4xODc1IDkuNzAzMTI1IDExLjk4ODI4MSBDIDEwLjIzNDM3NSAxMS42OTUzMTMgMTAuMDc0MjE5IDkuMzYzMjgxIDEwLjA3NDIxOSA5LjM2MzI4MSBDIDEwLjA3NDIxOSA5LjM2MzI4MSAxMC4wODU5MzggOC41MTU2MjUgOS44MDg1OTQgOC4xMzY3MTkgQyA5LjU5Mzc1IDcuODQ3NjU2IDkuMTg3NSA3Ljc2MTcxOSA5LjAwNzgxMyA3LjczODI4MSBDIDguODYzMjgxIDcuNzE4NzUgOS4xMDE1NjMgNy4zODI4MTMgOS40MTAxNTYgNy4yMzA0NjkgQyA5Ljg3MTA5NCA3LjAwNzgxMyAxMC42ODc1IDYuOTkyMTg4IDExLjY1NjI1IDcuMDAzOTA2IEMgMTIuNDA2MjUgNy4wMTE3MTkgMTIuNjI1IDcuMDU4NTk0IDEyLjkxNzk2OSA3LjEyODkwNiBDIDEzLjgwNDY4OCA3LjM0Mzc1IDEzLjUwMzkwNiA4LjE3MTg3NSAxMy41MDM5MDYgMTAuMTUyMzQ0IEMgMTMuNTAzOTA2IDEwLjc4OTA2MyAxMy4zOTA2MjUgMTEuNjc5Njg4IDEzLjg0NzY1NiAxMS45NzY1NjMgQyAxNC4wNDY4NzUgMTIuMTA1NDY5IDE0LjUyNzM0NCAxMS45OTYwOTQgMTUuNzMwNDY5IDkuOTQ5MjE5IEMgMTYuMzA0Njg4IDguOTgwNDY5IDE2LjczMDQ2OSA3LjgzOTg0NCAxNi43MzA0NjkgNy44Mzk4NDQgQyAxNi43MzA0NjkgNy44Mzk4NDQgMTYuODI0MjE5IDcuNjM2NzE5IDE2Ljk3MjY1NiA3LjU1MDc4MSBDIDE3LjEyMTA5NCA3LjQ2MDkzOCAxNy4zMjAzMTMgNy40ODgyODEgMTcuMzIwMzEzIDcuNDg4MjgxIEwgMjAuMDI3MzQ0IDcuNDcyNjU2IEMgMjAuMDI3MzQ0IDcuNDcyNjU2IDIwLjgzOTg0NCA3LjM3NSAyMC45NzI2NTYgNy43NDIxODggQyAyMS4xMDkzNzUgOC4xMjg5MDYgMjAuNjY3OTY5IDkuMDI3MzQ0IDE5LjU2MjUgMTAuNSBDIDE3Ljc0NjA5NCAxMi45MTc5NjkgMTcuNTQ2ODc1IDEyLjY5MTQwNiAxOS4wNTA3ODEgMTQuMDg5ODQ0IFoiPjwvcGF0aD48L3N2Zz4=';

		add_menu_page(
			__( 'VKCommerce Settings', 'vkcommerce' ),
			__( 'VKCommerce', 'vkcommerce' ),
			'manage_options',
			VkCommerce_Admin_Settings_Page::get_slug(),
			array( 'VkCommerce_Admin_Settings_Page', 'output' ),
			$vkcommerce_icon,
			'58'
		);
	}

	public function settings_menu() {
		$settings_page = add_submenu_page(
			VkCommerce_Admin_Settings_Page::get_slug(),
			__( 'VkCommerce Settings', 'vkcommerce' ),
			__( 'Settings', 'vkcommerce' ),
			'manage_options',
			VkCommerce_Admin_Settings_Page::get_slug(),
			array( 'VkCommerce_Admin_Settings_Page', 'output' )
		);

		add_action( 'load-' . $settings_page, array( 'VkCommerce_Admin_Settings_Page', 'load' ) );
	}

	public function help_menu() {
		$help_page = add_submenu_page(
			VkCommerce_Admin_Settings_Page::get_slug(),
			__( 'VkCommerce Help', 'vkcommerce' ),
			__( 'Help', 'vkcommerce' ),
			'manage_options',
			VkCommerce_Admin_Help_Page::get_slug(),
			array( 'VkCommerce_Admin_Help_Page', 'output' )
		);

		add_action( 'load-' . $help_page, array( 'VkCommerce_Admin_Help_Page', 'load' ) );
	}
}

return new VkCommerce_Admin_Menus();
