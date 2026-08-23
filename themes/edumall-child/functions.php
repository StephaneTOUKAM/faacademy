<?php
defined('ABSPATH') || exit;

/**
 * Enqueue child scripts
 */
if (! function_exists('edumall_child_enqueue_scripts')) {
	function edumall_child_enqueue_scripts()
	{
		wp_enqueue_style('edumall-child-style', get_stylesheet_directory_uri() . '/style.css');
	}
}
add_action('wp_enqueue_scripts', 'edumall_child_enqueue_scripts', 15);

/**
 * WPML: get current language code, or null if WPML is inactive.
 */
if (! function_exists('edumall_wpml_current_language')) {
	function edumall_wpml_current_language()
	{
		if (! defined('ICL_SITEPRESS_VERSION')) {
			return null;
		}

		return apply_filters('wpml_current_language', null);
	}
}

/**
 * WPML: filter an array of course IDs down to the current language.
 * Used to fix instructor dashboard tabs that fetch course IDs via raw SQL,
 * which bypasses WPML's automatic WP_Query language filtering.
 */
if (! function_exists('edumall_wpml_filter_course_ids')) {
	function edumall_wpml_filter_course_ids($course_ids)
	{
		$current_language = edumall_wpml_current_language();

		if (empty($current_language) || empty($course_ids)) {
			return $course_ids;
		}

		$course_post_type = tutor()->course_post_type;

		return array_values(array_filter((array) $course_ids, function ($course_id) use ($current_language, $course_post_type) {
			$course_language = apply_filters('wpml_element_language_code', null, [
				'element_id'   => $course_id,
				'element_type' => 'post_' . $course_post_type,
			]);

			return empty($course_language) || $course_language === $current_language;
		}));
	}
}

/**
 * WPML: filter a list of items down to the current language, using a callback
 * to extract each item's related course ID.
 */
if (! function_exists('edumall_wpml_filter_items_by_course_id')) {
	function edumall_wpml_filter_items_by_course_id($items, $course_id_callback)
	{
		$current_language = edumall_wpml_current_language();

		if (empty($current_language) || empty($items)) {
			return $items;
		}

		$course_post_type = tutor()->course_post_type;

		return array_values(array_filter((array) $items, function ($item) use ($course_id_callback, $current_language, $course_post_type) {
			$course_id = call_user_func($course_id_callback, $item);

			if (empty($course_id)) {
				return true;
			}

			$course_language = apply_filters('wpml_element_language_code', null, [
				'element_id'   => $course_id,
				'element_type' => 'post_' . $course_post_type,
			]);

			return empty($course_language) || $course_language === $current_language;
		}));
	}
}

/**
 * WPML: get an instructor's bio for a given language.
 * Falls back to the untranslated "_tutor_profile_bio" value when no
 * translation has been saved yet for that language, so nothing breaks for
 * instructors who never filled one in.
 */
if (! function_exists('edumall_wpml_get_instructor_bio')) {
	function edumall_wpml_get_instructor_bio($user_id, $language_code = null)
	{
		$default_bio = get_user_meta($user_id, '_tutor_profile_bio', true);

		if (empty($language_code)) {
			$language_code = edumall_wpml_current_language();
		}

		edumall_bio_debug_log('GET user=' . $user_id . ' detected_lang=' . var_export($language_code, true) . ' uri=' . ($_SERVER['REQUEST_URI'] ?? ''));

		if (empty($language_code)) {
			return $default_bio;
		}

		$default_language = apply_filters('wpml_default_language', null);

		if ($language_code === $default_language) {
			return $default_bio;
		}

		$translated_bio = get_user_meta($user_id, '_tutor_profile_bio_' . $language_code, true);

		return ('' !== $translated_bio) ? $translated_bio : $default_bio;
	}
}

/**
 * TEMPORARY diagnostic logging for the bio-per-language bug investigation.
 * Writes to its own file (not the noisy shared debug.log). Remove once the
 * root cause is confirmed and fixed.
 */
if (! function_exists('edumall_bio_debug_log')) {
	function edumall_bio_debug_log($message)
	{
		error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, WP_CONTENT_DIR . '/edumall-bio-debug.log');
	}
}

/**
 * WPML: save an instructor's bio for the language the dashboard is
 * currently displayed in, so the front-end shows each language's own bio
 * instead of whichever one was typed last.
 *
 * The default-language bio keeps using the plain "_tutor_profile_bio" key
 * (already saved by the site's customized TUTOR\Student::update_profile(),
 * see wp-content/plugins/tutor/classes/Student.php); this only adds a
 * per-language copy for secondary languages.
 *
 * Note: the dashboard settings form posts tutor_action=tutor_profile_edit
 * and a "tutor_profile_bio" field (no leading underscore) — that's the
 * handler actually wired up on this site, not Tutor's stock
 * tutor_profile_update_by_wp/User::profile_update().
 */
if (! function_exists('edumall_wpml_save_instructor_bio')) {
	function edumall_wpml_save_instructor_bio($user_id)
	{
		if (! defined('ICL_SITEPRESS_VERSION')) {
			return;
		}

		if (tutor_utils()->array_get('tutor_action', $_POST) !== 'tutor_profile_edit') {
			return;
		}

		if (! isset($_POST['tutor_profile_bio'])) {
			return;
		}

		$language_code     = edumall_wpml_current_language();
		$default_language  = apply_filters('wpml_default_language', null);
		$posted_bio_snippet = substr(wp_strip_all_tags((string) $_POST['tutor_profile_bio']), 0, 40);

		edumall_bio_debug_log(
			'SAVE user=' . $user_id
			. ' detected_lang=' . var_export($language_code, true)
			. ' default_lang=' . var_export($default_language, true)
			. ' uri=' . ($_SERVER['REQUEST_URI'] ?? '')
			. ' referer=' . ($_SERVER['HTTP_REFERER'] ?? '')
			. ' posted_bio_start="' . $posted_bio_snippet . '"'
		);

		if (empty($language_code)) {
			return;
		}

		if ($language_code === $default_language) {
			return;
		}

		$bio = wp_kses_post(wp_unslash($_POST['tutor_profile_bio']));

		update_user_meta($user_id, '_tutor_profile_bio_' . $language_code, $bio);
	}
}
add_action('profile_update', 'edumall_wpml_save_instructor_bio');

/**
 * Defensive fix for a crash in the site's customized
 * TUTOR\Student::update_profile() (wp-content/plugins/tutor/classes/Student.php,
 * hand-modified by a previous developer to add street/town/postal_code/CV/
 * diplomas/topics fields to the instructor dashboard profile form).
 *
 * That method calls count($_POST['tax_input']['course-topic']) without
 * checking it's set. When the instructor submits the profile form without
 * picking any "speciality" topic, that key is simply absent from $_POST, so
 * count(null) fatals the whole page request (PHP 8+).
 *
 * Rather than patching the vendor plugin file directly (which a future
 * Tutor LMS update would silently overwrite, losing the fix), this runs
 * before Tutor's own template_redirect handler and pre-fills the missing
 * key with a safe empty array.
 */
