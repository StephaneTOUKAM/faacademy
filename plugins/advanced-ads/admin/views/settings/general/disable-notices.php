<input id="advanced-ads-disabled-notices" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[disable-notices]" <?php checked( $checked, 1 ); ?>>
<p class="description">
<?php
	esc_html_e( 'Disable Ad Health in frontend and backend, warnings and internal notices like tips, tutorials, email newsletters and update notices.', 'advanced-ads' );
?>
 <a href="<?php echo esc_url( ADVADS_URL ); ?>'manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-ad-health" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
</p>
