<?php
/**
 * Render group hints.
 *
 * @var string[] $hints Group hints (escaped strings).
 */
?>
<?php foreach ( $hints as $hint ) : ?>
	<p class="advads-notice-inline advads-error">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the output is already escaped
		echo $hint;
		?>
	</p>
	<?php
endforeach;