if (! function_exists('edumall_fix_tutor_profile_form_crash')) {
	function edumall_fix_tutor_profile_form_crash()
	{
		if (tutor_utils()->array_get('tutor_action', $_POST) !== 'tutor_profile_edit') {
			return;
		}

		if (! isset($_POST['tax_input']) || ! is_array($_POST['tax_input'])) {
			$_POST['tax_input'] = [];
		}

		if (! isset($_POST['tax_input']['course-topic']) || ! is_array($_POST['tax_input']['course-topic'])) {
			$_POST['tax_input']['course-topic'] = [];
		}
	}
}
// Priority 5: must run before Student::update_profile(), registered at the default priority 10.
add_action('template_redirect', 'edumall_fix_tutor_profile_form_crash', 5);

/**
 * WPML: make the Tutor dashboard (My Profile, My Courses, Create Course...)
 * actually reachable in every language, not just the one whose page is
 * stored in the "tutor_dashboard_page_id" option.
 *
 * TUTOR\Rewrite_Rules::add_rewrite_rules() (wp-content/plugins/tutor/classes/Rewrite_Rules.php)
 * builds dashboard sub-page rewrite rules using ONLY that single page's
 * slug — it has no WPML awareness. WPML duplicates the Dashboard page once
 * per language (e.g. /dashboard/, /dashboard-en/, /dashboard-de/), but only
 * the one Tutor's option happens to point at ever gets a matching rewrite
 * rule; every other language 404s on every dashboard sub-page.
 *
 * This adds the same rules for the sibling-language dashboard pages,
 * without touching the vendor plugin file. Runs after Tutor's own
 * generate_rewrite_rules callback (registered at the default priority 10)
 * so $wp_rewrite->rules already contains its base rules to extend.
 */
if (! function_exists('edumall_wpml_tutor_dashboard_rewrite_rules')) {
	function edumall_wpml_tutor_dashboard_rewrite_rules($wp_rewrite)
	{
		if (! defined('ICL_SITEPRESS_VERSION') || ! function_exists('tutor_utils') || ! class_exists('\TUTOR\Rewrite_Rules')) {
			return;
		}

		$dashboard_page_id = (int) tutor_utils()->get_option('tutor_dashboard_page_id');

		if (! $dashboard_page_id) {
			return;
		}

		$trid = apply_filters('wpml_element_trid', null, $dashboard_page_id, 'post_page');

		if (! $trid) {
			return;
		}

		$translations = apply_filters('wpml_get_element_translations', null, $trid, 'post_page');

		if (! is_array($translations)) {
			return;
		}

		$dashboard_pages = tutor_utils()->tutor_dashboard_permalinks();
		$new_rules       = array();

		foreach ($translations as $translation) {
			$translated_page_id = (int) $translation->element_id;

			if (! $translated_page_id || $translated_page_id === $dashboard_page_id) {
				continue; // Tutor's own rule already covers the page its option points to.
			}

			$slug = get_post_field('post_name', $translated_page_id);

			if (! $slug) {
				continue;
			}

			foreach ($dashboard_pages as $dashboard_key => $dashboard_page) {
				$new_rules["({$slug})/{$dashboard_key}/?$"]        = 'index.php?pagename=' . $wp_rewrite->preg_index(1) . '&tutor_dashboard_page=' . $dashboard_key;
				$new_rules["({$slug})/{$dashboard_key}/(.+?)/?$"] = 'index.php?pagename=' . $wp_rewrite->preg_index(1) . '&tutor_dashboard_page=' . $dashboard_key . '&tutor_dashboard_sub_page=' . $wp_rewrite->preg_index(2);
			}
		}

		if ($new_rules) {
			$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
		}
	}
}
add_action('generate_rewrite_rules', 'edumall_wpml_tutor_dashboard_rewrite_rules', 20);


require_once(get_stylesheet_directory() . '/widgets/tutor/popular-courses.php');
require_once(get_stylesheet_directory() . '/widgets/tutor/course-themes.php');
require_once(get_stylesheet_directory() . '/widgets/tutor/course-subjects.php');
require_once(get_stylesheet_directory() . '/widgets/tutor/subjects.php');
require_once(get_stylesheet_directory() . '/widgets/tutor/subjects.php');
require_once(get_stylesheet_directory() . '/widgets/tutor/class-course-layered-nav-base.php');
require_once(get_stylesheet_directory() . '/widgets/class-widget-init.php');
require_once(get_stylesheet_directory() . '/widgets/class-widget-base.php');
require_once(get_stylesheet_directory() . '/framework/tutor/class-tutor.php');



function wpc_cpt()
{

	/* Property */
	$labels = array(
		'name'                => _x('Recently review Courses', 'Post Type General Name', 'textdomain'),
		'singular_name'       => _x('Recently Review Course', 'Post Type Singular Name', 'textdomain'),
		'menu_name'           => __('Recently Review Course', 'textdomain'),
		'name_admin_bar'      => __('Recently Review Course', 'textdomain'),
		'parent_item_colon'   => __('Parent Item:', 'textdomain'),
		'all_items'           => __('All Items', 'textdomain'),
		'add_new_item'        => __('Add New Item', 'textdomain'),
		'add_new'             => __('Add New', 'textdomain'),
		'new_item'            => __('New Item', 'textdomain'),
		'edit_item'           => __('Edit Item', 'textdomain'),
		'update_item'         => __('Update Item', 'textdomain'),
		'view_item'           => __('View Item', 'textdomain'),
		'search_items'        => __('Search Item', 'textdomain'),
		'not_found'           => __('Not found', 'textdomain'),
		'not_found_in_trash'  => __('Not found in Trash', 'textdomain'),
	);
	$rewrite = array(
		'slug'                => _x('cours', 'cours', 'textdomain'),
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => false,
	);
	$args = array(
		'label'               => __('cours', 'textdomain'),
		'description'         => __('Recently Review Course', 'textdomain'),
		'labels'              => $labels,
		'supports'            => array('title', 'editor', 'thumbnail'),
		'taxonomies'          => array('cours_type'),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-admin-home',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'query_var'           => 'cours',
		'rewrite'             => $rewrite,
		'capability_type'     => 'page',
	);
	register_post_type('cours', $args);
}

add_action('init', 'wpc_cpt', 10);




