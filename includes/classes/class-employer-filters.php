<?php
/*
  Class : EmployerFilterHTML
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_EmployerFilterHTML {

// hook things up
    public function __construct() {
        add_filter('jobsearch_employer_filter_date_posted_box_html', array($this, 'jobsearch_employer_filter_date_posted_box_html_callback'), 1, 5);
        add_filter('jobsearch_employer_filter_sector_box_html', array($this, 'jobsearch_employer_filter_sector_box_html_callback'), 1, 5);
        add_filter('jobsearch_team_size_filter_box_html', array($this, 'jobsearch_employer_filter_team_size_box_html_callback'), 1, 5);
        add_filter('jobsearch_employer_filter_location_box_html', array($this, 'jobsearch_employer_filter_location_box_html_callback'), 1, 5);

        //
        add_filter('wp_ajax_jobsearch_load_more_filter_locs_to_list', array($this, 'load_more_locations'));
        add_filter('wp_ajax_nopriv_jobsearch_load_more_filter_locs_to_list', array($this, 'load_more_locations'));
    }

    static function jobsearch_employer_filter_team_size_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        $team_size = isset($_REQUEST['team_size']) ? $_REQUEST['team_size'] : '';
        $rand = rand(234, 34234);
        $default_date_time_formate = 'd-m-Y H:i:s';
        $current_timestamp = current_time('timestamp');

        $employer_team_filter = isset($sh_atts['employer_filters_team']) ? $sh_atts['employer_filters_team'] : '';

        $team_size_arr = explode('-', $team_size);
        $team_size_fv = isset($team_size_arr[0]) ? absint($team_size_arr[0]) : 0;
        $team_size_sv = isset($team_size_arr[1]) ? absint($team_size_arr[1]) : 0;
        
        $team_filter_collapse = isset($sh_atts['employer_filters_team_collapse']) ? $sh_atts['employer_filters_team_collapse'] : '';

        ob_start();
        ?>
        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($team_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>">
                <h2><a href="javascript:void(0);" class="jobsearch-click-btn"><?php echo esc_html__('Team Size', 'wp-jobsearch'); ?></a></h2>
                <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($team_filter_collapse == 'yes' ? 'none' : 'block') ?>;"> 
                    <ul class="jobsearch-checkbox"> 
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            $team_size_count_arr = array(
                                array(
                                    'key' => 'jobsearch_field_employer_team_size',
                                    'value' => array(1, 100),
                                    'type' => 'numeric',
                                    'compare' => 'BETWEEN',
                                )
                            );
                            $team_size_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $team_size_count_arr, $global_rand_id, 'team_size');
                            ?>
                            <input id="team-size-1-100-<?php echo absint($rand); ?>" type="radio" name="team_size" <?php if ($team_size == '1-100') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="1-100" />
                            <label for="team-size-1-100-<?php echo absint($rand); ?>"><span></span><?php esc_html_e('1-100 Members', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($team_size_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            $team_size_count_arr = array(
                                array(
                                    'key' => 'jobsearch_field_employer_team_size',
                                    'value' => array(101, 200),
                                    'type' => 'numeric',
                                    'compare' => 'BETWEEN',
                                )
                            );
                            $team_size_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $team_size_count_arr, $global_rand_id, 'team_size');
                            ?>
                            <input id="team-size-101-200-<?php echo absint($rand); ?>" type="radio" name="team_size" <?php if ($team_size == '101-200') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="101-200" />
                            <label for="team-size-101-200-<?php echo absint($rand); ?>"><span></span><?php esc_html_e('101-200 Members', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($team_size_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            $team_size_count_arr = array(
                                array(
                                    'key' => 'jobsearch_field_employer_team_size',
                                    'value' => array(201, 300),
                                    'type' => 'numeric',
                                    'compare' => 'BETWEEN',
                                )
                            );
                            $team_size_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $team_size_count_arr, $global_rand_id, 'team_size');
                            ?>
                            <input id="team-size-201-300-<?php echo absint($rand); ?>" type="radio" name="team_size" <?php if ($team_size == '201-300') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="201-300" />
                            <label for="team-size-201-300-<?php echo absint($rand); ?>"><span></span><?php esc_html_e('201-300 Members', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($team_size_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            $team_size_count_arr = array(
                                array(
                                    'key' => 'jobsearch_field_employer_team_size',
                                    'value' => array(301, 400),
                                    'type' => 'numeric',
                                    'compare' => 'BETWEEN',
                                )
                            );
                            $team_size_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $team_size_count_arr, $global_rand_id, 'team_size');
                            ?>
                            <input id="team-size-301-400-<?php echo absint($rand); ?>" type="radio" name="team_size" <?php if ($team_size == '301-400') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="301-400" />
                            <label for="team-size-301-400-<?php echo absint($rand); ?>"><span></span><?php esc_html_e('301-400 Members', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($team_size_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            $team_size_count_arr = array(
                                array(
                                    'key' => 'jobsearch_field_employer_team_size',
                                    'value' => array(401, 500),
                                    'type' => 'numeric',
                                    'compare' => 'BETWEEN',
                                )
                            );
                            $team_size_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $team_size_count_arr, $global_rand_id, 'team_size');
                            ?>
                            <input id="team-size-401-500-<?php echo absint($rand); ?>" type="radio" name="team_size" <?php if ($team_size == '401-500') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="401-500" />
                            <label for="team-size-401-500-<?php echo absint($rand); ?>"><span></span><?php esc_html_e('401-500 Members', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($team_size_totnum); ?></span>
                            <?php } ?>
                        </li>
                    </ul>
                </div> 
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
        if ($employer_team_filter == 'no') {
            $html = '';
        }
        return $html;
    }

    static function jobsearch_employer_filter_date_posted_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        $posted = isset($_REQUEST['posted']) ? $_REQUEST['posted'] : '';
        $rand = rand(234, 34234);
        $default_date_time_formate = 'd-m-Y H:i:s';
        $current_timestamp = current_time('timestamp');

        $employer_date_filter = isset($sh_atts['employer_filters_date']) ? $sh_atts['employer_filters_date'] : '';
        $date_filter_collapse = isset($sh_atts['employer_filters_date_collapse']) ? $sh_atts['employer_filters_date_collapse'] : '';
        ob_start();
        ?>
        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($date_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>">
                <h2><a href="javascript:void(0);" class="jobsearch-click-btn"><?php echo esc_html__('Date Posted', 'wp-jobsearch'); ?></a></h2>
                <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($date_filter_collapse == 'yes' ? 'none' : 'block') ?>;"> 
                    <ul class="jobsearch-checkbox"> 
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $lastdate = date($default_date_time_formate, strtotime('-1 hours', $current_timestamp));
                            $last_hour_count_arr = array(
                                array(
                                    'key' => 'post_date',
                                    'value' => strtotime($lastdate),
                                    'compare' => '>=',
                                )
                            );
                            $last_hour_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $last_hour_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="lasthour<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'lasthour') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="lasthour" />
                            <label for="lasthour<?php echo absint($rand); ?>"><span></span><?php esc_html_e('Last Hour', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($last_hour_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $lastdate = date($default_date_time_formate, strtotime('-24 hours', $current_timestamp));
                            $last24_count_arr = array(
                                array(
                                    'key' => 'post_date',
                                    'value' => strtotime($lastdate),
                                    'compare' => '>=',
                                )
                            );
                            $last24_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $last24_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="last24<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'last24') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="last24" />
                            <label for="last24<?php echo absint($rand); ?>"><span></span><?php esc_html_e('Last 24 hours', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($last24_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $lastdate = date($default_date_time_formate, strtotime('-7 days', $current_timestamp));
                            $days7_count_arr = array(
                                array(
                                    'key' => 'post_date',
                                    'value' => strtotime($lastdate),
                                    'compare' => '>=',
                                )
                            );
                            $days7_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $days7_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="7days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '7days') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="7days" />
                            <label for="7days<?php echo absint($rand); ?>"><span></span><?php esc_html_e('Last 7 days', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($days7_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $lastdate = date($default_date_time_formate, strtotime('-14 days', $current_timestamp));
                            $days14_count_arr = array(
                                array(
                                    'key' => 'post_date',
                                    'value' => strtotime($lastdate),
                                    'compare' => '>=',
                                )
                            );
                            $days14_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $days14_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="14days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '14days') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="14days" />
                            <label for="14days<?php echo absint($rand); ?>"><span></span><?php esc_html_e('Last 14 days', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($days14_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $lastdate = date($default_date_time_formate, strtotime('-30 days', $current_timestamp));
                            $days30_count_arr = array(
                                array(
                                    'key' => 'post_date',
                                    'value' => strtotime($lastdate),
                                    'compare' => '>=',
                                )
                            );
                            $days30_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $days30_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="30days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '30days') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="30days" />
                            <label for="30days<?php echo absint($rand); ?>"><span></span><?php esc_html_e('Last 30 days', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($days30_totnum); ?></span>
                            <?php } ?>
                        </li>
                        <li<?php echo ($left_filter_count_switch != 'yes' ? ' class="no-filter-counts"' : '') ?>>
                            <?php
                            // main query array $args_count 
                            $all_days_count_arr = array(
                            );
                            $all_days_totnum = jobsearch_get_employer_item_count($left_filter_count_switch, $args_count, $all_days_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="all<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'all' || $posted == '') echo 'checked="checked"'; ?> onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);" value="all" />
                            <label for="all<?php echo absint($rand); ?>"><span></span><?php esc_html_e('All', 'wp-jobsearch') ?></label>
                            <?php if ($left_filter_count_switch == 'yes') { ?>
                                <span class="filter-post-count"><?php echo absint($all_days_totnum); ?></span>
                            <?php } ?>
                        </li>
                    </ul>
                </div> 
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
        if ($employer_date_filter == 'no') {
            $html = '';
        }
        return $html;
    }

    static function jobsearch_employer_filter_sector_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        global $jobsearch_form_fields;
        $sector_name = 'sector';
        $sector = isset($_REQUEST['sector']) ? $_REQUEST['sector'] : '';

        $employer_sector_filter = isset($sh_atts['employer_filters_sector']) ? $sh_atts['employer_filters_sector'] : '';
        $sec_filter_collapse = isset($sh_atts['employer_filters_sector_collapse']) ? $sh_atts['employer_filters_sector_collapse'] : '';

        ob_start();
        ?>
        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($sec_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>">
                <h2><a href="javascript:void(0);" class="jobsearch-click-btn"><?php echo esc_html__('Sector', 'wp-jobsearch') ?></a></h2>
                <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($sec_filter_collapse == 'yes' ? 'none' : 'block') ?>;"> 

                    <?php
                    // get all employer types

                    $sector_parent_id = 0;
                    $sector_show_count = 0;
                    $input_type_sector = 'radio';   // if first level then select only sigle sector
                    if ($sector != '') {
                        $selected_spec = get_term_by('slug', $sector, 'sector');
                        if (isset($selected_spec->term_id))
                            $sector_parent_id = $selected_spec->term_id;
                        ?>
                        <ul class="jobsearch-checkbox">
                            <li><a href ="#" onclick="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>);"><?php echo esc_html__('View all sector', 'wp-jobsearch'); ?></a></li><li>&nbsp;</li>
                        </ul>
                        <?php
                    }
                    $sector_args = array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'number' => $sector_show_count,
                        'fields' => 'all',
                        'slug' => '',
                        'hide_empty' => false,
                        'parent' => $sector_parent_id,
                    );
                    $sector_all_args = array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'fields' => 'all',
                        'slug' => '',
                        'hide_empty' => false,
                        'parent' => $sector_parent_id,
                    );
                    $all_sector = get_terms('sector', $sector_args);
                    if (count($all_sector) <= 0) {
                        $sector_args = array(
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'number' => $sector_show_count,
                            'fields' => 'all',
                            'hide_empty' => false,
                            'slug' => '',
                            'parent' => isset($selected_spec->parent) ? $selected_spec->parent : 0,
                        );
                        $sector_all_args = array(
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'fields' => 'all',
                            'hide_empty' => false,
                            'slug' => '',
                            'parent' => isset($selected_spec->parent) ? $selected_spec->parent : 0,
                        );
                        $all_sector = get_terms('sector', $sector_args);
                        if (isset($selected_spec->parent) && $selected_spec->parent != 0) {
                            $input_type_sector = 'checkbox';
                        }
                    } else {
                        if ($sector_parent_id != 0) {    // if parent is not root means not main parent
                            $input_type_sector = 'checkbox';   // if first level then select multiple sector
                        }
                    }

                    if (!empty($all_sector)) {
                        $number_option = 1;
                        $show_sector = 'yes';
                        if ($input_type_sector == 'radio' && $sector != '') {
                            if (is_array($sector) && is_array_empty($sector)) {
                                $show_sector = 'yes';
                            } else {
                                $show_sector = 'no';
                            }
                        } else {
                            $show_sector = 'yes';
                        }
                        if ($show_sector == 'yes') {

                            if ($input_type_sector == 'checkbox') {
                                
                            }
                            $number_option_flag = 1;
                            echo '<ul class="jobsearch-checkbox">';
                            foreach ($all_sector as $sectoritem) {
                                $sector_count_post = jobsearch_get_taxanomy_type_item_count($left_filter_count_switch, $sectoritem->slug, 'sector', $args_count);
                                $employer_id_para = '';

                                if ($input_type_sector == 'checkbox') {
                                    ?>
                                    <li class="jobsearch-<?php echo $input_type_sector; ?><?php echo ($number_option_flag > 6 ? ' filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                        <?php
                                        $sector_selected = '';
                                        if ($sector == $sectoritem->slug) {
                                            $sector_selected = ' checked="checked"';
                                        }
                                        $jobsearch_form_fields->radio_field(
                                                array(
                                                    'simple' => true,
                                                    'id' => 'sector_' . $number_option,
                                                    'cus_name' => $sector_name,
                                                    'std' => $sectoritem->slug,
                                                    'classes' => $sector_name,
                                                    'ext_attr' => ' onchange="jobsearch_employer_content_load(' . absint($global_rand_id) . ');"' . $sector_selected
                                                )
                                        );
                                        ?> 
                                        <label for="sector_<?php echo $number_option; ?>">
                                            <span></span><?php echo $sectoritem->name; ?>
                                        </label>
                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                            <span class="filter-post-count"><?php echo $sector_count_post; ?></span>
                                        <?php } ?>

                                    </li>
                                    <?php
                                } else
                                if ($input_type_sector == 'radio') {
                                    $sector_selected = '';
                                    if ($sector == $sectoritem->slug) {
                                        $sector_selected = ' checked="checked"';
                                    }
                                    ?>
                                    <li class="jobsearch-<?php echo $input_type_sector; ?><?php echo ($number_option_flag > 6 ? ' filter-more-fields' : '') ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                                        <?php
                                        $jobsearch_form_fields->radio_field(
                                                array(
                                                    'simple' => true,
                                                    'id' => 'sector_' . $number_option,
                                                    'cus_name' => $sector_name,
                                                    'std' => $sectoritem->slug,
                                                    'classes' => $sector_name,
                                                    'ext_attr' => ' onchange="jobsearch_employer_content_load(' . absint($global_rand_id) . ');"' . $sector_selected
                                                )
                                        );
                                        ?>
                                        <label for="sector_<?php echo $number_option; ?>">
                                            <span></span><?php echo $sectoritem->name; ?>
                                        </label>
                                        <?php if ($left_filter_count_switch == 'yes') { ?>
                                            <span class="filter-post-count"><?php echo $sector_count_post; ?></span>
                                        <?php } ?>
                                    </li>
                                    <?php
                                }
                                $number_option ++;
                                $number_option_flag ++;
                            }
                            echo '</ul>';
                            if ($number_option_flag > 6) {
                                echo '<a href="javascript:void(0);" class="show-toggle-filter-list">' . esc_html__('+ see more', 'wp-jobsearch') . '</a>';
                            }
                        }
                    } else {
                        ?>
                        <p><?php esc_html_e('No sector found. Please add from admin > job > sectors.', 'wp-jobsearch') ?></p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
        if ($employer_sector_filter == 'no') {
            $html = '';
        }

        return $html;
    }

    public function load_more_locations() {

        $page_num = isset($_POST['page_num']) && $_POST['page_num'] > 0 ? $_POST['page_num'] : 1;
        $global_rand_id = isset($_POST['param_rid']) ? $_POST['param_rid'] : 1;
        $left_filter_count_switch = isset($_POST['param_cousw']) ? $_POST['param_cousw'] : '';

        $loc_args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'fields' => 'all',
            'hide_empty' => false,
        );

        $all_locs = get_terms('job-location', $loc_args);

        if (!empty($all_locs)) {

            $h_list = self::get_terms_hierarchical($all_locs, '', 0, 0, $global_rand_id, array(), $left_filter_count_switch, 'array', false);
            $reults_per_page = 6;
            $start = ($page_num - 1) * ($reults_per_page);
            $offset = $reults_per_page;

            $paged_locs = array_slice($h_list, $start, $offset);

            $h_list_html = '';
            if (!empty($paged_locs)) {
                foreach ($paged_locs as $paged_loc) {
                    $h_list_html .= $paged_loc;
                }
            }

            echo json_encode(array('list' => $h_list_html));
        }
        die;
    }

    public static function get_terms_hierarchical($terms, $output = '', $parent_id = 0, $level = 0, $global_rand_id, $args_count, $left_filter_count_switch, $output_type = 'html', $output_break = true, $html_array = array()) {

        global $jobsearch_form_fields, $job_location_flag, $loc_counter, $sitepress;

        $job_type_name = 'job-location';

        $job_type = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';

        foreach ($terms as $term) {
            if ($parent_id == $term->parent) {

                $job_type_count_post = '';

                $location_slug = $term->slug;

                $location_condition_arr = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'jobsearch_field_location_location1',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'jobsearch_field_location_location2',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'jobsearch_field_location_location3',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'jobsearch_field_location_location4',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'jobsearch_field_location_address',
                        'value' => $location_slug,
                        'compare' => 'LIKE',
                    ),
                );
                $job_args = array(
                    'posts_per_page' => '1',
                    'post_type' => 'employer',
                    'post_status' => 'publish',
                    'fields' => 'ids', // only load ids
                    'meta_query' => array(
                        $location_condition_arr,
                        array(
                            'key' => 'jobsearch_field_job_publish_date',
                            'value' => strtotime(current_time('d-m-Y H:i:s', 1)),
                            'compare' => '<=',
                        ),
                        array(
                            'key' => 'jobsearch_field_job_expiry_date',
                            'value' => strtotime(current_time('d-m-Y H:i:s', 1)),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => 'jobsearch_field_job_status',
                            'value' => 'approved',
                            'compare' => '=',
                        )
                    ),
                );
                $jobs_query = new WP_Query($job_args);
                $job_type_count_post = $jobs_query->found_posts;
                wp_reset_postdata();
                if (function_exists('icl_object_id')) {
                    $trans_able_options = $sitepress->get_setting('custom_posts_sync_option', array());
                    if ($job_type_count_post == 0 && isset($trans_able_options['employer']) && $trans_able_options['employer'] == '2') {
                        $sitepress_def_lang = $sitepress->get_default_language();
                        $sitepress_curr_lang = $sitepress->get_current_language();
                        $sitepress->switch_lang($sitepress_def_lang, true);
                        
                        $loc_taxnomy = get_term_by('slug', $location_slug, 'job-location');
                        if (is_object($loc_taxnomy) && isset($loc_taxnomy->slug)) {
                            $job_args['meta_query'][0][0]['value'] = $loc_taxnomy->slug;
                            $job_args['meta_query'][0][1]['value'] = $loc_taxnomy->slug;
                            $job_args['meta_query'][0][2]['value'] = $loc_taxnomy->slug;
                            $job_args['meta_query'][0][3]['value'] = $loc_taxnomy->slug;
                            $job_args['meta_query'][0][4]['value'] = $loc_taxnomy->slug;
                        }
                        $ljob_query = new WP_Query($job_args);
                        wp_reset_postdata();
                        $job_type_count_post = $ljob_query->found_posts;

                        $sitepress->switch_lang($sitepress_curr_lang, true);
                    }
                }

                ob_start();
                ?>                    
                <li class="<?php echo 'location-level-' . $level ?><?php echo ($left_filter_count_switch != 'yes' ? ' no-filter-counts' : '') ?>">
                    <?php
                    $job_type_selected = '';
                    if ($job_type == $term->slug) {
                        $job_type_selected = ' checked="checked"';
                    }
                    $jobsearch_form_fields->radio_field(
                            array(
                                'simple' => true,
                                'id' => 'job_location_' . $job_location_flag,
                                'cus_name' => 'location',
                                'std' => $term->slug,
                                'ext_attr' => 'onchange="jobsearch_employer_content_load(\'' . absint($global_rand_id) . ' \')"' . $job_type_selected,
                            )
                    );
                    ?>
                    <label for="<?php echo force_balance_tags('job_location_' . $job_location_flag) ?>"><span></span><?php echo force_balance_tags($term->name); ?></label>
                    <?php if ($left_filter_count_switch == 'yes') { ?>
                        <span class="filter-post-count"><?php echo absint($job_type_count_post); ?></span>
                    <?php } ?>
                </li>
                <?php
                $job_location_flag++;
                $loc_counter++;

                if ($output_type == 'array') {
                    $output = ob_get_clean();
                } else {
                    $output .= ob_get_clean();
                }
                $html_array[] = $output;
                if ($output_type == 'array') {
                    $html_array = self::get_terms_hierarchical($terms, $output, $term->term_id, $level + 1, $global_rand_id, $args_count, $left_filter_count_switch, $output_type, $output_break, $html_array);
                } else {
                    $output = self::get_terms_hierarchical($terms, $output, $term->term_id, $level + 1, $global_rand_id, $args_count, $left_filter_count_switch, $output_type, $output_break, $html_array);
                }

                if ($loc_counter > 6 && $output_break === true) {
                    break;
                }
            }
        }
        if ($output_type == 'array') {
            return $html_array;
        }
        return $output;
    }

    static function jobsearch_employer_filter_location_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        global $jobsearch_form_fields, $employer_location_flag, $loc_counter;
        $job_type_name = 'job-location';

        $loc_counter = 1;

        $job_type = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';

        $employer_loc_filter = isset($sh_atts['employer_filters_loc']) ? $sh_atts['employer_filters_loc'] : '';
        $employer_loc_filter_view = isset($sh_atts['employer_filters_loc_view']) ? $sh_atts['employer_filters_loc_view'] : '';
        $loc_filter_collapse = isset($sh_atts['employer_filters_loc_collapse']) ? $sh_atts['employer_filters_loc_collapse'] : '';
        ob_start();
        ?>

        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($loc_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>">
                <h2><a href="javascript:void(0);" class="jobsearch-click-btn"><?php echo esc_html__('Locations', 'wp-jobsearch'); ?></a></h2>
                <?php
                if ($employer_loc_filter_view == 'input') {
                    ?>
                    <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($loc_filter_collapse == 'yes' ? 'none' : 'block') ?>;">   
                        <ul class="jobsearch-checkbox">
                            <li>
                                <input type="text" name="location" placeholder="<?php echo esc_html__('Search by Location', 'wp-jobsearch'); ?>" value="<?php echo ($job_type) ?>" onchange="jobsearch_employer_content_load(<?php echo absint($global_rand_id); ?>)">
                            </li>
                        </ul>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($loc_filter_collapse == 'yes' ? 'none' : 'block') ?>;"> 
                        <?php
                        // parse query string and create hidden fileds
                        $job_type_args = array(
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'fields' => 'all',
                            'hide_empty' => false,
                        );

                        $all_job_type = get_terms('job-location', $job_type_args);

                        $total_pages = 1;
                        $total_records = !empty($all_job_type) ? count($all_job_type) : 0;
                        $reults_per_page = 6;
                        if ($total_records > 0 && $reults_per_page > 0 && $total_records > $reults_per_page) {
                            $total_pages = ceil($total_records / $reults_per_page);
                        }

                        // get all job types

                        if (!empty($all_job_type)) {
                            echo '<ul class="jobsearch-checkbox"> ';
                            $job_location_flag = 1;
                            echo self::get_terms_hierarchical($all_job_type, '', 0, 0, $global_rand_id, $args_count, $left_filter_count_switch);
                            echo '</ul>';
                        } else {
                            ?>
                            <p><?php esc_html_e('No location found. Please add from admin > job > locations.', 'wp-jobsearch') ?></p>
                            <?php
                        }

                        if ($loc_counter > 6) {
                            echo '<a href="javascript:void(0);" class="show-toggle-filter-list jobsearch-loadmore-locations" data-pnum="2" data-tpgs="' . $total_pages . '" data-rid="' . $global_rand_id . '" data-cousw="' . $left_filter_count_switch . '">' . esc_html__('+ see more', 'wp-jobsearch') . ' <small class="loc-filter-loder"></small></a>';
                        }
                        ?>

                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
        if ($employer_loc_filter == 'no') {
            $html = '';
        }
        return $html;
    }

}

// class Jobsearch_EmployerFilterHTML 
$Jobsearch_EmployerFilterHTML_obj = new Jobsearch_EmployerFilterHTML();
global $Jobsearch_EmployerFilterHTML_obj;
