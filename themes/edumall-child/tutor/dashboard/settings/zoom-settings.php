<?php
/**
 * @package       Edumall/TutorLMS/Templates
 *
 * @theme-since   1.3.5
 * @theme-version 2.4.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! Edumall_Tutor::instance()->is_instructor() || ! Edumall_Tutor_Zoom::instance()->is_activate() ) {
	return;
}
?>
<h3><?php esc_html_e( 'Settings', 'edumall' ) ?></h3>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', [ 'active_setting_nav' => 'zoom' ] ); ?>
	</div>

	<div class="dashboard-content-box dashboard-content-box-small">
		<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Setup your Zoom Integration', 'edumall' ); ?></h4>
		<p><?php esc_html_e( 'Visit your Zoom account and fetch the API key to connect Zoom with your eLearning website. Create an app on Zoom by following this', 'edumall' ); ?>
			<a href="https://marketplace.zoom.us/develop/create" target="_blank"
			   class="link-transition-02"> <?php esc_html_e( 'link', 'edumall' ); ?></a></p>
		<div class="tutor-zoom-api-container">
			<form id="tutor-zoom-settings" action="">
				<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
				<input type="hidden" name="action" value="tutor_save_zoom_api">
				<div class="tutor-zoom-form-container">
					<div class="input-area">
						<div class="tutor-form-group">
							<label for="tutor_zoom_api_key"><?php esc_html_e( 'API Key', 'edumall' ); ?></label>
							<input type="text" id="tutor_zoom_api_key"
							       name="<?php echo Edumall_Tutor_Zoom::API_KEY; ?>[api_key]"
							       value="<?php echo Edumall_Tutor_Zoom::instance()->get_api( 'api_key' ); ?>"
							       placeholder="<?php esc_html_e( 'Enter Your Zoom Api Key', 'edumall' ); ?>"/>
						</div>
						<div class="tutor-form-group">
							<label for="tutor_zoom_api_secret"><?php esc_html_e( 'Secret Key', 'edumall' ); ?></label>
							<input type="text" id="tutor_zoom_api_secret"
							       name="<?php echo Edumall_Tutor_Zoom::API_KEY; ?>[api_secret]"
							       value="<?php echo Edumall_Tutor_Zoom::instance()->get_api( 'api_secret' ); ?>"
							       placeholder="<?php esc_html_e( 'Enter Your Zoom Secret Key', 'edumall' ); ?>"/>
						</div>
						<div class="tutor-zoom-button-container">
							<button type="submit" id="save-changes"
							        class="button button-primary"><?php esc_html_e( 'Save Changes', 'edumall' ); ?></button>
							<button type="button" id="check-zoom-api-connection"
							        class="button"><?php esc_html_e( 'Check API Connection', 'edumall' ); ?></button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
