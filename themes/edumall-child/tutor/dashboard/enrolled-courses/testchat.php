<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h3><?php _e('Enrolled Courses', 'tutor'); ?></h3>

<div class="tutor-dashboard-content-inner">


    <div class="tutor-dashboard-inline-links">
        <ul>
            <li class="active"><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses'); ?>"> <?php _e('All Courses', 'tutor'); ?></a> </li>
            <li><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses/active-courses'); ?>"> <?php _e('Active Courses', 'tutor'); ?> </a> </li>
            <li><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('enrolled-courses/completed-courses'); ?>">
					<?php _e('Completed Courses', 'tutor'); ?> </a> </li>
            <li >
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/recently-viewed-courses' ); ?>">
					<?php esc_html_e( 'Recently Viewed Courses', 'edumall' ); ?>
				</a>
			</li>
            <li >
				<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/testchat' ); ?>">
					<?php esc_html_e( 'Test chat', 'edumall' ); ?>
				</a>
			</li>
        </ul>
    </div>


	<?php
	$my_courses = tutor_utils()->get_enrolled_courses_by_user(get_current_user_id(), array('private', 'publish'));

	if ($my_courses && $my_courses->have_posts()):
        ?>
        <table class="dashboard-table">
				<tr>
					<th class="col-student-info"><?php esc_html_e( 'Instructor', 'edumall' ); ?></th>
					<th class="col-student-actions"><?php esc_html_e( 'Profile', 'edumall' ); ?></th>
                    <th class="col-student-actions"><?php esc_html_e( 'Chat', 'edumall' ); ?></th>
				
				</tr>
                
            <?php
            $i=0;
            $name=array();
            $id=array();
            while ($my_courses->have_posts()):
                $my_courses->the_post();
                $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
                $tutor_course_img = get_tutor_course_thumbnail_src();
                /**
                 * wp 5.7.1 showing plain permalink for private post
                 * since tutor do not work with plain permalink
                 * url is set to post_type/slug (courses/course-slug)
                 * @since 1.8.10
                */
                $post = $my_courses->post;
                $custom_url = home_url($post->post_type.'/'.$post->post_name);
                $profile_url             = tutor_utils()->profile_url( intval($post->post_author) );
               
                $name[$i]=$post->post_author;
              
                $i=$i+1;
            endwhile;
            $name=array_unique($name);
            foreach ($name as $element ) {
                ?>
                <tr>
                            <td class="td-student-info">
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <?php echo edumall_get_avatar(intval($element), 100 ); ?>
                                    </div>
                                    <h6 class="student-name">
                                        <?php 
                                            $first=get_the_author_meta('first_name',intval($element));
                                            $last=get_the_author_meta('last_name',intval($element));
                                            echo esc_html(  $first.' '.$last); 
                                        ?>
                                    </h6>
                                </div>
                            </td>
                            <td class="td-student-actions">
                                <a href="<?php echo esc_url( $profile_url ); ?>" class="student-profile-link"><i
                                        class="fal fa-eye"></i><?php esc_html_e( 'Profile', 'edumall' ) ?></a>
                            </td>
                            <td  >
							<div class="tutor-mycourse-content">
								<span>
									<span clas="button-text">
										<?php
											ens_send_private_message_link();
											echo ens_send_message_button($args = '' );
										?>
									</span>
								</span>
							</div>
						</td>
                        
                </tr>
                <?php
               
            }
                ?>
                
                

                <?php
           
        ?>
        </table>
            <?php
		wp_reset_postdata();
    else:
        echo "<div class='tutor-mycourse-wrap'><div class='tutor-mycourse-content'>".__('You haven\'t purchased any course', 'tutor')."</div></div>";
	endif;

	?>

</div>
<?php

											function ens_displayed_user_id() {
												
												$test2=get_the_ID();
												// $url=the_permalink();
												// $test=url_to_postid($url);
												//echo($test2);
												$author_id = get_post_field( 'post_author', $test2 );
												//var_dump($my_courses);
												//echo $author_id;
												return $author_id;
											}

											function ens_send_private_message_link() {

												if ( bp_is_my_profile() || ! is_user_logged_in() ) {
													return false;
												}

												return apply_filters( 'bp_get_send_private_message_link', wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( ens_displayed_user_id() ) ) );
											}
									
										function ens_send_message_button( $args = '' ) {

											$r = bp_parse_args( $args, array(
												'id'                => 'private_message',
												'component'         => 'messages',
												'must_be_logged_in' => true,
												'block_self'        => true,
												'wrapper_id'        => 'send-private-message',
												'link_href'         => ens_send_private_message_link(),
												'link_text'         => __( ' Messages', 'buddypress' ),
												'link_class'        => 'tutor-btn bordered-btn',
											) );
											return apply_filters( 'bp_get_send_message_button',
												bp_get_button( apply_filters( 'bp_get_send_message_button_args', $r ) )
											);
										}
										
										
										?>
