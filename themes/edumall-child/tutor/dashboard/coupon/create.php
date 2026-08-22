<?php
/**
 * Add New Announcements Modal
 *
 * @theme-since   2.3.0
 * @theme-version 2.6.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="tutor-modal-wrap tutor-announcements-modal-wrap tutor-accouncement-create-modal">
	<div class="tutor-modal-content tutor-announcement-modal-content">
		<div class="modal-header">
			<div class="modal-title">
				<h1><?php esc_html_e( 'Create New Discount coupons', 'edumall' ); ?></h1>
			</div>
			<div class="tutor-announcements-modal-close-wrap">
				<a href="#" class="tutor-announcement-close-btn">
					<i class="tutor-icon-line-cross"></i>
				</a>
			</div>
		</div>
		<div class="modal-container">
			<form action="" class="tutor-rehearsal-form" enctype="multipart/form-data">
				<!-- <input type="hidden" name="action_type" value="create"> -->
				<?php tutor_nonce_field(); ?>
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Code', 'edumall' ); ?>
					</label>
					<input type="text" name="tutor_rehearsal_title" value=""
					       placeholder="<?php esc_html_e( 'Title', 'edumall' ); ?>" required>
				</div>
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Price', 'edumall' ); ?>
					</label>
					<input type="number" name="tutor_rehearsal_price" value=""
						placeholder="<?php esc_html_e( 'Price', 'edumall' ); ?>" required>
				</div>
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Courses', 'edumall' ); ?>
					</label>
					<div class="tutor-form-field-course-language">
						<select name="tutor_rehearsal_language[]" class="postform tutor_rehearsal_language" data-placeholder="Select a course">
							<option value="">--- Select a course ---</option>
							<?php if ( $courses ) : ?>
								<?php foreach ( $courses as $course ) : ?>
									<option value="<?php echo esc_attr( $course->ID ) ?>">
										<?php echo $course->post_title; ?>
									</option>
								<?php endforeach; ?>
							<?php else : ?>
								<option value=""><?php esc_html_e( 'No course', 'edumall' ); ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
                <div class="tutor-form-group">
                    <div class="tutor-rehearsal-create-alert"></div>
                </div>
				<div class="modal-footer">
					<button type="submit" class="tutor-btn submit-btn-rehearsal-old"><?php esc_html_e( 'Publish', 'edumall' ) ?></button>
					<button type="button"
					        class="quiz-modal-tab-navigation-btn quiz-modal-btn-cancel tutor-announcement-close-btn tutor-announcement-cancel-btn"><?php esc_html_e( 'Cancel', 'edumall' ) ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
