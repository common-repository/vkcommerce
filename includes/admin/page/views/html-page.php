<?php
/**
 * @var VkCommerce_Admin_Page_Tab[] $tabs
 * @var VkCommerce_Admin_Page_Tab $current_tab
 * @var string $current_section_id
 */

$sections = $current_tab->get_sections();
?>

<div class="wrap vkcommerce">
	<?php if ( count( $tabs ) > 1 ): ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $tab ): ?>
				<a href="<?php echo esc_url( $tab->get_url() ); ?>"
				   class="nav-tab<?php echo ( $current_tab->get_id() === $tab->get_id() ) ? ' nav-tab-active' : ''; ?>"
				>
					<?php echo esc_html( $tab->get_label() ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( count( $sections ) > 1 ): ?>
		<?php $sections_ids = array_keys( $sections ); ?>
		<ul class="subsubsub nav-section-wrapper">
			<?php foreach ( $sections as $id => $label ): ?>
				<li>
					<a href="<?php echo esc_url( $current_tab->get_url( array( 'section' => $id ) ) ); ?>"
					   class="nav-section <?php echo ( $current_section_id === $id ) ? 'current' : ''; ?>"
					>
						<?php echo esc_html( $label ); ?>
					</a>
					<?php echo ( end( $sections_ids ) === $id ) ? '' : '|'; ?>
				</li>
			<?php endforeach; ?>
		</ul><br class="clear"/>
	<?php endif; ?>

	<?php settings_errors(); ?>

	<?php if ( ! VkCommerce::instance()->is_woocommerce_activated() ): ?>
		<?php require __DIR__ . '/html-wc-dependency-notice.php'; ?>
	<?php endif; ?>

	<?php $current_tab->output( $current_section_id ); ?>
</div>