/* Fire our meta box setup function on the post editor screen. */
add_action('load-post.php', 'cours_post_meta_boxes_setup');
add_action('load-post-new.php', 'cours_post_meta_boxes_setup');
/* Meta box setup function. */
function cours_post_meta_boxes_setup()
{

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action('add_meta_boxes', 'cours_add_post_meta_boxes');
	/* Save post meta on the 'save_post' hook. */
	add_action('save_post', 'cours_save_post_class_meta', 10, 2);
	add_action('save_post', 'user_save_post_class_meta', 10, 2);
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function cours_add_post_meta_boxes()
{

	add_meta_box(
		'cours-post-class',      // Unique ID
		esc_html__('Course ID', 'example'),    // Title
		'cours_post_class_meta_box',   // Callback function
		'cours',         // Admin page (or post type)
		'side',         // Context
		'default'         // Priority
	);
	add_meta_box(
		'user-post-class',      // Unique ID
		esc_html__('User ID', 'example'),    // Title
		'user_post_class_meta_box',   // Callback function
		'cours',         // Admin page (or post type)
		'side',         // Context
		'default'         // Priority
	);
}
/* Display the post meta box. */
function cours_post_class_meta_box($post)
{ ?>

	<?php wp_nonce_field(basename(__FILE__), 'cours_post_class_nonce'); ?>

	<p>
		<label for="cours-post-class"><?php _e("The name of the course consulted.", 'example'); ?></label>
		<br />
		<input class="widefat" type="text" name="cours-post-class" id="cours-post-class" value="<?php echo get_post(esc_attr(get_post_meta($post->ID, 'cours_post_class', true)))->post_title; ?>" size="30" />
		<a href="<?php echo get_the_permalink(esc_attr(get_post_meta($post->ID, 'cours_post_class', true))); ?>" target="_blank">Click here to see the course</a>
	</p>
<?php
}
function user_post_class_meta_box($post)
{ ?>

	<?php wp_nonce_field(basename(__FILE__), 'user_post_class_nonce'); ?>

	<p>
		<label for="user-post-class"><?php _e("The user  who consulted", 'example'); ?></label>
		<br />
		<input class="widefat" type="text" name="user-post-class" id="user-post-class" value="<?php echo  get_userdata(esc_attr(get_post_meta($post->ID, 'user_post_class', true)))->first_name; ?>" size="30" />
	</p>
<?php
}


/* Save the meta box’s post metadata. */
function cours_save_post_class_meta($post_id, $post)
{


	/* Verify the nonce before proceeding. */
	if (!isset($_POST['cours_post_class_nonce']) || !wp_verify_nonce($_POST['cours_post_class_nonce'], basename(__FILE__)))
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object($post->post_type);

	/* Check if the current user has permission to edit the post. */
	if (!current_user_can($post_type->cap->edit_post, $post_id))
		return $post_id;

	/* Get the posted data and sanitize it for use as an HTML class. */
	$new_meta_value = (isset($_POST['cours-post-class']) ? sanitize_html_class($_POST['cours-post-class']) : ’);

	/* Get the meta key. */
	$meta_key = 'cours_post_class';

	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta($post_id, $meta_key, true);

	/* If a new meta value was added and there was no previous value, add it. */
	if ($new_meta_value && ’ == $meta_value)
		add_post_meta($post_id, $meta_key, $new_meta_value, true);

	/* If the new meta value does not match the old value, update it. */
	elseif ($new_meta_value && $new_meta_value != $meta_value)
		update_post_meta($post_id, $meta_key, $new_meta_value);

	/* If there is no new meta value but an old value exists, delete it. */
	elseif (’ == $new_meta_value && $meta_value)
		delete_post_meta($post_id, $meta_key, $meta_value);
}

function user_save_post_class_meta($post_id, $post)
{

	/* Verify the nonce before proceeding. */
	if (!isset($_POST['user_post_class_nonce']) || !wp_verify_nonce($_POST['user_post_class_nonce'], basename(__FILE__)))
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object($post->post_type);

	/* Check if the current user has permission to edit the post. */
	if (!current_user_can($post_type->cap->edit_post, $post_id))
		return $post_id;

	/* Get the posted data and sanitize it for use as an HTML class. */
	$new_meta_value = (isset($_POST['user-post-class']) ? sanitize_html_class($_POST['user-post-class']) : ’);

	/* Get the meta key. */
	$meta_key = 'user_post_class';

	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta($post_id, $meta_key, true);

	/* If a new meta value was added and there was no previous value, add it. */
	if ($new_meta_value && ’ == $meta_value)
		add_post_meta($post_id, $meta_key, $new_meta_value, true);

	/* If the new meta value does not match the old value, update it. */
	elseif ($new_meta_value && $new_meta_value != $meta_value)
		update_post_meta($post_id, $meta_key, $new_meta_value);

	/* If there is no new meta value but an old value exists, delete it. */
	elseif (’ == $new_meta_value && $meta_value)
		delete_post_meta($post_id, $meta_key, $meta_value);
}



function retrieve_id()
{


	$cours_id = get_the_ID();
	// $cours_link=get_permalink();
	$user_id                   = get_current_user_id();
	$user                      = get_user_by('ID', $user_id);
	$title						= get_the_title();


	$args = array(
		'post_type' => 'cours',
		'meta_query'	=> array(
			'relation'		=> 'AND',
			array(
				'key'	 	=> 'cours_post_class',
				'value'	  	=> $cours_id,
				'compare' 	=> '=',
			),
			array(
				'key'	  	=> 'user_post_class',
				'value'	  	=> $user_id,
				'compare' 	=> '=',
			)
		)
	);
	$my_query = new WP_Query($args);

	if ($my_query->have_posts()) {
		$my_query->the_post();
	} else {
		if (is_bool($user)) {
		} else {

			$post_id = wp_insert_post(array(

				'post_title'    	=> $user->display_name . "  review  " . $title,
				'post_status'  	 	=> 'publish',
				'post_author'  	 	=> $user->display_name,
				'post_type'			=> 'cours',

			));


			add_post_meta($post_id, 'cours_post_class', $cours_id);
			add_post_meta($post_id, 'user_post_class', $user_id);
		}
	};
}
add_action('tutor_course/single/before/content', 'retrieve_id');



function set_views($post_ID)
{
	$key = 'views';
	$count = get_post_meta($post_ID, $key, true); //retrieves the count

	if ($count == '') { //check if the post has ever been seen

		//set count to 0
		$count = 0;

		//just in case
		delete_post_meta($post_ID, $key);

		//set number of views to zero
		add_post_meta($post_ID, $key, '0');
	} else { //increment number of views
		$count++;
		update_post_meta($post_ID, $key, $count);
	}
}

//keeps the count accurate by removing prefetching
remove_action('tutor_course/single/before/content', 'adjacent_posts_rel_link_wp_head', 10, 0);


// Debut du code 
function savecourse()
{
	$course_id  = tutor_utils()->get_post_id(get_the_ID());
	$product_id = tutor_utils()->get_course_product_id($course_id);

	$key = '_sale_price';

	update_post_meta($product_id, $key, $_POST['_sale_price']);
}
add_action('save_tutor_course', 'savecourse', 10, 2);

add_filter('woocommerce_payment_complete_order_status', 'rfvc_update_order_status', 10, 2);
function rfvc_update_order_status($order_status, $order_id)
{
	$order = new WC_Order($order_id);
	if ('processing' == $order_status && ('on-hold' == $order->status || 'pending' == $order->status || 'failed' == $order->status)) {
		return 'wc-completed';
	}

	$billing_address = $order->get_formatted_billing_address(); // for printing or displaying on web page
	$shipping_address = $order->get_formatted_shipping_address();
	$email = $order->get_data()['billing']['email'];
	$name = $order->get_data()['billing']['first_name'] . ' ' . $order->get_data()['billing']['last_name'];
	$billing_phone = $order->get_data()['billing']['phone'];
	$date = date('d.m.y');
	$data   = '';
	$data   .= "<!DOCTYPE html>
		<html lang='en'>
		<head>
			<meta charset='UTF-8'>
			<meta http-equiv='X-UA-Compatible' content='IE=edge'>
			<meta name='viewport' content='width=device-width, initial-scale=1.0'>
			<title>Facture</title>
			<style>
				html{
					font-family: Arial, Helvetica, sans-serif;
					font-size: 12px;
				}
		
				h1{
					color: rgb(224, 224, 224);
					font-weight: 400;
					font-size: 40px;
				}
		
				h2{
					font-size: 16px;
				}
		
				.entete{
					display: flex;
					justify-content: space-between;
					align-items: center;
					border-bottom: 4px solid #007fc5;
					margin-bottom: 35px;
				}
		
				.color-text{
					color: #007fc5;
				}
		
				.bold-text{
					font-weight: 700;
				}
		
				.first-p{
					font-size: 12px;
					padding: 0px 12px;
					display: block;
				}
		
				.entete img{
					width: 130px;
				}
		
				.entete-corps{
					display: flex;
					justify-content: space-between;
					margin-bottom: 20px;
					padding: 0px 12px;
				}
		
				.surligne span{
					background-color: green;
				}
		
				.corps-entete-droit strong{
					display: inline-block;
					width: 170px;
					text-align: right;
					margin-right: 15px;
					padding-top: 1px;
					padding-bottom: 1px;
				}
				.droit{
					text-align: right;
				}
				.corps-entete-gauche{
					padding-top: 14px;
					line-height: 15px;
				}
				.contraint-box{
					padding: 0px 12px;
				}
		
				.table-faq{
					width: 100%;
					text-align: right;
					border: 0;
					margin-top: 8px;
					margin-bottom: 15px;
					/* border: 1px solid #000; */
				}
				.table-faq td, .table-faq th{
					padding: 7px 5px;
				}
				.table-faq tr th:nth-of-type(2), .table-faq tbody tr td:nth-of-type(2){
					text-align: left;
					max-width: 60%;
				}
				.table-faq tr th{
					background-color: rgb(185, 185, 185);
				}
				.table-faq th:nth-child(1),
				.table-faq th:nth-child(2),
				.table-faq th:nth-child(3){
					background: #007fc5;
					color: #FFF;
				}
		
				.table-faq tbody tr td{
					border-bottom: 1px solid lightgray;
				}
				.table-faq tbody tr:last-of-type td{
					border-bottom: 2px solid #000;
				}
				tfoot{
					font-weight: 600;
				}
				tfoot tr:last-of-type td:nth-last-child(1), 
				tfoot tr:last-of-type td:nth-last-child(2){
					background: #007fc5;
					color: #FFF;
				}
		
				.space-p{
					margin-top: 35px;
				}
		
				.signature{
					max-width: 100px;
					max-height: 100px;
					display: block;
				}
		
				.container-infos{
					display: flex;
					justify-content: space-evenly;
					position: relative;
					z-index: 10;
				}
		
				.container-infos p{
					margin: 0;
				}
		
				.container-info{
					text-align: center;
					display: flex;
					align-items: center;
					flex-direction: column;
					margin-top: 15px;
					position: relative;
				}
		
				.container-info img{
					width: 20px;
					height: 20px;
				}
		
				.container-img{
					width: 30px;
					height: 30px;
					padding: 5px;
					border: 1px solid rgb(95, 95, 95);
					border-radius: 50%;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
					margin-bottom: 6px;
					background-color: #FFF;
				}
		
				.last-p{
					position: relative;
				}
		
				.last-p::after{
					content: '';
					width: 100%;
					height: 3px;
					background-color: #007fc5;
					position: absolute;
					top: -72px;
					left: 0;
					z-index: 0;
					display:none;
				}
			</style>
		</head>
		<body>
			<div class='container-principal'>
				<div class='entete' style='justify-content: space-between !important;'>
					<h1 style='width: 100%;'>" . __('INVOICE', 'edumall') . "</h1>
					<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='logo'>
				</div>
				<div class='corps'>
					<span class='color-text bold-text first-p'>F@Academy, Lettenweg 13, 79618 Rheinfelden</span>
					<div class='entete-corps' style='justify-content: space-between;'>
						<div class='corps-entete-gauche' style='width: 72%;'>
							<strong>" . __('Customer', 'edumall') . "</strong>: " . $name . " <br>
							<strong>" . __('Street name', 'edumall') . "</strong>: " . $billing_address . " <br>
						</div>
						<div class='corps-entete-droit'>
							<strong>" . __('Invoice no.', 'edumall') . ":</strong>" . $order_id . "<br>
							<strong>" . __('Invoice date', 'edumall') . ":</strong>" . $date . "<br>
							<div class='surligne'><strong> <span>" . __('Customer no.', 'edumall') . ":</span></strong><span>" . get_current_user_id() . "</span></div>
							<strong>" . __('Contact', 'edumall') . ":</strong>Paul, Fansi<br>
						</div>
					</div>
					<div class='container-data droit'>
						<span>" . $date . "</span>
					</div>
				</div>
				<div class='contraint-box'>
					<h2 class='color-text'>" . __('INVOICE NO.', 'edumall') . " " . $order_id . "</h2>
					<span class='dear-text'>" . __('Dear Sir or Madam', 'edumall') . ",</span>
					<p>" . __('Thank you for your confidence in the', 'edumall') . " <strong>F@Academy</strong>. " . __('We hereby invoice you for the following services', 'edumall') . ":</p>
				</div>
				<table class='table-faq' cellspacing='0' cellpadding='0'>
					<thead>
						<tr>
							<th>" . __('Pos.', 'edumall') . "</th>
							<th>" . __('Description', 'edumall') . "</th>
							<th>" . __('Quantity', 'edumall') . "</th>
							<th>" . __('Unit price', 'edumall') . "</th>
							<th>" . __('Total price', 'edumall') . "</th>
						</tr>
					</thead>
					<tbody>";
	$total_net = 0;
	if (sizeof($order->get_items()) > 0) {
		$i = 0;
		foreach ($order->get_items() as $item_key => $item) {
			$i++;
			$label =    get_the_title(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id);
			$qte = $item->get_quantity();
			$total_net = $total_net + $item->get_subtotal();
			$keys = $i;
			if (get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_type_data", true) && (get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_type_data", true) == "Rehearsal Course")) {
				$data .= "<tr>
					<td>$keys.</th>
					<td>
						<h4>$label</h4>
						<ul>
							<li>" . __('Date', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_date", true) . "</li>
							<li>" . __('Hour', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_hour", true) . "</li>
							<li>" . __('Platform', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_platform", true) . "</li>
							<li>" . __('Link', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_link", true) . "</li>
							<li>" . __('Identifier', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_identifiers", true) . "</li>
						</ul>
					</th>
					<td>X$qte</td>
					<td>" . $item->get_subtotal() . "</th>
					<td>" . $item->get_total() . "</th>
					</tr>";
			} else {
				$data .= "<tr>
					<td>$keys.</th>
					<td>$label</th>
					<td>X$qte</td>
					<td>" . $item->get_subtotal() . "</th>
					<td>" . $item->get_total() . "</th>
					</tr>";
			}
		}
	}
	$data .= "</tbody>
				<tfoot>
					<tr>
						<td colspan='4'>" . __('Total net', 'edumall') . "</td>
						<td>" . $total_net . " €</td>
					</tr>
					<tr>
						<td colspan='3'></td>
						<td style='background: #007fc5;color: white;'>" . __('Total', 'edumall') . "</td>
						<td style='background: #007fc5;color: white;'>" . $total_net . " €</td>
					</tr>
				</tfoot>
				</table>
				<small>" . __('Terms of payment: Payment within 14 days from receipt of invoice without deductions.', 'edumall') . ".</small>
				<p class='space-p'>" . __('If you have any questions, please do not hesitate to contact us at any time', 'edumall') . ".</p>
				<span>" . __('With kind regards', 'edumall') . "</span>
				<img class='signature' src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='signature'>
				<div class='footer' style='position:relative;'>
					<div class='container-infos' style='justify-content: space-evenly;'>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								F@Academy <br>
								Lettenweg 13 <br>
								79618 Rheifelde <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								+4917624537308 <br>
								https://faacademy.de <br>
								info@faacademy.de <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								TARGOBANK <br>
								DE45300209005360594849 <br>
								BIC: CMCIDEDDxxx <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								" . __('General Manager', 'edumall') . ": <br>
								Paul Fansi <br><br>
							</p>
						</div>
					</div>
					<p class='last-p' style='text-align: center; margin: 0; margin-top: 12px;'>" . __('Note : No sales tax according to §19 UStG', 'edumall') . "</p>
					<div style='display:none;content: \"\";width: 100%;height: 3px;background-color: #007fc5;position: absolute;top: 16px;left: 0;z-index:  0;'></div>
				</div>
			</div>
		</body>
		</html>";

	$mailer = WC()->mailer();
	$subject = __('Thanks for your purchase', 'edumall');
	$headers = [];
	$headers[]  = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/html; charset=iso-8859-1";
	$headers[] = "X-Priority: 1 (Higuest)";
	$headers[] = "X-MSMail-Priority: High";
	$headers[] = "Importance: High";
	$email_from_name = tutor_utils()->get_option('email_from_name');
	$from_name = apply_filters('tutor_email_from_name', $email_from_name);
	$headers[] = "From: " . wp_specialchars_decode(esc_html($from_name), ENT_QUOTES) . " <no-reply@smartcodegroup.com>";
	return $order_status;
}


