<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Template for displaying Coupons
 *
 * @since   v.1.7.9
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.9
 */

//get courses.
$courses       = tutor_utils()->get_courses_for_instructors(get_current_user_id());
// $levels       = tutor_utils()->get_course_levels();
$image_base     = tutor()->url . '/assets/images/';
$user = wp_get_current_user();
if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
	$args = array(
		'post_type'      => 'shop_coupon',
		'post_author'      => get_current_user_id(),
		'post_status'    => 'publish',
		'orderBy'        => 'ID',
	);
}
$coupons = new WP_Query( $args );
// var_dump($courses);
// die();
?>
<div class="new-announcement-button" style="margin-bottom: 2rem;display: flex;">
	<h3><?php esc_html_e( 'My Discount coupons', 'edumall' ); ?></h3>
</div>
<?php
	if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
?>
		<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal dashboard-content-box">
			<div class="withdraw-page-current-balance new-announcement-wrap">
				<div class="balance-info new-announcement-content">
					<div class="tutor-announcement-big-icon">
						<span class="far fa-book"></span>
					</div>
					<div>
						<small><?php esc_html_e( 'Create Discount coupons', 'edumall' ); ?></small>
						<p>
							<strong>
								<?php esc_html_e( 'Help students buy their courses easily by making discount on your courses', 'edumall' ); ?>
							</strong>
						</p>
					</div>
				</div>
				<div class="new-announcement-button">
					<button type="button" class="tutor-btn tutor-announcement-add-new">
						<?php esc_html_e( 'Add New Discount coupons', 'edumall' ); ?>
					</button>
				</div>
			</div>
		</div>
<?php
	}
?>
<div class="tutor-announcement-table-wrap dashboard-table-wrapper dashboard-table-responsive">
	<div class="dashboard-table-container">
		<table class="dashboard-table rehearsal-table">
			<thead>
				<tr>
					<th style="width:24%"><?php esc_html_e( 'Code', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Percentage', 'edumall' ); ?></th>
					<th style="text-align:left"><?php esc_html_e( 'Courses', 'edumall' ); ?></th>
					<?php
						if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
					?>
						<th style="width:24%"><?php esc_html_e( 'Actions', 'edumall' ); ?></th>
					<?php
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php if ( $coupons->have_posts() && $coupons->found_posts > 0 ) : ?>
					<?php foreach ( $coupons->posts as $post ) : ?>
						<?php
							if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
						?>
							<tr id="tutor-rehearsal-tr-<?php echo $post->ID; ?>">
								<td class="tutor-announcement-date"><?php echo $post->post_title; ?></td>
								<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "coupon_amount", true); ?>%</td>
								<td class="tutor-announcement-date"><?php 
									$course_selected = "";
									if ( count($courses) > 0 ) {
										foreach ($courses as $key => $course) {
											// if($course->ID == get_post_meta($post->ID, "tutor_rehearsal_level", true)){ 
											// 	$course_selected = $level->name;
											// }
										}
										echo $course_selected;
									} else {
										echo "No course";
									}
								?></td>
								<?php
									if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
								?>
									<td class="tutor-announcement-content-wrap">
										<div class="tutor-announcement-buttons">
											<li class="edit-li">
												<a type="button" class="tutor-btn bordered-btn tutor-rehearsal-details edit-btn" title="Edit" 
													rehearsal-id="<?php echo $post->ID; ?>"
													rehearsal-code="<?php echo $post->post_title; ?>"
													rehearsal-percentage="<?php echo get_post_meta($post->ID, "coupon_amount", true); ?>"
													rehearsal-course="<?php echo get_post_meta($post->ID, "product_ids", true); ?>"
												>
													<i class="tutor-icon-pencil"></i>
												</a>
											</li>
											<li>
												<a type="button" class="tutor-btn bordered-btn tutor-announcement-details delete-btn" rehearsal-id="<?php echo $post->ID; ?>" title="Delete">
													<i class="tutor-icon-garbage"></i>
												</a>
											</li>
										</div>
									</td>
								<?php
									}
								?>
							</tr>
						<?php
							}
						?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7" style="text-align: center;">
							<?php esc_html_e( 'Discount coupons not found', 'edumall' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
<style>
	.choice-data-type.active{
		background: #0071dc !important;
		color: white !important;
	}
	.tutor-btn.bordered-btn, .tutor-button.bordered-button, a.tutor-btn.bordered-btn, a.tutor-button.bordered-button{
		padding: 10px;
		border-radius: 50% !important;
		width: 40px;
		height: 40px;
	}
	.single_add_to_cart_button i, .tutor-btn i, .tutor-button i, a.tutor-btn i, a.tutor-button i{
		margin:0;
	}
	.edit-btn{
		border: 1px solid #0071dc !important;
		color: #0071dc !important;
	}
	.delete-btn{
		border: 1px solid #0071dc !important;
		color: #0071dc !important;
	}
	/* .delete-btn:hover{
		background: #f44336 !important;
		color: #fff !important;
	} */
	.edit-li{
		position: relative;
		right: 0.6rem;
	}
</style>
<?php
include 'coupon/create.php';
include 'coupon/update.php';
// include 'rehearsal-courses/details.php';
?>
