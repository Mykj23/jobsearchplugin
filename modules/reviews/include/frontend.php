<?php
/*
  Class : Reviews Frontend
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_Reviews_Frontend {

    public function __construct() {
        add_action('jobsearch_add_review_btn', array($this, 'add_review_btn'), 10, 1);
        add_action('jobsearch_add_review_form', array($this, 'add_review_form_html'), 10, 1);
        add_action('jobsearch_post_avg_rating', array($this, 'post_avg_rating'), 10, 1);
        add_action('jobsearch_post_reviews_list', array($this, 'post_reviews_list'), 10, 1);
        add_action('wp_ajax_jobsearch_user_review_post', array($this, 'user_review_submit'));
        add_action('wp_ajax_nopriv_jobsearch_user_review_post', array($this, 'user_review_submit'));
    }

    public function add_review_btn($args = array()) {

        global $jobsearch_review_popup_args, $jobsearch_plugin_options;

        $jobsearch_review_popup_args = $args;

        $reviews_switch = isset($jobsearch_plugin_options['reviews_switch']) ? $jobsearch_plugin_options['reviews_switch'] : '';

        if ($reviews_switch == 'on') {
            $post_id = isset($args['post_id']) ? $args['post_id'] : 0;
            if (is_user_logged_in()) {
                wp_enqueue_script('jobsearch-barrating');
                wp_enqueue_script('jobsearch-add-review');
                ?>
                <a href="javascript:void(0);" data-target="add_review_form_sec" class="jobsearch-employerdetail-btn jobsearch-go-to-review-form" data-post_id="<?php echo ($post_id) ?>"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Add a review', 'wp-jobsearch') ?></a>
                <?php
            } else {
                ?>
                <a href="javascript:void(0);" class="jobsearch-employerdetail-btn jobsearch-open-signin-tab"><i class="jobsearch-icon jobsearch-add"></i> <?php esc_html_e('Add a review', 'wp-jobsearch') ?></a>
                <?php
            }
        }
    }

    public function add_review_form_html($args = array()) {
        $view_form = false;
        if (is_user_logged_in()) {
            $view_form = true;
        }

        if (isset($args['must_login']) && $args['must_login'] == 'no') {
            $view_form = true;
        }
        if ($view_form === true) {
            global $jobsearch_review_popup_args, $jobsearch_plugin_options, $sitepress;

            $lang_code = '';
            if (function_exists('icl_object_id')) {
                $lang_code = $sitepress->get_current_language();
            }

            $post_id = isset($args['post_id']) ? $args['post_id'] : 0;
            $rev_post_type = get_post_type($post_id);
            if ($rev_post_type == 'candidate') {
                $main_class = 'jobsearch-candidate-wrap-section';
            } else {
                $main_class = 'jobsearch-employer-wrap-section';
            }

            $reviews_switch = isset($jobsearch_plugin_options['reviews_switch']) ? $jobsearch_plugin_options['reviews_switch'] : '';

            if ($reviews_switch == 'on') {
                wp_enqueue_script('jobsearch-barrating');
                wp_enqueue_script('jobsearch-add-review');

                $review_titles = isset($jobsearch_plugin_options['reviews_titles']) ? $jobsearch_plugin_options['reviews_titles'] : '';
                ?>
                <div id="add_review_form_sec" class="<?php echo ($main_class) ?> jobsearch-margin-bottom">
                    <div class="jobsearch-content-title jobsearch-addmore-space"><h2><?php esc_html_e('Leave Your Review', 'wp-jobsearch'); ?></h2></div>
                    <div class="jobsearch-add-review-con">

                        <form id="jobsearch-review-form" class="jobsearch-addreview-form" method="post">
                            <ul>
                                <li>
                                    <div class="review-stars-sec">
                                        <?php
                                        if (!empty($review_titles)) {
                                            $review_title_count = 1;
                                            foreach ($review_titles as $review_title) {

                                                $review_title = apply_filters('wpml_translate_single_string', $review_title, 'JobSearch Options', 'Review Title - ' . $review_title, $lang_code);
                                                ?>

                                                <div class="review-stars-holder">
                                                    <label><?php echo ($review_title) ?></label>
                                                    <select id="review-stars-selector-<?php echo ($review_title_count) ?>" name="user_rating_<?php echo ($review_title_count) ?>" autocomplete="off">
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                    </select>
                                                </div>
                                                <?php
                                                $review_title_count ++;
                                            }
                                        } else {
                                            ?>
                                            <div class="review-stars-holder">
                                                <label><?php esc_html_e("Rating", "wp-jobsearch") ?></label>
                                                <select id="review-stars-selector" name="user_rating_1" autocomplete="off">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="review-overall-stars-sec">
                                        <span class="rating-text"><?php esc_html_e("Overall Rating", "wp-jobsearch") ?></span>
                                        <span class="rating-num">1.0</span>
                                        <div class="jobsearch-company-rating"><span class="jobsearch-company-rating-box" style="width: 20%;"></span></div>
                                    </div>
                                </li>
                                <li>
                                    <textarea name="user_comment" placeholder="<?php esc_html_e("Your Review", "wp-jobsearch") ?>"></textarea>
                                </li>
                                <li>
                                    <input type="hidden" name="review_post" value="<?php echo absint($post_id) ?>">
                                    <input type="hidden" name="action" value="jobsearch_user_review_post">
                                    <input type="submit" id="jobsearch-review-submit-btn" value="<?php esc_html_e("Submit now", "wp-jobsearch") ?>">
                                    <span class="jobsearch-review-loader"></span>
                                    <span class="jobsearch-review-msg"></span>
                                </li>
                            </ul>
                        </form>                                
                    </div>                               
                </div>
                <?php
            }
        }
    }

    public function post_reviews_list($args = array()) {

        global $jobsearch_plugin_options, $sitepress;

        $lang_code = '';
        if (function_exists('icl_object_id')) {
            $lang_code = $sitepress->get_current_language();
        }

        $reviews_switch = isset($jobsearch_plugin_options['reviews_switch']) ? $jobsearch_plugin_options['reviews_switch'] : '';

        if ($reviews_switch == 'on') {
            $post_id = isset($args['post_id']) ? $args['post_id'] : '';

            $rev_post_type = get_post_type($post_id);

            $rev_list_label = isset($args['list_label']) ? $args['list_label'] : '';
            $com_args = array(
                'post_id' => $post_id,
                'status' => 'approve',
            );
            $all_comments = get_comments($com_args);

            wp_enqueue_script('jobsearch-add-review');
            if (!empty($all_comments)) {

                if ($rev_post_type == 'candidate') {
                    $main_class = 'jobsearch-candidate-wrap-section';
                } else {
                    $main_class = 'jobsearch-employer-wrap-section';
                }

                $review_titles = isset($jobsearch_plugin_options['reviews_titles']) ? $jobsearch_plugin_options['reviews_titles'] : '';
                ?>
                <div class="<?php echo ($main_class) ?>">
                    <div class="jobsearch-content-title jobsearch-addmore-space"><h2><?php echo ($rev_list_label) ?></h2></div>

                    <div class="jobsearch-company-review">
                        <ul class="jobsearch-row">
                            <?php
                            foreach ($all_comments as $r_comment) {
                                $com_id = $r_comment->comment_ID;
                                $comment_date = $r_comment->comment_date;

                                $rev_avg_rating = get_comment_meta($com_id, 'review_avg_rating', true);
                                $rev_overall_rating = get_comment_meta($com_id, 'review_overall_rating', true);

                                $_avg_rting_perc = 0;
                                if ($rev_avg_rating > 0) {
                                    $_avg_rting_perc = ($rev_avg_rating / 5) * 100;
                                }

                                $rev_avg_rating = $rev_avg_rating > 0 ? $rev_avg_rating : 0;

                                $comment_user_id = $r_comment->user_id;
                                $comment_user_obj = get_user_by('ID', $comment_user_id);

                                if ($rev_post_type == 'candidate') {
                                    $comment_candidate_id = jobsearch_get_user_employer_id($comment_user_id);
                                } else {
                                    $comment_candidate_id = jobsearch_get_user_candidate_id($comment_user_id);
                                }

                                $user_def_avatar_url = get_avatar_url($comment_user_id, array('size' => 60));
                                $user_avatar_id = get_post_thumbnail_id($comment_candidate_id);
                                if ($user_avatar_id > 0) {
                                    $user_thumbnail_image = wp_get_attachment_image_src($user_avatar_id, 'thumbnail');
                                    $user_def_avatar_url = isset($user_thumbnail_image[0]) && esc_url($user_thumbnail_image[0]) != '' ? $user_thumbnail_image[0] : '';
                                }
                                ?>
                                <li class="jobsearch-column-12">
                                    <?php
                                    if (!empty($rev_overall_rating) && sizeof($rev_overall_rating) > 1) {
                                        ?>
                                        <div class="review-detail-popover">
                                            <?php
                                            $overall_ratee_count = 0;
                                            foreach ($rev_overall_rating as $rev_overall_ratee) {
                                                $o_avg_rting_perc = 0;
                                                if ($rev_overall_ratee > 0) {
                                                    $o_avg_rting_perc = ($rev_overall_ratee / 5) * 100;
                                                }

                                                $review_title = isset($review_titles[$overall_ratee_count]) ? $review_titles[$overall_ratee_count] : '';
                                                $review_title = apply_filters('wpml_translate_single_string', $review_title, 'JobSearch Options', 'Review Title - ' . $review_title, $lang_code);
                                                ?>
                                                <div class="rating-detail-item">
                                                    <span class="rating-title"><?php echo ($review_title) ?></span>
                                                    <div class="jobsearch-company-rating"><span class="jobsearch-company-rating-box" style="width: <?php echo ($o_avg_rting_perc) ?>%;"></span></div>
                                                </div>
                                                <?php
                                                $overall_ratee_count++;
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    $com_user_dname = isset($comment_user_obj->display_name) ? $comment_user_obj->display_name : '';
                                    $com_user_dname = apply_filters('jobsearch_user_display_name', $com_user_dname, $comment_user_obj);
                                    ?>
                                    <figure>
                                        <a href="<?php echo get_permalink($comment_candidate_id) ?>" class="company-review-thumb"><img src="<?php echo ($user_def_avatar_url) ?>" alt=""></a>
                                        <figcaption>
                                            <div class="jobsearch-company-review-left">
                                                <h6><a href="<?php echo get_permalink($comment_candidate_id) ?>"><?php echo ($com_user_dname); ?></a></h6>
                                                <div class="jobsearch-company-rating"><span class="jobsearch-company-rating-box" style="width: <?php echo ($_avg_rting_perc) ?>%;"></span></div>
                                                <small><?php echo number_format($rev_avg_rating, 1) ?></small>
                                            </div>
                                            <?php
                                            if ($comment_date != '') {
                                                ?>
                                                <time datetime="<?php echo date_i18n('Y-m-d H:i:s', strtotime($comment_date)) ?>"><?php echo date_i18n(get_option('date_format'), strtotime($comment_date)) ?></time>
                                                <?php
                                            }
                                            ?>
                                        </figcaption>
                                    </figure>
                                    <div class="jobsearch-company-review-text">
                                        <p><?php echo ($r_comment->comment_content) ?></p>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
            }
        }
    }

    public function post_avg_rating($args = array()) {

        global $jobsearch_plugin_options;
        $reviews_switch = isset($jobsearch_plugin_options['reviews_switch']) ? $jobsearch_plugin_options['reviews_switch'] : '';

        if ($reviews_switch == 'on') {
            $post_id = isset($args['post_id']) ? $args['post_id'] : '';
            $rating_view = isset($args['view']) ? $args['view'] : '';

            $over_all_avg_rting = 0;
            $over_all_avg_rting_perc = 0;
            $oveall_review_avg_rating = get_post_meta($post_id, 'oveall_review_avg_rating', true);
            if ($oveall_review_avg_rating > 0) {
                $over_all_avg_rting = $oveall_review_avg_rating;
                $over_all_avg_rting_perc = ($over_all_avg_rting / 5) * 100;
            }
            if ($over_all_avg_rting > 0) {
                if ($rating_view == 'number') {
                    echo number_format($over_all_avg_rting, 1);
                } else {
                    ?>
                    <small class="jobsearch-rating-info"><?php echo number_format($over_all_avg_rting, 1) ?></small>
                    <div class="jobsearch-rating"><small class="jobsearch-rating-box" style="width:<?php echo ($over_all_avg_rting_perc) ?>%"></small></div>
                    <?php
                }
            }
        }
    }

    private function user_avg_review_asign_to_arr($arr = array(), $rate = 1) {
        if ($rate > 0 && $rate < 2) {
            $arr['rating_1'] += 1;
        } else if ($rate >= 2 && $rate < 3) {
            $arr['rating_2'] += 1;
        } else if ($rate >= 3 && $rate < 4) {
            $arr['rating_3'] += 1;
        } else if ($rate >= 4 && $rate < 5) {
            $arr['rating_4'] += 1;
        } else {
            $arr['rating_5'] += 1;
        }
        return $arr;
    }

    private function user_review_asign_to_arr($arr = array(), $rate = '1') {
        switch ($rate) {
            case '1':
                $arr['rating_1'] += 1;
                break;
            case '2':
                $arr['rating_2'] += 1;
                break;
            case '3':
                $arr['rating_3'] += 1;
                break;
            case '4':
                $arr['rating_4'] += 1;
                break;
            case '5':
                $arr['rating_5'] += 1;
                break;
        }
        return $arr;
    }

    public function user_review_submit() {
        global $jobsearch_plugin_options;

        $review_text_length = isset($jobsearch_plugin_options['review_text_length']) ? $jobsearch_plugin_options['review_text_length'] : '';
        $review_text_length = absint($review_text_length) > 0 ? absint($review_text_length) : 5000;

        $time = current_time('mysql');

        $review_post_id = isset($_POST['review_post']) ? $_POST['review_post'] : '';
        $user_comment = isset($_POST['user_comment']) ? $_POST['user_comment'] : '';

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_obj = get_user_by('ID', $user_id);

            $rev_post_type = get_post_type($review_post_id);

            if ($rev_post_type == 'candidate') {
                $user_can_post = jobsearch_user_is_employer($user_id);
            } else {
                $user_can_post = jobsearch_user_is_candidate($user_id);
            }

            if ($user_can_post) {
                $user_name = $user_obj->display_name;
                $user_email = $user_obj->user_email;

                if ($user_comment == '') {
                    $msg = esc_html__("Review text cannot be blank.", "wp-jobsearch");
                    echo json_encode(array('msg' => $msg, 'error' => '1'));
                    die;
                }
                if (strlen($user_comment) > $review_text_length) {
                    $msg = sprintf(esc_html__("Maximum Review text allowed is %s characters.", "wp-jobsearch"), $review_text_length);
                    echo json_encode(array('msg' => $msg, 'error' => '1'));
                    die;
                }

                $review_titles = isset($jobsearch_plugin_options['reviews_titles']) ? $jobsearch_plugin_options['reviews_titles'] : '';

                // already reviewd check
                $args = array(
                    'post_id' => $review_post_id,
                );
                $post_all_comments = get_comments($args);

                $user_has_reviewd = false;
                if (!empty($post_all_comments)) {
                    foreach ($post_all_comments as $postr_comment) {
                        $comment_user_id = $postr_comment->user_id;

                        if ($user_id == $comment_user_id) {
                            $user_has_reviewd = true;
                            break;
                        }
                    }
                }
                if ($user_has_reviewd === true) {
                    $msg = esc_html__("You have already posted review here.", "wp-jobsearch");
                    echo json_encode(array('msg' => $msg, 'error' => '1'));
                    die;
                }
                //

                $review_user_rate = array();
                if (!empty($review_titles)) {
                    $review_title_count = 1;
                    foreach ($review_titles as $review_title) {
                        $review_user_rate['user_rating_' . $review_title_count] = isset($_POST['user_rating_' . $review_title_count]) && absint($_POST['user_rating_' . $review_title_count]) > 0 ? $_POST['user_rating_' . $review_title_count] : 1;
                        $review_title_count ++;
                    }
                } else {
                    $review_user_rate['user_rating_1'] = isset($_POST['user_rating_1']) && absint($_POST['user_rating_1']) > 0 ? $_POST['user_rating_1'] : 1;
                }
                //
                $total_rating = 0;
                $num_ratings = 0;
                foreach ($review_user_rate as $review_user_rat_key => $review_user_rat_val) {
                    $review_user_rat_val = absint($review_user_rat_val) > 5 ? 5 : absint($review_user_rat_val);
                    $total_rating += $review_user_rat_val;
                    $num_ratings++;
                }
                $avg_rating = 1;
                if ($total_rating > 0 && $num_ratings > 0) {
                    $avg_rating = $total_rating / $num_ratings;
                }

                $review_data = array(
                    'comment_post_ID' => $review_post_id,
                    'comment_author' => $user_name,
                    'comment_author_email' => $user_email,
                    'comment_author_url' => '',
                    'comment_content' => $user_comment,
                    'comment_type' => '',
                    'comment_parent' => 0,
                    'user_id' => $user_id,
                    'comment_author_IP' => '',
                    'comment_agent' => '',
                    'comment_date' => $time,
                    'comment_approved' => 1,
                );

                $comment_id = wp_insert_comment($review_data);

                add_comment_meta($comment_id, 'review_avg_rating', $avg_rating);
                add_comment_meta($comment_id, 'review_overall_rating', $review_user_rate);

                // updating review Post
                $all_avg_ratings = $all_overall_ratings = array(
                    'rating_1' => 0,
                    'rating_2' => 0,
                    'rating_3' => 0,
                    'rating_4' => 0,
                    'rating_5' => 0,
                );

                $args = array(
                    'post_id' => $review_post_id,
                    'status' => 'approve',
                );
                $all_comments = get_comments($args);

                $all_avg_ratingee = 0;
                $count_reviews = 0;
                if (!empty($all_comments)) {
                    foreach ($all_comments as $r_comment) {
                        $com_id = $r_comment->comment_ID;
                        $rev_avg_rating = get_comment_meta($com_id, 'review_avg_rating', true);
                        $all_avg_ratings = $this->user_avg_review_asign_to_arr($all_avg_ratings, $rev_avg_rating);

                        $all_avg_ratingee += $rev_avg_rating;

                        $rev_overall_rating = get_comment_meta($com_id, 'review_overall_rating', true);
                        if (!empty($rev_overall_rating)) {
                            $overall_user_rate = array();
                            $review_title_count = 1;
                            foreach ($rev_overall_rating as $review_title_rate) {
                                $overall_user_rate['user_rating_' . $review_title_count] = $this->user_review_asign_to_arr($all_overall_ratings, $review_title_rate);
                                $review_title_count ++;
                            }
                        }
                        $count_reviews ++;
                    }

                    $all_avg_ratingee = $all_avg_ratingee / $count_reviews;
                }

                update_post_meta($review_post_id, 'oveall_review_avg_rating', $all_avg_ratingee);
                update_post_meta($review_post_id, 'oveall_review_avg_ratings', $all_avg_ratings);
                update_post_meta($review_post_id, 'oveall_review_overall_ratings', $overall_user_rate);
                update_post_meta($review_post_id, 'oveall_review_count', $count_reviews);

                $msg = esc_html__("Review submit successfully.", "wp-jobsearch");
                echo json_encode(array('msg' => $msg));
                die;
                //
            } else {
                $msg = esc_html__("You cannot add review.", "wp-jobsearch");
                echo json_encode(array('msg' => $msg, 'error' => '1'));
                die;
            }
            //
        } else {
            $msg = esc_html__("You are not logged in.", "wp-jobsearch");
            echo json_encode(array('msg' => $msg, 'error' => '1'));
            die;
        }
    }

}

// class Jobsearch_Reviews 
$Jobsearch_Reviews_Frontend_obj = new Jobsearch_Reviews_Frontend();
global $Jobsearch_Reviews_Frontend_obj;
