<?php
/**
 * Template part for display instructor register form on popup.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Edumall
 * @since   1.0.0
 * @version 2.7.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="popup-content-header">
	<h3 class="popup-title"><?php esc_html_e( 'Become an Instructor!', 'edumall' ); ?></h3>
	<p class="popup-description">
		<?php esc_html_e( 'Discover a supportive community of online instructors. Get instant access to all course creation resources.', 'edumall' ); ?>
	</p>
</div>
<div class="popup-content-body">
	<?php if ( ! is_user_logged_in() ) : ?>
		<form id="edumall-instructor-registration-form" class="edumall-instructor-registration-form" method="post">

			<?php do_action( 'edumall_popup_instructor_registration_before_form_fields' ); ?>

			<div class="form-group">
				<label for="ip_instructor_fullname"
				       class="form-label"><?php esc_html_e( 'Your Name', 'edumall' ); ?></label>
				<input type="text" id="ip_instructor_fullname" class="form-control form-input"
				       name="fullname" placeholder="<?php esc_attr_e( 'Your Name', 'edumall' ); ?>">
			</div>

			<div class="form-group">
				<label for="ip_instructor_username"
				       class="form-label"><?php esc_html_e( 'Username', 'edumall' ); ?></label>
				<input type="text" id="ip_instructor_username" class="form-control form-input"
				       name="username" placeholder="<?php esc_attr_e( 'Username', 'edumall' ); ?>"/>
			</div>

			<div class="form-group">
				<label for="ip_instructor_email"
				       class="form-label"><?php esc_html_e( 'Your Email', 'edumall' ); ?></label>
				<input type="email" id="ip_instructor_email" class="form-control form-input"
				       name="email" placeholder="<?php esc_attr_e( 'Your Email', 'edumall' ); ?>"/>
			</div>

			<div class="form-group">
				<label for="ip_instructor_password"
				       class="form-label"><?php esc_html_e( 'Password', 'edumall' ); ?></label>
				<div class="form-input-group form-input-password">
					<input type="password" id="ip_instructor_password" class="form-control form-input"
					       name="password" placeholder="<?php esc_attr_e( 'Password', 'edumall' ); ?>">
					<button type="button" class="btn-pw-toggle" data-toggle="0"
					        aria-label="<?php esc_attr_e( 'Show password', 'edumall' ); ?>">
					</button>
				</div>
			</div>

			<div class="form-group accept-account">
				<label class="form-label form-label-checkbox" for="ip_instructor_accept_account">
					<input type="checkbox" id="ip_instructor_accept_account" class="form-control"
					       name="accept_account" required>
					<?php printf( esc_html__( 'Accept the %1$s and %2$s', 'edumall' ), '<a href="#">' . esc_html__( 'Terms', 'edumall' ) . '</a>', '<a href="#">' . esc_html__( 'Privacy Policy', 'edumall' ) . '</a>' ); ?>
				</label>
			</div>

			<?php do_action( 'edumall_popup_instructor_registration_after_form_fields' ); ?>

			<div class="form-response-messages"></div>

			<div class="form-group">
				<?php wp_nonce_field( 'instructor_register', 'instructor_register_nonce' ); ?>
				<input type="hidden" name="action" value="edumall_instructor_register">
				<button type="submit"
				        class="button form-submit"><?php esc_html_e( 'Sign Up & Apply', 'edumall' ); ?></button>
			</div>

			<div class="popup-form-footer">
				<?php printf( esc_html__( 'Already have an account? %sLog in%s', 'edumall' ), '<a href="#" class="open-popup-login link-transition-02">', '</a>' ); ?>
			</div>
		</form>
	<?php else: ?>
		<?php if ( Edumall_Tutor::instance()->is_pending_instructor() ) : ?>
			<div class="popup-form-footer">
				<p><?php esc_html_e( 'Please waiting, we are processing your request.', 'edumall' ); ?></p>
			</div>
		<?php elseif ( Edumall_Tutor::instance()->is_instructor() ) : ?>
			<div class="popup-form-footer">
				<p><?php esc_html_e( 'You are an instructor.', 'edumall' ); ?></p>

				<?php
				Edumall_Templates::render_button( [
					'link' => [
						'url' => tutor_utils()->profile_url(),
					],
					'text' => esc_html__( 'Go to Dashboard', 'edumall' ),
				] );
				?>
			</div>
		<?php else : ?>
			<form id="edumall-apply-instructor-form" class="edumall-apply-instructor-form" method="post">
				<div class="form-response-messages"></div>

				<div class="form-group">
					<?php wp_nonce_field( 'apply_instructor', 'apply_instructor_nonce' ); ?>
					<input type="hidden" name="action" value="edumall_apply_instructor">
					<button type="submit"
					        class="button form-submit"><?php esc_html_e( 'Apply to become an instructor', 'edumall' ); ?></button>
				</div>
			</form>
		<?php endif; ?>
	<?php endif; ?>
</div>
