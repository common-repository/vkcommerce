<?php
/**
 * @var string $type
 * @var string $name
 * @var string $label
 * @var string $id
 * @var string $class
 * @var string $css
 * @var string $description
 * @var string $placeholder
 * @var mixed $value
 * @var array $attributes
 * @var string $visibility_class
 */
?>

<fieldset class="<?php echo esc_attr( $visibility_class ); ?>">
	<label<?php if ( $id ): ?> for="<?php echo esc_attr( $id ); ?>"<?php endif; ?>>
		<input type="checkbox"
			   name="<?php echo esc_attr( $name ); ?>"
		       <?php if ( $id ): ?>id="<?php echo esc_attr( $id ); ?>"<?php endif; ?>
		       <?php if ( $class ): ?>class="<?php echo esc_attr( $class ); ?>"<?php endif; ?>
			   value="1"
			<?php checked( $value ); ?>
			<?php foreach ( $attributes as $attribute => $attribute_value ): ?>
				<?php echo sprintf( '%s="%s"', esc_attr( $attribute ), esc_attr( $attribute_value ) ); ?>
			<?php endforeach; ?>
		/>
		<?php if ( $description ): ?>
			<p class="description">
				<?php echo wp_kses_post( $description ); ?>
			</p>
		<?php endif; ?>
	</label>
</fieldset>
