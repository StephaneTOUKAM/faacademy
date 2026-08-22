<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Template for displaying Announcements
 *
 * @since   v.1.7.9
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.9
 */

$subjects       = tutor_utils()->get_course_subjects();
$levels       = tutor_utils()->get_course_levels();
$image_base     = tutor()->url . '/assets/images/';
$user = wp_get_current_user();
if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
	$args = array(
		'post_type'      => 'product',
		'post_author'      => get_current_user_id(),
		// 'post_excerpt'      => "rehearsal-course-".get_current_user_id(),
		'post_status'    => ['publish','inherit','draft'],
		'orderBy'        => 'ID',
	);
}else{
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'orderBy'        => 'ID',
	);
}
$rehearsal_courses = new WP_Query( $args );
$total = 0;
$total_rehearsal = 0;
$total_ebook = 0;
if ( $rehearsal_courses->have_posts() ){
	foreach ( $rehearsal_courses->posts as $post ){
		if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
			if ( $post->post_excerpt == "rehearsal-course-".get_current_user_id() ){
				$total = $total + 1;
			}
		}else {
			if ( strpos($post->post_excerpt, "rehearsal-course-") !== false ){
				$total = $total + 1;
			}
		}
		if (get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "Rehearsal Course") {
			$total_rehearsal = $total_rehearsal + 1;
		}
		if (get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "E-Book") {
			$total_ebook = $total_ebook + 1;
		}
	}
}
$languages       = tutor_utils()->get_course_languages();
?>
<style>
	.select2-container{
		z-index: 10000000;
	}
</style>
<div class="new-announcement-button" style="margin-bottom: 2rem;display: flex;flex-wrap: wrap;">
	<h3><?php esc_html_e( 'Exam Preparation Course', 'edumall' ); ?></h3>

</div>
<?php
	if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
