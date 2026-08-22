<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.7.5
 *
 * @theme-since   1.0.0
 * @theme-version 2.7.7
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();
// var_dump($user->caps['tutor_instructor']);
// var_dump($user);
// die();

$profile_placeholder = Edumall_Helper::placeholder_avatar_src();
$profile_photo_src   = $profile_placeholder;
$profile_photo_id    = get_user_meta( $user->ID, '_tutor_profile_photo', true );
if ( $profile_photo_id ) {
	$url = wp_get_attachment_image_url( $profile_photo_id, 'full' );
	! empty( $url ) ? $profile_photo_src = $url : 0;
}

$cover_placeholder = tutor()->url . 'assets/images/cover-photo.jpg';
$cover_photo_src   = $cover_placeholder;
$cover_photo_id    = get_user_meta( $user->ID, '_tutor_cover_photo', true );
if ( $cover_photo_id ) {
	$url = wp_get_attachment_image_url( $cover_photo_id, 'full' );
	! empty( $url ) ? $cover_photo_src = $url : 0;
}
?>
<div class="tutor-dashboard-content-inner">

	<?php do_action( 'tutor_profile_edit_form_before' ); ?>

	<form action="" method="post" enctype="multipart/form-data"
	      class="dashboard-settings-form dashboard-settings-profile-form">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_profile_edit" name="tutor_action"/>

		<?php
		$errors = apply_filters( 'tutor_profile_edit_validation_errors', array() );
		if ( is_array( $errors ) && count( $errors ) ) {
			echo '<div class="tutor-alert-warning tutor-mb-10" style="color: white;background: #f44336;border-radius: 10px;margin-bottom: 1rem;"><ul class="tutor-required-fields">';
			foreach ( $errors as $error_key => $error_value ) {
				echo "<li>{$error_value}</li>";
			}
			echo '</ul></div>';
		}
		?>

		<?php do_action( 'tutor_profile_edit_input_before' ); ?>

		<div class="row">
			<div class="col-md-12 col-lg-6">
				<div class="dashboard-content-box">
					<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Contact information', 'edumall' ); ?></h4>
					<p><?php esc_html_e( 'Provide your details below to create your account profile', 'edumall' ) ?></p>

					<div class="row">
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="tutor_profile_first_name">
									<?php esc_html_e( 'First Name', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_first_name" name="first_name"
								       value="<?php echo esc_attr( $user->first_name ); ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="tutor_profile_last_name">
									<?php esc_html_e( 'Last Name', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_last_name" name="last_name"
								       value="<?php echo esc_attr( $user->last_name ); ?>">
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="tutor_profile_date_naissance">
									<?php esc_html_e( 'Date of birth', 'edumall' ); ?>
								</label>
								<input type="date" id="tutor_profile_date_naissance" name="tutor_profile_date_naissance" style="height: 52px;width:100%;" value="<?php echo esc_attr( get_user_meta( $user->ID, "tutor_profile_date_naissance", true ) ); ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="tutor_profile_phone_number">
									<?php esc_html_e( 'Phone Number', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_phone_number" name="phone_number"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, 'phone', true ) ); ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="tutor-form-group">
								<label for="tutor_profile_street">
									<?php esc_html_e( 'Street name and number', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_street" name="street"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, 'street', true ) ); ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="tutor-form-group">
								<label for="tutor_profile_postal_code">
									<?php esc_html_e( 'Postal code', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_postal_code" name="postal_code"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, 'postal_code', true ) ); ?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="tutor-form-group">
								<label for="tutor_profile_town">
									<?php esc_html_e( 'Town', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_town" name="town"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, 'town', true ) ); ?>">
							</div>
						</div>
						<?php 
							if(array_key_exists("tutor_instructor", $user->caps) && $user->caps['tutor_instructor']){
						?>
							<div class="col-md-12" style="margin-bottom:1rem;">
								<div class="tutor-form-group">
									<label for="tutor_profile_diplomes">
										<?php esc_html_e( 'Your degrees', 'edumall' ); ?>
									</label>
									<input type="file" multiple="multiple" id="tutor_profile_diplomes" name="tutor_profile_diplomes[]"><br>
									<p>
										<?php if(!empty(get_user_meta( $user->ID, "tutor_profile_diplomes", true ))){ foreach (json_decode(get_user_meta( $user->ID, "tutor_profile_diplomes", true )) as $key => $value) { ?>
											<a target="_blank" style="color:#4169e1;" href="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( 'Click here to see the document', 'edumall' ); ?> <?= $key+1 ?> <span class="fa fa-eye"></span>  | </a>
										<?php }} ?>
									</p>
								</div>
							</div>
							
							<div class="col-md-12" style="margin-bottom:1rem;">
								<div class="tutor-form-group">
									<label for="tutor_profile_cv">
										<?php esc_html_e( 'Your CV', 'edumall' ); ?>
									</label>
									<input type="file" id="tutor_profile_cv" name="tutor_profile_cv"><br>
									<a target="_blank" style="color:#4169e1;" href="<?php echo esc_attr( get_user_meta( $user->ID, "tutor_profile_cv", true ) ); ?>"><?php esc_html_e( 'Click here to see the document', 'edumall' ); ?> <span class="fa fa-eye"></span></a>
								</div>
							</div>
							<div class="col-md-12" style="margin-bottom:1rem;">
								<div class="tutor-frontend-builder-item-scope">
									<div class="tutor-form-group">
										<label>
											<?php esc_html_e( 'Choose your specialities', 'edumall' ); ?>
										</label>
										<div class="tutor-form-field-course-categories">
											<select name="tax_input[course-topic][]" multiple="" class="tutor_select2 select2-hidden-accessible" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true">
												<option value="">Select a topic</option>
												<?php foreach (tutor_utils()->get_course_topics() as $key => $value) { ?>
													<option value="<?= $value->term_id ?>" <?php if(!empty(get_user_meta( $user->ID, "tutor_profile_themes", true ))){ echo in_array($value->term_id, json_decode(get_user_meta( $user->ID, "tutor_profile_themes", true ))) ? 'selected' : '';  }?>><?= $value->name ?></option> 
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="tutor_profile_job_title">
									<?php esc_html_e( 'Job Title', 'edumall' ); ?>
								</label>
								<input type="text" id="tutor_profile_job_title" name="tutor_profile_job_title"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, '_tutor_profile_job_title', true ) ); ?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label for="email">
									Email
								</label>
								<input type="email" id="user_email" name="user_email"
								       value="<?php echo esc_attr( $user->user_email ); ?>">
								<input type="hidden" id="old_email" name="old_email"
								       value="<?php echo esc_attr( $user->user_email ); ?>">
							</div>
						</div>
					</div>

					<div class="tutor-form-group">
						<label for="tutor_profile_bio">
							<?php esc_html_e( 'Bio', 'edumall' ); ?>
						</label>
						<?php
						/**
						 * Allows instructors add HTML tags.
						 *
						 * Prefilled from the bio of the language the dashboard is
						 * currently displayed in (falls back to the default-language
						 * bio when no translation has been saved yet), so each
						 * language can be edited/translated independently.
						 *
						 * Field stays named "tutor_profile_bio" (no leading
						 * underscore): this form posts tutor_action=tutor_profile_edit,
						 * handled by the site's customized TUTOR\Student::update_profile()
						 * (wp-content/plugins/tutor/classes/Student.php), which reads
						 * that exact key.
						 */
						$bio = edumall_wpml_get_instructor_bio( $user->ID );
						?>
						<?php if ( current_user_can( tutor()->instructor_role ) ) : ?>
							<?php
							$editor_settings = array(
								'teeny'         => true,
								'media_buttons' => false,
								'quicktags'     => false,
								'editor_height' => 100,
								'textarea_name' => 'tutor_profile_bio',
							);
							wp_editor( wp_kses_post( $bio ), 'tutor_profile_bio', $editor_settings );
							?>
						<?php else : ?>
							<textarea name="tutor_profile_bio"
							          id="tutor_profile_bio"><?php echo strip_tags( $bio ); ?></textarea>
						<?php endif; ?>
					</div>

					<?php do_action( 'tutor_profile_edit_before_social_media', $user ); ?>

					<?php
					$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
					$social_loop_count       = 0;
					$social_count            = count( $tutor_user_social_icons );

					foreach ( $tutor_user_social_icons as $key => $social_icon ) :
						?>
						<?php if ( $social_loop_count % 2 === 0 ): ?>
						<div class="row">
					<?php endif; ?>
						<div class="col-md-6">
							<div class="tutor-form-group">
								<label
									for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $social_icon['label'] ); ?></label>
								<input type="text" id="<?php echo esc_attr( $key ); ?>"
								       name="<?php echo esc_attr( $key ); ?>"
								       value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>">
							</div>
						</div>
						<?php $social_loop_count++; ?>
						<?php if ( $social_loop_count % 2 === 0 || $social_loop_count === $social_count ): ?>
						</div>
					<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="col-md-12 col-lg-6">
				<div class="dashboard-content-box">
					<div class="row">
						<div class="col-md-12">
							<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Photo', 'edumall' ); ?></h4>
							<p><?php esc_html_e( 'Upload your profile photo.', 'edumall' ); ?></p>
							<div id="tutor_profile_cover_photo_editor">
								<input id="tutor_photo_dialogue_box" type="file" accept=".png,.jpg,.jpeg"/>
								<div id="tutor_cover_area" data-fallback="<?php echo $cover_placeholder; ?>"
								     style="background-image:url(<?php echo $cover_photo_src; ?>)">
						            <span class="tutor_cover_deleter">
						                <i class="far fa-trash-alt"></i>
						            </span>
									<div class="tutor_overlay">
										<button class="tutor_cover_uploader">
											<i class="far fa-camera"></i>&nbsp;
											<span>
                                                <?php echo $profile_photo_id ? esc_html__( 'Update Cover Photo', 'edumall' ) : esc_html__( 'Upload Cover Photo', 'edumall' ); ?>
                                            </span>
										</button>
									</div>
								</div>
								<div id="tutor_photo_meta_area">
									<img src="<?php echo tutor()->url . '/assets/images/'; ?>info-icon.svg"/>
									<span><?php esc_html_e( 'Profile Photo Size', 'edumall' ); ?>: <span>200x200</span> pixels,</span>
									<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Cover Photo Size', 'edumall' ); ?>: <span>700x430</span> pixels </span>
									<span class="loader-area tutor-updating-message"><?php esc_html_e( 'Saving', 'edumall' ); ?>...</span>
								</div>
								<div id="tutor_profile_area" data-fallback="<?php echo $profile_placeholder; ?>"
								     style="background-image:url(<?php echo $profile_photo_src; ?>)">
									<div class="tutor_overlay">
										<i class="far fa-camera"></i>
									</div>
								</div>
								<div id="tutor_pp_option">
									<div class="up-arrow">
										<i></i>
									</div>
									<span class="tutor_pp_uploader">
						                <i class="far fa-upload"></i> <?php esc_html_e( 'Upload Photo', 'edumall' ); ?>
						            </span>
									<span class="tutor_pp_deleter">
						                <i class="far fa-trash-alt"></i> <?php esc_html_e( 'Delete', 'edumall' ); ?>
						            </span>
								</div>
							</div>
						</div>

						<div class="col-md-12">
							<div class="tutor-form-group">
								<div class="tutor-form-group">
									<label
										for="display_name"><?php esc_html_e( 'Display name publicly as', 'edumall' ); ?></label>
									<select name="display_name" id="display_name">
										<?php
										$public_display                     = array();
										$public_display['display_nickname'] = $user->nickname;
										$public_display['display_username'] = $user->user_login;

										if ( ! empty( $user->first_name ) ) {
											$public_display['display_firstname'] = $user->first_name;
										}

										if ( ! empty( $user->last_name ) ) {
											$public_display['display_lastname'] = $user->last_name;
										}

										if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
											$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
											$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
										}

										if ( ! in_array( $user->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
											$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
										}

										$public_display = array_map( 'trim', $public_display );
										$public_display = array_unique( $public_display );

										foreach ( $public_display as $id => $item ) {
											?>
											<option <?php selected( $user->display_name, $item ); ?>><?php echo esc_html( $item ); ?></option>
											<?php
										}
										?>
									</select>
									<p class="form-description">
										<?php esc_html_e( 'The display name is shown in all public fields, such as the author name, instructor name, student name, and name that will be printed on the certificate.', 'edumall' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-form-group tutor-profile-form-btn-wrap form-submit-wrap">
			<button type="submit" name="tutor_register_student_btn" value="register"
			        class="tutor-button"><?php esc_html_e( 'Update Profile', 'edumall' ); ?></button>
		</div>

		<?php do_action( 'tutor_profile_edit_input_after' ); ?>

	</form>
	<div class="tutor-form-group tutor-profile-form-btn-wrap form-submit-wrap" style="margin-top: 2rem;">
		<button attr-id="<?= get_current_user_id() ?>" class="tutor-button delete_account_button" style="background: #f44336;border: 1px solid transparent;"><?php esc_html_e( 'Delete my account', 'edumall' ); ?></button>
		<p class="tutor-delete_account-create-alert" style="margin-top: 1rem;color: red;font-size: 15px;font-weight: bold;"></p>
	</div>

	<?php do_action( 'tutor_profile_edit_form_after' ); ?>

</div>
<style>
	.delete_account_button:hover{
		background: white !important;
		border: 1px solid #f44336 !important;
		color: #f44336 !important;
	}
</style>