add_action('draft_to_publish', 'publishPostAction', 10, 3);
function publishPostAction($post)
{

	$author = get_userdata($post->post_author);

	if ($post->post_type == "product") {
		$message = "
              Hi " . $author->display_name . ",
               
              Your product, \"" . $post->post_title . "\" has just been approved.
               
              View product: " . get_permalink($post->ID) . "
               
              Thanks";
		$mailer = WC()->mailer();
		$subject = __('Product approved successfully', 'edumall');
		$headers = [];
		$headers[]  = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "X-Priority: 1 (Higuest)";
		$headers[] = "X-MSMail-Priority: High";
		$headers[] = "Importance: High";
		$email_from_name = tutor_utils()->get_option('email_from_name');
		$from_name = apply_filters('tutor_email_from_name', $email_from_name);
		$headers[] = "From: " . wp_specialchars_decode(esc_html($from_name), ENT_QUOTES) . " <no-reply@smartcodegroup.com>";
		$mailer->send($author->user_email, $subject, $message, $headers, '');
	}
}

add_action('publish_to_draft', 'editedPostAction', 10, 3);
function editedPostAction($post)
{

	$author = get_userdata($post->post_author);

	if ($post->post_type == "product") {
		$message = "
              Hi " . $author->display_name . ",
               
              Your product, \"" . $post->post_title . "\" has just been edited.
               
              View product: " . get_permalink($post->ID) . "
               
              Thanks";
		$mailer = WC()->mailer();
		$subject = __('Product edited', 'edumall');
		$headers = [];
		$headers[]  = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "X-Priority: 1 (Higuest)";
		$headers[] = "X-MSMail-Priority: High";
		$headers[] = "Importance: High";
		$email_from_name = tutor_utils()->get_option('email_from_name');
		$from_name = apply_filters('tutor_email_from_name', $email_from_name);
		$headers[] = "From: " . wp_specialchars_decode(esc_html($from_name), ENT_QUOTES) . " <no-reply@smartcodegroup.com>";
		$mailer->send($author->user_email, $subject, $message, $headers, '');
		$mailer->send(get_bloginfo('admin_email'), $subject, $message, $headers, '');
	}
}

