
<?php

/**
 * The template for displaying loop excerpt.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Edumall
 * @since   1.0
 */

$post_title = get_the_title();
?>
<div class="post-excerpt">
	<?php if ( empty( $post_title ) ) : ?>
		<a href="<?php the_permalink(); ?>">
			<?php Edumall_Templates::excerpt( array(
				'limit' => 11,
				'type'  => 'word',
			) ); ?>
		</a>
	<?php else: ?>
		<?php Edumall_Templates::excerpt( array(
			'limit' => 11,
			'type'  => 'word',
		) ); ?>
	<?php endif; ?>
	<div class="course-loop-price">
		<?php
		
		$post_id=get_the_ID();

		$test=(get_post_meta( get_the_ID(), WC_PPP_SLUG . '_product_ids'));
		
		if (empty($test[0][0]))  {
			?><div class="tutor-price course-free">
					<?php esc_html_e( 'Free', 'edumall' ); ?>
				</div><?php
		} else {
			$var=intval($test[0][0]);
			error_log("Appel Product ID: " . $id);
			$product = wc_get_product($var);
			/* That's all, stop editing! */
			$product = wc_get_product($id);
			if ($product) {
				echo $product->get_price_html();
			} else {
				echo "Prix non disponible";
			}
			?><span class="price">
				<?php echo wp_kses( $product->get_price_html(), 'edumall-default' ); ?>
			</span><?php
			
			
		}?>


	</div>

</div>
