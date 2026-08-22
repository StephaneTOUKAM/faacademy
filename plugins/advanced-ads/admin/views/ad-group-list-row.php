<?php
/**
 * Template for a single row in the group list
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @var Advanced_Ads_Group       $group              Ad group object.
 * @var Advanced_Ads_Groups_List $this               Groups list table object.
 * @var string                   $hints_html         Hints markup.
 * @var string                   $type_name          Group type name.
 * @var string                   $missing_type_error Contains an error message if the group type is missing.
 */
?><tr class="advads-group-row">
	<td class="column-group-type">
		<div class="advads-form-type">
			<?php if ( ! $missing_type_error ) : ?>
				<img src="<?php echo esc_url( $this->types[ $group->type ]['image'] ); ?>" alt="<?php echo esc_attr( $type_name ); ?>">
			<?php endif; ?>
			<p class="advads-form-description">
				<strong><?php echo esc_html( $type_name ); ?></strong>
			</p>
		</div>
	</td>
	<td>
		<div class="advads-table-name">
			<a class="row-title" href="#modal-group-edit-<?php echo absint( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></a>
		</div>
		<?php
		// escaping done by the function.
		// phpcs:ignore
		echo $this->render_action_links( $group );

		// edit form modal
		ob_start();
		require ADVADS_BASE_PATH . 'admin/views/ad-group-list-form-row.php';
		$modal_content = ob_get_clean();

		new Advanced_Ads_Modal( array(
			'modal_slug'       => 'group-edit-' . $group->id,
			'modal_content'    => $modal_content,
			'modal_title'      => __( 'Edit', 'advanced-ads' ) . ' ' . $group->name,
			'close_action'     => __( 'Save', 'advanced-ads' ) . ' ' . $group->name,
			'close_form'       => 'advads-form-groups',
			'close_validation' => 'advads_group_edit_submit',
		) );

		ob_start();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the output is already escaped
		echo $hints_html;
		?>
		<div class="advads-usage">
			<h2><?php esc_html_e( 'shortcode', 'advanced-ads' ); ?></h2>
				<code><input type="text" onclick="this.select();" value='[the_ad_group id="<?php echo absint( $group->id ); ?>"]' readonly /></code>
			<h2><?php esc_html_e( 'template (PHP)', 'advanced-ads' ); ?></h2>
				<code><input type="text" onclick="this.select();" value="the_ad_group(<?php echo absint( $group->id ); ?>);" readonly /></code>
		</div>
		<?php
		$modal_content = ob_get_clean();
		new Advanced_Ads_Modal( array(
			'modal_slug'       => $group->id . '-usage',
			'modal_content'    => $modal_content,
			'modal_title'      => __( 'Usage', 'advanced-ads' ),
		) );

		if ( $missing_type_error ) :
			?>
			<p class="advads-notice-inline advads-error"><?php echo esc_html( $missing_type_error ); ?></p>
			<?php
		endif;
		?>
	</td>
	<td>
		<ul>
			<li><strong>
			<?php
			/*
			 * translators: %s is the name of a group type
			 */
			printf( esc_html__( 'Type: %s', 'advanced-ads' ), esc_html( $type_name ) );
			?>
			</strong></li>
			<li>
			<?php
			/*
			 * translators: %s is the ID of an ad group
			 */
			printf( esc_attr__( 'ID: %s', 'advanced-ads' ), absint( $group->id ) );
			?>
			</li>
		</ul>
	</td>
	<td class="advads-ad-group-list-ads"><?php $this->render_ads_list( $group ); ?></td>
</tr>
