<?php
defined('ABSPATH') || exit;

/**
 * Let instructors translate their own courses from the front-end Tutor
 * dashboard, with no wp-admin access required.
 *
 * Flow: clicking "Translate to X" duplicates the course as a draft linked to
 * the original via WPML's language-details API, then redirects straight to
 * Tutor's frontend course builder for that new post. Tutor's own
 * is_instructor_of_this_course() check keeps everything scoped to courses
 * the instructor actually owns.
 */
if (! class_exists('Edumall_Tutor_Course_Translation')) {
	class Edumall_Tutor_Course_Translation
	{
		protected static $instance = null;

		public static function instance()
		{
			if (null === self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize()
		{
			if (! defined('ICL_SITEPRESS_VERSION')) {
				return;
			}

			add_action('tutor_course_dashboard_actions_after', [$this, 'render_translate_actions']);
			add_action('admin_post_edumall_translate_course', [$this, 'handle_translate_course']);
		}

		/**
		 * Print one action link per active language: "Translate to X" for
		 * languages the course doesn't have yet, "Edit X translation" for
		 * languages it already has.
		 */
		public function render_translate_actions($course_id)
		{
			if (! tutor_utils()->is_instructor_of_this_course(get_current_user_id(), $course_id)) {
				return;
			}

			$course_post_type = tutor()->course_post_type;

			$source_lang = apply_filters('wpml_element_language_code', null, [
				'element_id'   => $course_id,
				'element_type' => 'post_' . $course_post_type,
			]);

			if (! $source_lang) {
				return;
			}

			$languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);

			if (! is_array($languages)) {
				return;
			}

			unset($languages[$source_lang]);

			foreach ($languages as $lang_code => $language) {
				$translated_id = $this->get_existing_translation_id($course_id, $course_post_type, $lang_code);

				if ($translated_id) {
					$url   = $this->get_course_edit_url_in_language($translated_id, $lang_code);
					/* translators: %s is a language name, e.g. "Français" */
					$label = sprintf(esc_html__('Edit %s translation', 'edumall'), esc_html($language['native_name']));
				} else {
					$url = wp_nonce_url(
						add_query_arg(
							[
								'action'    => 'edumall_translate_course',
								'course_id' => $course_id,
								'lang'      => $lang_code,
							],
							admin_url('admin-post.php')
						),
						'edumall_translate_course_' . $course_id . '_' . $lang_code
					);
					/* translators: %s is a language name, e.g. "Français" */
					$label = sprintf(esc_html__('Translate to %s', 'edumall'), esc_html($language['native_name']));
				}

				printf(
					'<a href="%s" class="tutor-mycourse-translate"><i class="fal fa-language"></i>%s</a>',
					esc_url($url),
					$label
				);
			}
		}

		/**
		 * Duplicate a course into a new language as a draft linked to the
		 * original, then hand the instructor off to the frontend course
		 * builder to fill in the translated content.
		 */
		public function handle_translate_course()
		{
			$course_id   = isset($_GET['course_id']) ? absint($_GET['course_id']) : 0;
			$target_lang = isset($_GET['lang']) ? sanitize_text_field(wp_unslash($_GET['lang'])) : '';

			if (! $course_id || ! $target_lang || ! check_admin_referer('edumall_translate_course_' . $course_id . '_' . $target_lang)) {
				wp_die(esc_html__('Invalid request.', 'edumall'));
			}

			if (! is_user_logged_in() || ! tutor_utils()->is_instructor_of_this_course(get_current_user_id(), $course_id)) {
				wp_die(esc_html__('You are not allowed to translate this course.', 'edumall'));
			}

			$course_post_type = tutor()->course_post_type;
			$element_type      = 'post_' . $course_post_type;

			// Someone may have already created this translation; just send the instructor there.
			$existing_id = $this->get_existing_translation_id($course_id, $course_post_type, $target_lang);

			if ($existing_id) {
				wp_safe_redirect($this->get_course_edit_url_in_language($existing_id, $target_lang));
				exit;
			}

			$original = get_post($course_id);

			if (! $original || $course_post_type !== $original->post_type) {
				wp_die(esc_html__('Course not found.', 'edumall'));
			}

			$source_lang = apply_filters('wpml_element_language_code', null, [
				'element_id'   => $course_id,
				'element_type' => $element_type,
			]);

			$trid = apply_filters('wpml_element_trid', null, $course_id, $element_type);

			$new_course_id = wp_insert_post([
				'post_title'   => $original->post_title,
				'post_content' => $original->post_content,
				'post_excerpt' => $original->post_excerpt,
				'post_status'  => 'draft',
				'post_type'    => $course_post_type,
				'post_author'  => $original->post_author,
			], true);

			if (is_wp_error($new_course_id)) {
				wp_die(esc_html($new_course_id->get_error_message()));
			}

			$thumbnail_id = get_post_thumbnail_id($course_id);

			if ($thumbnail_id) {
				set_post_thumbnail($new_course_id, $thumbnail_id);
			}

			foreach (get_object_taxonomies($course_post_type) as $taxonomy) {
				$term_ids = wp_get_object_terms($course_id, $taxonomy, ['fields' => 'ids']);

				if (! is_wp_error($term_ids) && ! empty($term_ids)) {
					wp_set_object_terms($new_course_id, $term_ids, $taxonomy);
				}
			}

			do_action('wpml_set_element_language_details', [
				'element_id'           => $new_course_id,
				'element_type'         => $element_type,
				'trid'                 => $trid,
				'language_code'        => $target_lang,
				'source_language_code' => $source_lang,
			]);

			add_user_meta($original->post_author, '_tutor_instructor_course_id', $new_course_id);

			wp_safe_redirect($this->get_course_edit_url_in_language($new_course_id, $target_lang));
			exit;
		}

		/**
		 * course_edit_link() builds its URL from get_the_permalink() on the
		 * dashboard page, which WPML coerces to whatever language the
		 * instructor is CURRENTLY browsing in — not the language of the
		 * course being edited. That sent instructors translating a course
		 * to English, while browsing in French, to a French-prefixed URL
		 * (or worse, since the Dashboard page's slug also differs per
		 * language here, to a broken/mismatched one).
		 *
		 * edumall_wpml_dashboard_url_in_language() (functions.php) resolves
		 * the correct URL for the target language directly, without relying
		 * on WPML's current-browsing-language coercion.
		 */
		private function get_course_edit_url_in_language($course_id, $lang_code)
		{
			if (! tutor()->has_pro) {
				return tutor_utils()->course_edit_link($course_id);
			}

			$url = edumall_wpml_dashboard_url_in_language('create-course/?course_ID=' . $course_id, $lang_code);

			return $url ? $url : tutor_utils()->course_edit_link($course_id);
		}

		/**
		 * WPML keeps a course's translation link active even after the
		 * translated post has been moved to trash (deleting a course from
		 * the dashboard only trashes it, it doesn't unlink WPML's
		 * translation record). Treat a trashed translation as if it didn't
		 * exist, so the instructor is offered "Translate to X" again
		 * instead of a dead link to a trashed course.
		 */
		private function get_existing_translation_id($course_id, $course_post_type, $lang_code)
		{
			$translated_id = apply_filters('wpml_object_id', $course_id, $course_post_type, false, $lang_code);

			if (! $translated_id) {
				return null;
			}

			if ('trash' === get_post_status($translated_id)) {
				return null;
			}

			return $translated_id;
		}
	}
}