add_action('woocommerce_thankyou', 'my_custom_new_order_email');
function my_custom_new_order_email($order_id)
{
	$order = new WC_Order($order_id);

	$billing_address = $order->get_formatted_billing_address(); // for printing or displaying on web page
	$shipping_address = $order->get_formatted_shipping_address();
	$email = $order->get_data()['billing']['email'];
	$name = $order->get_data()['billing']['first_name'] . ' ' . $order->get_data()['billing']['last_name'];
	$billing_phone = $order->get_data()['billing']['phone'];
	$date = date('d.m.y');
	$data   = '';
	$data   .= "<!DOCTYPE html>
		<html lang='en'>
		<head>
			<meta charset='UTF-8'>
			<meta http-equiv='X-UA-Compatible' content='IE=edge'>
			<meta name='viewport' content='width=device-width, initial-scale=1.0'>
			<title>Facture</title>
			<style>
				html{
					font-family: Arial, Helvetica, sans-serif;
					font-size: 12px;
				}
		
				h1{
					color: rgb(224, 224, 224);
					font-weight: 400;
					font-size: 40px;
				}
		
				h2{
					font-size: 16px;
				}
		
				.entete{
					display: flex;
					justify-content: space-between;
					align-items: center;
					border-bottom: 4px solid #007fc5;
					margin-bottom: 35px;
				}
		
				.color-text{
					color: #007fc5;
				}
		
				.bold-text{
					font-weight: 700;
				}
		
				.first-p{
					font-size: 12px;
					padding: 0px 12px;
					display: block;
				}
		
				.entete img{
					width: 130px;
				}
		
				.entete-corps{
					display: flex;
					justify-content: space-between;
					margin-bottom: 20px;
					padding: 0px 12px;
				}
		
				.surligne span{
					background-color: green;
				}
		
				.corps-entete-droit strong{
					display: inline-block;
					width: 170px;
					text-align: right;
					margin-right: 15px;
					padding-top: 1px;
					padding-bottom: 1px;
				}
				.droit{
					text-align: right;
				}
				.corps-entete-gauche{
					padding-top: 14px;
					line-height: 15px;
				}
				.contraint-box{
					padding: 0px 12px;
				}
		
				.table-faq{
					width: 100%;
					text-align: right;
					border: 0;
					margin-top: 8px;
					margin-bottom: 15px;
					/* border: 1px solid #000; */
				}
				.table-faq td, .table-faq th{
					padding: 7px 5px;
				}
				.table-faq tr th:nth-of-type(2), .table-faq tbody tr td:nth-of-type(2){
					text-align: left;
					max-width: 60%;
				}
				.table-faq tr th{
					background-color: rgb(185, 185, 185);
				}
				.table-faq th:nth-child(1),
				.table-faq th:nth-child(2),
				.table-faq th:nth-child(3){
					background: #007fc5;
					color: #FFF;
				}
		
				.table-faq tbody tr td{
					border-bottom: 1px solid lightgray;
				}
				.table-faq tbody tr:last-of-type td{
					border-bottom: 2px solid #000;
				}
				tfoot{
					font-weight: 600;
				}
				tfoot tr:last-of-type td:nth-last-child(1), 
				tfoot tr:last-of-type td:nth-last-child(2){
					background: #007fc5;
					color: #FFF;
				}
		
				.space-p{
					margin-top: 35px;
				}
		
				.signature{
					max-width: 100px;
					max-height: 100px;
					display: block;
				}
		
				.container-infos{
					display: flex;
					justify-content: space-evenly;
					position: relative;
					z-index: 10;
				}
		
				.container-infos p{
					margin: 0;
				}
		
				.container-info{
					text-align: center;
					display: flex;
					align-items: center;
					flex-direction: column;
					margin-top: 15px;
					position: relative;
				}
		
				.container-info img{
					width: 20px;
					height: 20px;
				}
		
				.container-img{
					width: 30px;
					height: 30px;
					padding: 5px;
					border: 1px solid rgb(95, 95, 95);
					border-radius: 50%;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
					margin-bottom: 6px;
					background-color: #FFF;
				}
		
				.last-p{
					position: relative;
				}
		
				.last-p::after{
					content: '';
					width: 100%;
					height: 3px;
					background-color: #007fc5;
					position: absolute;
					top: -72px;
					left: 0;
					z-index: 0;
					display:none;
				}
			</style>
		</head>
		<body>
			<div class='container-principal'>
				<div class='entete' style='justify-content: space-between !important;'>
					<h1 style='width: 100%;'>" . __('INVOICE', 'edumall') . "</h1>
					<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='logo'>
				</div>
				<div class='corps'>
					<span class='color-text bold-text first-p'>F@Academy, Lettenweg 13, 79618 Rheinfelden</span>
					<div class='entete-corps' style='justify-content: space-between;'>
						<div class='corps-entete-gauche' style='width: 72%;'>
							<strong>" . __('Customer', 'edumall') . "</strong>: " . $name . " <br>
							<strong>" . __('Street name', 'edumall') . "</strong>: " . $billing_address . " <br>
						</div>
						<div class='corps-entete-droit'>
							<strong>" . __('Invoice no.', 'edumall') . ":</strong>" . $order_id . "<br>
							<strong>" . __('Invoice date', 'edumall') . ":</strong>" . $date . "<br>
							<div class='surligne'><strong> <span>" . __('Customer no.', 'edumall') . ":</span></strong><span>" . get_current_user_id() . "</span></div>
							<strong>" . __('Contact', 'edumall') . ":</strong>Paul, Fansi<br>
						</div>
					</div>
					<div class='container-data droit'>
						<span>" . $date . "</span>
					</div>
				</div>
				<div class='contraint-box'>
					<h2 class='color-text'>" . __('INVOICE NO.', 'edumall') . " " . $order_id . "</h2>
					<span class='dear-text'>" . __('Dear Sir or Madam', 'edumall') . ",</span>
					<p>" . __('Thank you for your confidence in the', 'edumall') . " <strong>F@Academy</strong>. " . __('We hereby invoice you for the following services', 'edumall') . ":</p>
				</div>
				<table class='table-faq' cellspacing='0' cellpadding='0'>
					<thead>
						<tr>
							<th>" . __('Pos.', 'edumall') . "</th>
							<th>" . __('Description', 'edumall') . "</th>
							<th>" . __('Quantity', 'edumall') . "</th>
							<th>" . __('Unit price', 'edumall') . "</th>
							<th>" . __('Total price', 'edumall') . "</th>
						</tr>
					</thead>
					<tbody>";
	$total_net = 0;
	if (sizeof($order->get_items()) > 0) {
		$i = 0;
		foreach ($order->get_items() as $item_key => $item) {
			$i++;
			$label =    get_the_title(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id);
			$qte = $item->get_quantity();
			$total_net = $total_net + $item->get_subtotal();
			$keys = $i;
			if (get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_type_data", true) && (get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_type_data", true) == "Rehearsal Course")) {
				$data .= "<tr>
					<td>$keys.</th>
					<td>
						<h4>$label</h4>
						<ul>
							<li>" . __('Date', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_date", true) . "</li>
							<li>" . __('Hour', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_hour", true) . "</li>
							<li>" . __('Platform', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_platform", true) . "</li>
							<li>" . __('Link', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_link", true) . "</li>
							<li>" . __('Identifier', 'edumall') . ": " . get_post_meta(tutor_utils()->product_belongs_with_course($item->get_data()['product_id'])->post_id, "tutor_rehearsal_identifiers", true) . "</li>
						</ul>
					</th>
					<td>X$qte</td>
					<td>" . $item->get_subtotal() . "</th>
					<td>" . $item->get_total() . "</th>
					</tr>";
			} else {
				$data .= "<tr>
					<td>$keys.</th>
					<td>$label</th>
					<td>X$qte</td>
					<td>" . $item->get_subtotal() . "</th>
					<td>" . $item->get_total() . "</th>
					</tr>";
			}
		}
	}
	$data .= "</tbody>
				<tfoot>
					<tr>
						<td colspan='4'>" . __('Total net', 'edumall') . "</td>
						<td>" . $total_net . " €</td>
					</tr>
					<tr>
						<td colspan='3'></td>
						<td style='background: #007fc5;color: white;'>" . __('Total', 'edumall') . "</td>
						<td style='background: #007fc5;color: white;'>" . $total_net . " €</td>
					</tr>
				</tfoot>
				</table>
				<small>" . __('Terms of payment: Payment within 14 days from receipt of invoice without deductions.', 'edumall') . ".</small>
				<p class='space-p'>" . __('If you have any questions, please do not hesitate to contact us at any time', 'edumall') . ".</p>
				<span>" . __('With kind regards', 'edumall') . "</span>
				<img class='signature' src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='signature'>
				<div class='footer' style='position:relative;'>
					<div class='container-infos' style='justify-content: space-evenly;'>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								F@Academy <br>
								Lettenweg 13 <br>
								79618 Rheifelde <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								+4917624537308 <br>
								https://faacademy.de <br>
								info@faacademy.de <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								TARGOBANK <br>
								DE45300209005360594849 <br>
								BIC: CMCIDEDDxxx <br>
							</p>
						</div>
						<div class='container-info' style='align-items: center;flex-direction: column;margin: auto;width: 25%;display:grid;'>
							<div class='container-img' style='width: 30px;height: 30px;padding: 5px;border: 1px solid rgb(95, 95, 95);border-radius: 50%;display: flex;flex-direction: column;justify-content: center;align-items: center;margin-bottom: 6px;background-color: #FFF;margin-left: 40%;'>
								<img src='https://fa-academy.smartcodegroup.com/wp-content/uploads/2020/08/F@Academy_logo_rgb_1000px-200x211.png' alt='' style='padding:0;margin:auto;'>
							</div>
							<p>
								" . __('General Manager', 'edumall') . ": <br>
								Paul Fansi <br><br>
							</p>
						</div>
					</div>
					<p class='last-p' style='text-align: center; margin: 0; margin-top: 12px;'>" . __('Note : No sales tax according to §19 UStG', 'edumall') . "</p>
					<div style='display:none;content: \"\";width: 100%;height: 3px;background-color: #007fc5;position: absolute;top: 16px;left: 0;z-index:  0;'></div>
				</div>
			</div>
		</body>
		</html>";

	$mailer = WC()->mailer();
	$subject = __('Thanks for your purchase', 'edumall');
	$headers = [];
	$headers[]  = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/html; charset=iso-8859-1";
	$headers[] = "X-Priority: 1 (Higuest)";
	$headers[] = "X-MSMail-Priority: High";
	$headers[] = "Importance: High";
	$email_from_name = tutor_utils()->get_option('email_from_name');
	$from_name = apply_filters('tutor_email_from_name', $email_from_name);
	$headers[] = "From: " . wp_specialchars_decode(esc_html($from_name), ENT_QUOTES) . " <no-reply@smartcodegroup.com>";

	$mailer->send($email, $subject, $data, $headers, '');
	$order->update_status('wc-completed');
}