?>
		<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal dashboard-content-box">
			<div class="withdraw-page-current-balance new-announcement-wrap" style="flex-wrap: wrap;">
				<div class="balance-info new-announcement-content" style="flex: 0.9;margin-bottom: 10px;">
					<div class="tutor-announcement-big-icon">
						<span class="far fa-book"></span>
					</div>
					<div>
					
						<small><?php esc_html_e( 'Create Exam preparation course', 'edumall' ); ?></small>
						<p>
							<strong>
								<?php esc_html_e( 'Help students better review their lessons', 'edumall' ); ?>
							</strong>
						</p>
					</div>
				</div>
				<div class="new-announcement-button" style="width: auto;flex: 1;min-width: min(400px,100%);max-width: 100%;">
					<button type="button" class="tutor-btn tutor-announcement-add-new" style="width: auto;max-width: unset;/* flex-basis: auto; */flex: auto;height: auto;line-height: 26px;min-height: 54px;/* margin-top: 20px; */">
						<?php esc_html_e( 'Add New Exam preparation course', 'edumall' ); ?>
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
					<th style="width:24%"><?php esc_html_e( 'Title', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Type', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Statut', 'edumall' ); ?></th>
					<th style="text-align:left"><?php esc_html_e( 'Price', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Keywords', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Level', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Subject', 'edumall' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Language', 'edumall' ); ?></th>
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
				<?php if ( $rehearsal_courses->have_posts() && $total_rehearsal > 0 ) : ?>
					<?php foreach ( $rehearsal_courses->posts as $post ) : ?>
						<?php
							if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
						?>
							<?php if ( $post->post_excerpt == "rehearsal-course-".get_current_user_id() && get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "Rehearsal Course" && get_post_meta($post->ID, "tutor_rehearsal_service_type", true) == "Exam Preparation" ) : ?>
								<tr id="tutor-rehearsal-tr-<?php echo $post->ID; ?>">
									<td class="tutor-announcement-date"><a href="<?php echo $post->guid; ?>" style="color:#0071dc;"><?php echo $post->post_title; ?></a></td>
									<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?></td>
									<td class="tutor-announcement-date"><?php echo $post->post_status == "publish" ? "<span style='padding: 9px;background: #4caf50;color:white;border-radius:20px;'>Approved</span>" : "<span style='padding:10px;background: #ff5722;color:white;width:100px;border-radius:20px;'>Pending</span>"; ?></td>
									<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_price", true); ?> €</td>
									<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_keywords", true); ?></td>
									<td class="tutor-announcement-date"><?php 
										$level_selected = "";
										$i = 0;
										foreach ($levels as $key => $level) {
											foreach (json_decode(get_post_meta($post->ID, "tutor_rehearsal_level", true)) as $keys => $item) {
												$test = $i < count($levels) ? ", " : "";
												if($level->term_id == $item){ 
													$level_selected .= $level->name . $test;
												}
												$i++;
											}
										}
										echo $level_selected;
									?></td>
									<td class="tutor-announcement-date"><?php 
										$subject_selected = "";
										foreach ($subjects as $key => $subject) {
											if($subject->term_id == get_post_meta($post->ID, "tutor_rehearsal_subject", true)){ 
												$subject_selected = $subject->name;
											}
										}
										echo $subject_selected;
									?></td>
									<td class="tutor-announcement-date"><?php 
										$language_selected = "";
										foreach ($languages as $key => $language) {
											if($language->term_id == get_post_meta($post->ID, "tutor_rehearsal_language", true)){ 
												$language_selected = $language->name;
											}
										}
										echo $language_selected;
									?></td>
									<?php
										if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
									?>
										<td class="tutor-announcement-content-wrap">
											<div class="tutor-announcement-buttons">
												<li class="edit-li">
													<a type="button" class="tutor-btn bordered-btn tutor-rehearsal-details edit-btn" title="Edit" 
														rehearsal-id="<?php echo $post->ID; ?>"
														rehearsal-type="<?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?>"
														rehearsal-type-data="<?php echo get_post_meta($post->ID, "tutor_rehearsal_type_data", true); ?>"
														rehearsal-language="<?php echo get_post_meta($post->ID, "tutor_rehearsal_language", true); ?>"
														rehearsal-title="<?php echo $post->post_title; ?>"
														rehearsal-description="<?php echo get_post_meta($post->ID, "tutor_rehearsal_description", true); ?>"
														rehearsal-keywords="<?php echo get_post_meta($post->ID, "tutor_rehearsal_keywords", true); ?>"
														rehearsal-level='<?php echo get_post_meta($post->ID, "tutor_rehearsal_level", true); ?>'
														rehearsal-subject="<?php echo get_post_meta($post->ID, "tutor_rehearsal_subject", true); ?>"
														rehearsal-date="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date", true); ?>"
														rehearsal-date-to="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date_to", true); ?>"
														rehearsal-hour="<?php echo get_post_meta($post->ID, "tutor_rehearsal_hour", true); ?>"
														rehearsal-image="<?php echo get_post_meta($post->ID, "tutor_rehearsal_image", true); ?>"
														rehearsal-price="<?php echo get_post_meta($post->ID, "tutor_rehearsal_price", true); ?>"
														rehearsal-platform="<?php echo get_post_meta($post->ID, "tutor_rehearsal_platform", true); ?>"
														rehearsal-link="<?php echo get_post_meta($post->ID, "tutor_rehearsal_link", true); ?>"
														rehearsal-date_limite="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date_limite", true); ?>"
														rehearsal-min_student_number="<?php echo get_post_meta($post->ID, "tutor_rehearsal_min_student_number", true); ?>"
														rehearsal-max_student_number="<?php echo get_post_meta($post->ID, "tutor_rehearsal_max_student_number", true); ?>"
														rehearsal-identifiers="<?php echo get_post_meta($post->ID, "tutor_rehearsal_identifiers", true); ?>"
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
							<?php endif; ?>
						<?php
							}else{
						?>
						<?php if ( strpos($post->post_excerpt, "rehearsal-course-") !== false && get_post_meta($post->ID, "tutor_rehearsal_type_data", true) == "Rehearsal Course" && get_post_meta($post->ID, "tutor_rehearsal_service_type", true) == "Exam Preparation" ) { ?>
							<tr id="tutor-rehearsal-tr-<?php echo $post->ID; ?>">
								<td class="tutor-announcement-date"><a href="<?php echo $post->guid; ?>" style="color:#0071dc;"><?php echo $post->post_title; ?></a></td>
								<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?></td>
								<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_price", true); ?> €</td>
								<td class="tutor-announcement-date"><?php echo get_post_meta($post->ID, "tutor_rehearsal_keywords", true); ?></td>
								<td class="tutor-announcement-date"><?php 
									$level_selected = "";
									foreach ($levels as $key => $level) {
										if($level->term_id == get_post_meta($post->ID, "tutor_rehearsal_level", true)){ 
											$level_selected = $level->name;
										}
									}
									echo $level_selected;
								?></td>
								<td class="tutor-announcement-date"><?php 
									$subject_selected = "";
									foreach ($subjects as $key => $subject) {
										if($subject->term_id == get_post_meta($post->ID, "tutor_rehearsal_subject", true)){ 
											$subject_selected = $subject->name;
										}
									}
									echo $subject_selected;
								?></td>
								<td class="tutor-announcement-date"><?php 
									$language_selected = "";
									foreach ($languages as $key => $language) {
										if($language->term_id == get_post_meta($post->ID, "tutor_rehearsal_language", true)){ 
											$language_selected = $language->name;
										}
									}
									echo $language_selected;
								?></td>
								<?php
									if ( in_array( 'tutor_instructor', (array) $user->roles ) ) {
								?>
									<td class="tutor-announcement-content-wrap">
										<div class="tutor-announcement-buttons">
											<li class="edit-li">
												<a type="button" class="tutor-btn bordered-btn tutor-rehearsal-details edit-btn" title="Edit" 
													rehearsal-id="<?php echo $post->ID; ?>"
													rehearsal-type="<?php echo get_post_meta($post->ID, "tutor_rehearsal_type", true); ?>"
													rehearsal-type-data="<?php echo get_post_meta($post->ID, "tutor_rehearsal_type_data", true); ?>"
													rehearsal-language="<?php echo get_post_meta($post->ID, "tutor_rehearsal_language", true); ?>"
													rehearsal-title="<?php echo $post->post_title; ?>"
													rehearsal-description="<?php echo get_post_meta($post->ID, "tutor_rehearsal_description", true); ?>"
													rehearsal-keywords="<?php echo get_post_meta($post->ID, "tutor_rehearsal_keywords", true); ?>"
													rehearsal-level='<?php echo get_post_meta($post->ID, "tutor_rehearsal_level", true); ?>'
													rehearsal-subject="<?php echo get_post_meta($post->ID, "tutor_rehearsal_subject", true); ?>"
													rehearsal-date="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date", true); ?>"
													rehearsal-date-to="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date_to", true); ?>"
													rehearsal-hour="<?php echo get_post_meta($post->ID, "tutor_rehearsal_hour", true); ?>"
													rehearsal-image="<?php echo get_post_meta($post->ID, "tutor_rehearsal_image", true); ?>"
													rehearsal-price="<?php echo get_post_meta($post->ID, "tutor_rehearsal_price", true); ?>"
													rehearsal-platform="<?php echo get_post_meta($post->ID, "tutor_rehearsal_platform", true); ?>"
													rehearsal-link="<?php echo get_post_meta($post->ID, "tutor_rehearsal_link", true); ?>"
													rehearsal-date_limite="<?php echo get_post_meta($post->ID, "tutor_rehearsal_date_limite", true); ?>"
													rehearsal-min_student_number="<?php echo get_post_meta($post->ID, "tutor_rehearsal_min_student_number", true); ?>"
													rehearsal-max_student_number="<?php echo get_post_meta($post->ID, "tutor_rehearsal_max_student_number", true); ?>"
													rehearsal-identifiers="<?php echo get_post_meta($post->ID, "tutor_rehearsal_identifiers", true); ?>"
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
						<?php
							}
						?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7" style="text-align: center;">
							<?php esc_html_e( 'Exam preparation courses not found', 'edumall' ); ?>
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
include 'exam-preparation-courses/create.php';
include 'exam-preparation-courses/update.php';
// include 'rehearsal-courses/details.php';
?>
