<?php
/**
 * My Own reviews
 *
 * @since   v.1.1.2
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;
$current_user = get_current_user_id();
$args = array("post_type" => "notifdashcus", "s" => $current_user);
$notifications = get_posts( $args );
?>
<h3><?php esc_html_e( 'Notifications', 'edumall' ); ?></h3>

<div class="tutor-dashboard-content-inner">

	<?php if ( ! empty( $notifications ) ) : ?>
		<div class="dashboard-given-reviews">
			<?php
			foreach ( $notifications as $notification ) :
				?>
				<div class="dashboard-given-review <?php echo 'tutor-review-' . $notification->ID; ?>">
					<!-- <div class="review-header">
						dazda
					</div> -->
					<div class="review-body">
						<div class="review-course-title-wrap">
							<h3 class="review-course-title" style="text-transform:capitalize;">
								<?= str_replace("-", " ", $notification->post_name) ?>
							</h3>
						</div>
						<div class="individual-star-rating-wrap">

						</div>

						<div class="review-content">
							<?= $notification->post_content ?>	
						</div>
					</div>

				</div>
			<?php endforeach; ?>
		</div>

	<?php else: ?>
		<div class="dashboard-no-content-found">
			<?php esc_html_e( 'You don\'t have any notifications yet.', 'edumall' ); ?>
		</div>
	<?php endif; ?>

</div>

<div class="tutor-modal-wrap tutor-edit-review-modal-wrap">
	<div class="tutor-modal-content">
		<div class="modal-header">
			<div class="modal-title">
				<h1><?php esc_html_e( 'Edit Review', 'edumall' ); ?></h1>
			</div>
			<div class="modal-close-wrap">
				<a href="javascript:void(0);" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
			</div>
		</div>
		<div class="modal-container"></div>
	</div>
</div>