function torque_hello_world_shortcode($atts)
{
	$a = shortcode_atts(array(
		'name' => 'Rehearsal Courses & E-Books'
	), $atts);
	ob_start();

	include('cours-de-repetition-widget.php');

	return ob_get_clean();
	// return $data;
}
add_shortcode('rehearsal_ebook', 'torque_hello_world_shortcode');

function our_instructors_list_shortcode($atts)
{
	$a = shortcode_atts(array(
		'name' => 'Our instructors list'
	), $atts);
	ob_start();

	include('our-instructors-widget.php');

	return ob_get_clean();
}
add_shortcode('our_instructors_list', 'our_instructors_list_shortcode');

function count_cours_shortcode($atts)
{
	return tutor_utils()->get_total_courses();
}
add_shortcode('count_courses', 'count_cours_shortcode');

function count_instructor_shortcode($atts)
{
	return tutor_utils()->get_total_instructors();
}
add_shortcode('count_instructors', 'count_instructor_shortcode');

add_filter('manage_edit-matiere_manager_columns', 'my_columns');
function my_columns($columns)
{
	$columns['examen'] = __('exam', 'edumall');
	return $columns;
}

add_action('manage_posts_custom_column', 'my_show_columns');
function my_show_columns($name)
{
	global $post;
	switch ($name) {
		case 'examen':
			$views = get_post(get_post_meta($post->ID, 'examen_id', true));
			echo $views->post_title;
	}
}

