<?php
/**
 * Renders the ad group page in WP Admin
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @var WP_List_Table|false $wp_list_table the groups list table
 * @var WP_Taxonomy         $tax ad group taxonomy
 * @var Advanced_Ads_Groups_List $ad_groups_list
 * @var array[]             $group_types
 * @var array[]             $group_types_premium
 * @var bool                $is_search true if a group is searched.
 */
?>
<div class="wrap">
<?php
// create new group.
if ( isset( $_REQUEST['advads-group-add-nonce'] ) ) {
	$create_result = $ad_groups_list->create_group();
	// display error message.
	if ( is_wp_error( $create_result ) ) {
		// potential error comes from WP_Error and is no user input.
		// phpcs:ignore
		$error_string = $create_result->get_error_message();
		echo '<div class="notice error inline"><p>' . esc_html( $error_string ) . '</p></div>';
	} else {
		echo '<div class="notice inline"><p>' . esc_html__( 'Ad Group successfully created', 'advanced-ads' ) . '</p></div>';
	}
}
// save updated groups.
if ( isset( $_REQUEST['advads-group-update-nonce'] ) ) {
	$udpate_result = $ad_groups_list->update_groups();
	// display error message.
	if ( is_wp_error( $udpate_result ) ) {
		$error_string = $udpate_result->get_error_message();
		// potential error comes from WP_Error and is no user input.
		// phpcs:ignore
		echo '<div id="message" class="error inline"><p>' . $error_string . '</p></div>';
	} else {
		echo '<div id="message" class="updated inline"><p>' . esc_html__( 'Ad Groups successfully updated', 'advanced-ads' ) . '</p></div>';
	}
}
?>
</div>
<div class="wrap nosubsub">
	<h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
	<?php ob_start(); ?>
	<p>
		<?php
		esc_attr_e( 'Ad Groups are a very flexible method to bundle ads. You can use them to display random ads in the frontend or run split tests, but also just for informational purposes. Not only can an Ad Groups have multiple ads, but an ad can belong to multiple ad groups.', 'advanced-ads' );
		?>
		<a href="<?php echo esc_url( ADVADS_URL ) . 'manual/ad-groups/?utm_source=advanced-ads&utm_medium=link&utm_campaign=groups'; ?>" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
	</p>
	<?php
	require ADVADS_BASE_PATH . 'admin/views/group-form.php';
	$modal_slug = 'group-new';
	new Advanced_Ads_Modal( array(
		'modal_slug'       => $modal_slug,
		'modal_content'    => ob_get_clean(),
		'modal_title'      => __( 'New Ad Group', 'advanced-ads' ),
		'close_action'     => __( 'Save New Group', 'advanced-ads' ),
		'close_form'       => 'advads-group-new-form',
		'close_validation' => 'advads_validate_new_form',
	) );
	?>
	<?php if ( isset( $message ) ) : ?>
		<div id="message" class="updated"><p><?php echo esc_html( $message ); ?></p></div>
		<?php
		$_SERVER['REQUEST_URI'] = esc_url( remove_query_arg( [ 'message' ], wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	endif;
	?>
	<div id="ajax-response"></div>

	<div id="col-container">
		<div class="col-wrap">
			<div class="tablenav top <?php echo $is_search ? '' : 'hidden advads-toggle-with-filters-button'; ?>" style="padding-bottom: 20px;">
				<?php
				if ( $is_search ) {
					printf( '<span class="subtitle" style="float:left;">' . __( 'Search results for: %s' ) . '</span>', '<strong>' . esc_html( wp_unslash( $_REQUEST['s'] ) ) . '</strong>' );
				}
				?>
				<form class="search-form" action="" method="get">
					<input type="hidden" name="page" value="advanced-ads-groups"/>
					<?php
					$wp_list_table->search_box( $tax->labels->search_items, 'tag' );
					?>
				</form>
			</div>
			<div id="advads-ad-group-list">
				<form action="" method="post" id="advads-form-groups" class="advads-form-groups">
					<?php wp_nonce_field( 'update-advads-groups', 'advads-group-update-nonce' ); ?>
					<table class="wp-list-table widefat fixed advads-table">
						<?php $ad_groups_list->render_header(); ?>
						<?php
						if ( count( $ad_groups_list->groups ) ) :
							$ad_groups_list->render_rows();
						else :
							?>
							<tr class="advads-group-row"><td colspan="4"><?php esc_html_e( 'No Ad Group found', 'advanced-ads' ); ?></td><tr>
							<?php
						endif;
						?>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
// trigger the group form when no groups exist and we are not currently searching
if ( ! count( $ad_groups_list->groups ) && ! $is_search ) :
	?>
	<script>
		window.location.hash = '#modal-<?php echo esc_html( $modal_slug ); ?>';
	</script>
	<?php
endif;
