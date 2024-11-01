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
 */

if ( empty( $options ) || ! is_array( $options ) ) {
	$options = array();
}
?>

<select name="<?php echo esc_attr( $name ); ?><?php echo ( 'multiselect' === $type ) ? '[]' : ''; ?>"
        <?php if ( $id ): ?>id="<?php echo esc_attr( $id ); ?>"<?php endif; ?>
        <?php if ( $css ): ?>style="<?php echo esc_attr( $css ); ?>"<?php endif; ?>
        <?php if ( $class ): ?>class="<?php echo esc_attr( $class ); ?>"<?php endif; ?>
	<?php foreach ( $attributes as $attribute => $attribute_value ): ?>
		<?php echo sprintf( '%s="%s"', esc_attr( $attribute ), esc_attr( $attribute_value ) ); ?>
	<?php endforeach; ?>
>
	<?php foreach ( $options as $key => $val ): ?>
		<option value="<?php echo esc_attr( $key ); ?>"
			<?php
			if ( is_array( $value ) ) {
				selected( in_array( (string) $key, $value, true ), true );
			} else {
				selected( $value, (string) $key );
			}
			?>
		><?php echo esc_html( $val ); ?></option>
	<?php endforeach; ?>
</select>
<?php if ( $description ): ?>
	<p class="description">
		<?php echo wp_kses_post( $description ); ?>
	</p>
<?php endif; ?>