add_action('woocommerce_before_add_to_cart_button', 'add_cf_before_addtocart_in_single_products', 1, 0);
function add_cf_before_addtocart_in_single_products()
{
	// global $product;
	global $post, $product;
	$profile_url = tutor_utils()->profile_url($product->post->post_author);
	$author_first_name = get_the_author_meta('first_name', $product->post->post_author);
	$author_last_name = get_the_author_meta('last_name', $product->post->post_author);
	if (get_post_meta($product->get_id(), 'tutor_rehearsal_service_type', true) == "Rehearsal Course" || get_post_meta($product->get_id(), 'tutor_rehearsal_service_type', true) == "Exam Preparation")
		echo '<div class="row" style="margin-bottom: 2rem;">
					<div class="col-md-6"><span>' . __("From", "edumall") . '<span>: <span style="font-weight: bold;">' . get_post_meta($product->get_id(), 'tutor_rehearsal_date', true) . '</span></div>
					<div class="col-md-6"><span>' . __("To", "edumall") . '<span>: <span style="font-weight: bold;">' . get_post_meta($product->get_id(), 'tutor_rehearsal_date_to', true) . '</span></div>
					<div class="col-md-6"><span>' . __("Hour", "edumall") . '<span>: <span style="font-weight: bold;">' . get_post_meta($product->get_id(), 'tutor_rehearsal_hour', true) . '</span></div>
					<div class="col-md-6"><span>' . __("Limit date", "edumall") . '<span>: <span style="font-weight: bold;">' . get_post_meta($product->get_id(), 'tutor_rehearsal_date_limite', true) . '</span></div>
					<div class="col-md-12"><span>' . __("Instructor", "edumall") . '<span>: <a href="' . $profile_url . '" style="font-weight: bold;color: #4169e1;">' . $author_first_name . ' ' . $author_last_name . '</a></div>
				</div>';
}


function filter_woocommerce_customer_available_downloads($downloads, $customer_id)
{
	// Manipulate download data here, this example we'll get the first 5 downloads in the array
	// $downloads = array_slice($downloads, 0, 5);
	// Sorting the array by Order Ids in DESC order
	arsort($downloads);

	// Return first five downloads
	return $downloads;
}
add_filter('woocommerce_customer_available_downloads', 'filter_woocommerce_customer_available_downloads', 10, 2);

