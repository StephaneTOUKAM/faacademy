<?php
/**
 * Template for displaying left navigation for student/instructor dashboard page.
 *
 * @author  ThemeMove
 * @package EduMall
 * @since   1.3.1
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$dashboard_page_slug = '';
$dashboard_page_name = '';
if ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] ) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sub pages.
 */
if ( isset( $wp_query->query_vars['tutor_dashboard_sub_page'] ) && $wp_query->query_vars['tutor_dashboard_sub_page'] ) {
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
	if ( $dashboard_page_slug ) {
		$dashboard_page_name = $dashboard_page_slug . '/' . $dashboard_page_name;
	}
}
?>
<div id="tutor-dashboard-left-menu" class="tutor-dashboard-left-menu">
	<div id="dashboard-nav-wrapper" class="dashboard-nav-wrapper">
		<div class="dashboard-nav-header">
			<div class="dashboard-header-toggle-menu dashboard-header-close-menu">
				<span class="fal fa-times"></span>
			</div>
			<?php
			$branding_args = [
				'reverse_scheme' => true,
			];
			?>
			<?php edumall_load_template( 'branding', null, $branding_args ); ?>
		</div>
		<div class="dashboard-nav-content">
			<div class="dashboard-nav-content-inner">
				<ul class="tutor-dashboard-permalinks">
					<?php
					$dashboard_pages = tutils()->tutor_dashboard_nav_ui_items();

					foreach ( $dashboard_pages as $dashboard_key => $dashboard_page ) {
						$menu_title = $dashboard_page;
						$menu_link  = tutils()->get_tutor_dashboard_page_permalink( $dashboard_key );
						$separator  = false;
						if ( is_array( $dashboard_page ) ) {
							$menu_title = tutils()->array_get( 'title', $dashboard_page );

							/**
							 * Add new menu item property "url" for custom link
							 *
							 * @since v 1.5.5
							 */
							if ( isset( $dashboard_page['url'] ) ) {
								$menu_link = $dashboard_page['url'];
							}

							if ( isset( $dashboard_page['type'] ) && $dashboard_page['type'] == 'separator' ) {
								$separator = true;
							}
						}

						if ( $separator ) {
							echo '<li class="tutor-dashboard-menu-divider"></li>';
							if ( $menu_title ) {
								echo "<li class='tutor-dashboard-menu-divider-header'>{$menu_title}</li>";
							}
						} else {
							$li_class = "tutor-dashboard-menu-{$dashboard_key}";
							if ( $dashboard_key === 'index' ) {
								$dashboard_key = '';
							}
							$active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';
							echo "<li class='{$li_class}  {$active_class}'><a href='" . $menu_link . "'> {$menu_title} </a> </li>";
						}
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
