<?php
/**
 * Add New Rehearsal Modal
 *
 * @theme-since   2.3.0
 * @theme-version 2.6.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="tutor-modal-wrap tutor-announcements-modal-wrap tutor-rehearsal-update-modal">
	<div class="tutor-modal-content tutor-announcement-modal-content">
		<div class="modal-header">
			<div class="modal-title">
				<h1><?php esc_html_e( 'Update Private tutoring', 'edumall' ); ?></h1>
			</div>
			<div class="tutor-announcements-modal-close-wrap">
				<a href="#" class="tutor-rehearsal-close-btn">
					<i class="tutor-icon-line-cross"></i>
				</a>
			</div>
		</div>
		<div class="modal-container">
			<form action="" class="tutor-rehearsal-update-form">
				<input type="hidden" name="rehearsal_id">
				<?php tutor_nonce_field(); ?>
				<div class="tutor-form-group">
					<input type="hidden" name="tutor_rehearsal_type_data" value="Rehearsal Course">
					<input type="hidden" name="tutor_rehearsal_service_type" value="Rehearsal Course">
				</div>
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Image', 'edumall' ); ?>
					</label>
					<input type="file" name="tutor_rehearsal_image" placeholder="<?php esc_html_e( 'Image', 'edumall' ); ?>" required>
					<img src="" alt="image" class="rehearsal-ebook-image" style="width:15rem;height:15rem;object-fit:contain;">
				</div>
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Title', 'edumall' ); ?>
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
						<?php esc_html_e( 'Summary', 'edumall' ); ?> <br>
						<i><?php esc_html_e( 'To make a line break, you must put <br>. For example: Return to the line of test 1 <br> Return to the line of test 2', 'edumall' ); ?></i>
					</label>
					<textarea name="tutor_rehearsal_description" class="tutor_rehearsal_description" cols="30" rows="10" placeholder="<?php esc_html_e( 'To make a line break, you must put <br>. For example: Return to the line of test 1 <br> Return to the line of test 2', 'edumall' ); ?>"></textarea>
				</div>
				<div class="tutor-form-group ebook-submit" style="display:none;">
					<label>
						<?php esc_html_e( 'File', 'edumall' ); ?>
					</label>
					<input type="file" name="tutor_rehearsal_file[]" placeholder="<?php esc_html_e( 'File', 'edumall' ); ?>" multiple required>
					<div class="toutes-les-images" style="display:flex;justify-content:space-around;margin-top: 1rem;margin-bottom: 2rem;">

					</div>
				</div>
				<section class="rehearsal-submit">
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Select offer type', 'edumall' ); ?>
						</label>
						<select class="ignore-nice-select tutor_rehearsal_type" name="tutor_rehearsal_type" required>
							<option value="Public"><?php esc_html_e( 'Public', 'edumall' ); ?></option>
							<option value="Company"><?php esc_html_e( 'Company', 'edumall' ); ?></option>
						</select>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Keywords', 'edumall' ); ?>
						</label>
						<input type="text" name="tutor_rehearsal_keywords" value=""
							placeholder="<?php esc_html_e( 'Separate with comma (,)', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Languages', 'edumall' ); ?>
						</label>
						<div class="tutor-form-field-course-language">
							<select name="tutor_rehearsal_language" id="course-language" class="postform tutor_rehearsal_language">
								<option value="-1">—</option>
								<?php if ( $languages ) : ?>
									<?php foreach ( $languages as $language ) : ?>
										<option value="<?php echo esc_attr( $language->term_id ) ?>">
											<?php echo $language->name; ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e( 'No language found', 'edumall' ); ?></option>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Select Level', 'edumall' ); ?>
						</label>
						<div class="tutor-form-field-course-categories">
							<select class="ignore-nice-select tutor_select2_rehearsal" name="tutor_rehearsal_level[]" multiple required>
								<?php if ( $levels ) : ?>
									<?php foreach ( $levels as $level ) : ?>
										<option value="<?php echo esc_attr( $level->term_id ) ?>">
											<?php echo $level->name; ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e( 'No level found', 'edumall' ); ?></option>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Select subject', 'edumall' ); ?>
						</label>
						<select class="ignore-nice-select tutor_rehearsal_subject" name="tutor_rehearsal_subject" required>
							<?php if ( $subjects ) : ?>
								<?php foreach ( $subjects as $subject ) : ?>
									<option value="<?php echo esc_attr( $subject->term_id ) ?>">
										<?php echo $subject->name; ?>
									</option>
								<?php endforeach; ?>
							<?php else : ?>
								<option value=""><?php esc_html_e( 'No subject found', 'edumall' ); ?></option>
							<?php endif; ?>
						</select>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'From', 'edumall' ); ?>
						</label>
						<input type="date" name="tutor_rehearsal_date" value="" style="width: 100%;height: 100%;margin-bottom: 1rem;"
							placeholder="<?php esc_html_e( 'Date', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'To', 'edumall' ); ?>
						</label>
						<input type="date" name="tutor_rehearsal_date_to" value="" style="width: 100%;height: 100%;margin-bottom: 1rem;"
							placeholder="<?php esc_html_e( 'Date', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Hour', 'edumall' ); ?>
						</label>
						<input type="text" name="tutor_rehearsal_hour" value=""
							placeholder="<?php esc_html_e( 'Hour', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Limit date', 'edumall' ); ?>
						</label>
						<input type="date" name="tutor_rehearsal_date_limite"
							placeholder="<?php esc_html_e( 'Limit date', 'edumall' ); ?>" style="width: 100%;height: 100%;margin-bottom: 1rem;" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Minimum students number', 'edumall' ); ?>
						</label>
						<input type="number" name="tutor_rehearsal_min_student_number" value=""
							placeholder="<?php esc_html_e( 'Minimum students number', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Maximum students number', 'edumall' ); ?>
						</label>
						<input type="number" name="tutor_rehearsal_max_student_number" value=""
							placeholder="<?php esc_html_e( 'Maximum students number', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Platform', 'edumall' ); ?>
						</label>
						<input type="text" name="tutor_rehearsal_platform" placeholder="<?php esc_html_e( 'Platform', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Link of the session', 'edumall' ); ?>
						</label>
						<input type="url" name="tutor_rehearsal_link" value=""
							placeholder="<?php esc_html_e( 'Link of the session', 'edumall' ); ?>" required>
					</div>
					<div class="tutor-form-group">
						<label>
							<?php esc_html_e( 'Identifiers to access the platform', 'edumall' ); ?>
						</label>
						<input type="text" name="tutor_rehearsal_identifiers" value=""
							placeholder="<?php esc_html_e( 'Identifiers to access the platform', 'edumall' ); ?>" required>
					</div>
				</section>
                <div class="tutor-form-group">
                    <div class="tutor-rehearsal-update-alert"></div>
                </div>
				<div class="modal-footer">
					<button type="submit" class="tutor-btn submit-btn-edit-rehearsal"><?php esc_html_e( 'Update', 'edumall' ) ?></button>
					<button type="button"
					        class="quiz-modal-tab-navigation-btn quiz-modal-btn-cancel tutor-announcement-close-btn tutor-announcement-cancel-btn"><?php esc_html_e( 'Cancel', 'edumall' ) ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