/**
 * Group Downloadable products by product ID
 *
 * @param array $downloads
 * @return array
 */
function prefix_group_downloadable_products(array $downloads)
{
	$unique_downloads = [];

	foreach ($downloads as $download) {
		$list = [
			'download_url' => $download['download_url'],
			'file_name'    => $download['file']['name'],
			'orderid'      => $download['order_id']
		];

		if (array_key_exists($download['product_id'], $unique_downloads)) {
			$unique_downloads[$download['product_id']]['list'][] = $list;
			continue;
		}

		$data = $download;
		$data['list'] = [$list];
		$unique_downloads[$download['product_id']] = $data;
	}

	return $unique_downloads;
}
add_filter('woocommerce_customer_get_downloadable_products', 'prefix_group_downloadable_products', 10, 1);

/**
 * Show number list of downloadable files for group product
 *
 * @param array $download
 * @return void
 */
function prefix_downloads_column_download_file(array $download)
{
	$lists = $download['list'];

	// Sorting the array by Order Ids in DESC order
	arsort($lists);

	if (empty($lists)) {
		_e('No Download Files', 'storefront');
		return;
	}

	// Set variable
	$li = '';

	foreach ($lists as $list) {
		// Get $order object from order ID 
		$order = wc_get_order($list['orderid']);

		$order_date = $order->get_date_completed();

		// Append to
		$li .= '<li><a href="' . esc_url($list['download_url']) . '" class="woocommerce-MyAccount-downloads-file">' . esc_html($list['file_name']) . '</a></li>';
	}

	echo '<ul class="charliesub">';
	echo '<p>' .  explode("T", $order_date)[0] . '</p>';
	echo '<li class="accordion">';
	echo '<div>';
	echo '<ol>';

	echo $li;

	echo '</ol>';
	echo '</div>';
	echo '</li>';
	echo '</ul>';
}
add_action('woocommerce_account_downloads_column_download-file', 'prefix_downloads_column_download_file');

add_action('edit_user_profile', 'user_field_wpse_87261');
function user_field_wpse_87261($user)
{
	$tutor_profile_date_naissance = get_user_meta($user->ID, 'tutor_profile_date_naissance', true);
?>
	<h3><?php _e('Additional information'); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label><?php esc_html_e('Date of birth', 'edumall'); ?></label>
			</th>
			<td>
				<input type="date" name="tutor_profile_date_naissance" id="tutor_profile_date_naissance" value="<?= get_user_meta($user->ID, 'tutor_profile_date_naissance', true); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th>
				<label><?php esc_html_e('Phone Number', 'edumall'); ?></label>
			</th>
			<td>
				<input type="text" name="phone_number" id="phone_number" value="<?= get_user_meta($user->ID, 'phone', true); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th>
				<label><?php esc_html_e('Street name and number', 'edumall'); ?></label>
			</th>
			<td>
				<input type="text" name="street" id="street" value="<?= get_user_meta($user->ID, 'street', true); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th>
				<label><?php esc_html_e('Postal code', 'edumall'); ?></label>
			</th>
			<td>
				<input type="text" name="postal_code" id="postal_code" value="<?= get_user_meta($user->ID, 'postal_code', true); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th>
				<label><?php esc_html_e('Town', 'edumall'); ?></label>
			</th>
			<td>
				<input type="text" name="town" id="town" value="<?= get_user_meta($user->ID, 'town', true); ?>" class="regular-text">
			</td>
		</tr>

		<?php
		if (array_key_exists("tutor_instructor", $user->caps) && $user->caps['tutor_instructor']) {
		?>
			<tr>
				<th>
					<label><?php esc_html_e('Your degrees', 'edumall'); ?></label>
				</th>
				<td>
					<input type="file" multiple="multiple" id="tutor_profile_diplomes" name="tutor_profile_diplomes[]"><br>
					<p>
						<?php if (!empty(get_user_meta($user->ID, "tutor_profile_diplomes", true))) {
							foreach (json_decode(get_user_meta($user->ID, "tutor_profile_diplomes", true)) as $key => $value) { ?>
								<a target="_blank" style="color:#4169e1;" href="<?php echo esc_attr($value); ?>"><?php esc_html_e('Click here to see the document', 'edumall'); ?> <?= $key + 1 ?> <span class="fa fa-eye"></span> | </a>
						<?php }
						} ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_html_e('Choose your specialities', 'edumall'); ?> </label>
				</th>
				<td>
					<div class="tutor-form-field-course-categories">
						<select name="tax_input[course-topic][]" multiple="" class="tutor_select2 select2-hidden-accessible" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true">
							<option value="">Select a topic</option>
							<?php foreach (tutor_utils()->get_course_topics() as $key => $value) { ?>
								<option value="<?= $value->term_id ?>" <?php if (!empty(get_user_meta($user->ID, "tutor_profile_themes", true))) {
																			echo in_array($value->term_id, json_decode(get_user_meta($user->ID, "tutor_profile_themes", true))) ? 'selected' : '';
																		} ?>><?= $value->name ?></option>
							<?php } ?>
						</select>
					</div>
				</td>
			</tr>

		<?php
		}
		?>
	</table>
<?php
}

add_action('messages_message_before_save', 'control_sending_of_private_message');

/**
 * Control the message from Private Messages before beign saved/sent
 * @param  BP_Messages_Message $message_object 
 * @return void
 */
function control_sending_of_private_message($message_object)
{

	$sender_id = $message_object->sender_id;

	//String to be checked for email addresses
	$tstr = $message_object->message;

	//test string for checking email
	$test_patt = "/(?:[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/";

	//comapare using preg_match_all() method 
	preg_match_all($test_patt, $tstr, $valid);
	if (count($valid[0]) > 0) {
		$message_object->recipients = false;
		$response["errors"] = [__("Email sharing in messages is prohibited !", 'edumall')];
		$response["redirect"] = "redirect";
		$response["result"] = false;
		wp_send_json($response);
	}

	$regex = "~\b\d[- /\d]*\d\b~";
	$your_string_here = $message_object->message;
	// $your_string_here = "Voici mon numéro de telephone 6 95"; 
	// str_replace(' ', '', $str);
	preg_match_all($regex, $your_string_here, $numbers);

	// var_dump($message_object);
	// var_dump($valid[0]);
	// var_dump($numbers);
	if (count($numbers[0]) > 0 && strlen($numbers[0][0]) > 4) {
		$message_object->recipients = false;
		$message_object->recipients = false;
		$response["errors"] = [__("Phone number sharing in messages is prohibited !", 'edumall')];
		$response["redirect"] = "redirect";
		$response["result"] = false;
		wp_send_json($response);
	}
}
