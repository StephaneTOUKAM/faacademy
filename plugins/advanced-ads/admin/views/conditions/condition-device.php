<?php
/**
 * Template for the Device visitor condition
 *
 * @var string $name form name attribute.
 * @var string $operator
 * @var array $type_options array with additional information.
 */
?>
<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $options['type'] ); ?>"/>
<select name="<?php echo esc_attr( $name ); ?>[operator]">
	<option
		value="is" <?php selected( 'is', $operator ); ?>><?php esc_html_e( 'Mobile (including tablets)', 'advanced-ads' ); ?></option>
	<option
		value="is_not" <?php selected( 'is_not', $operator ); ?>><?php esc_html_e( 'Desktop', 'advanced-ads' ); ?></option>
</select>
<?php
printf(
	'<p class="description">%1$s <a href="%2$s" class="advads-manual-link" target="_blank">%3$s</a></p>',
	esc_html( $type_options[ $options['type'] ]['description'] ),
	esc_url( $type_options[ $options['type'] ]['helplink'] ),
	esc_html__( 'Manual', 'advanced-ads' )
);
