<?php
/**
 * Template for displaying student Public Profile
 *
 * @author        Themeum
 * @url https://themeum.com
 * @package       TutorLMS/Templates
 * @since         1.0.0
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$user_name = sanitize_text_field( get_query_var( 'tutor_student_username' ) );
$sub_page  = sanitize_text_field( get_query_var( 'profile_sub_page' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );

if ( empty( $get_user ) ) {
	return;
}

$user_id = $get_user->ID;

global $wp_query;

$profile_sub_page = '';
if ( isset( $wp_query->query_vars['profile_sub_page'] ) && $wp_query->query_vars['profile_sub_page'] ) {
	$profile_sub_page = $wp_query->query_vars['profile_sub_page'];
}
?>
	<div class="page-content">
		<div class="container small-gutter">
			<div class="row">
				<div class="page-main-content">
					<div class="tutor-dashboard-content">
						<?php
						if ( $sub_page ) {
							tutor_load_template( 'profile.' . $sub_page, compact( 'get_user' ) );
						} else {
							tutor_load_template( 'profile.bio', compact( 'get_user' ) );
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer();
