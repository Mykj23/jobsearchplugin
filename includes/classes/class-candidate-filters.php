<?php
/*
  Class : CandidateFilterHTML
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class Jobsearch_CandidateFilterHTML {

// hook things up
    public function __construct() {
        add_filter('jobsearch_candidate_filter_date_posted_box_html', array($this, 'jobsearch_candidate_filter_date_posted_box_html_callback'), 1, 5);
        add_filter('jobsearch_candidate_filter_sector_box_html', array($this, 'jobsearch_candidate_filter_sector_box_html_callback'), 1, 5);
    }

    static function jobsearch_candidate_filter_date_posted_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        $posted = isset($_REQUEST['posted']) ? $_REQUEST['posted'] : '';
        $rand = rand(234, 34234);
        $default_date_time_formate = 'd-m-Y H:i:s';
        $current_timestamp = current_time('timestamp');

        $candidate_date_filter = isset($sh_atts['candidate_filters_date']) ? $sh_atts['candidate_filters_date'] : '';

        $date_filter_collapse = isset($sh_atts['candidate_filters_date_collapse']) ? $sh_atts['candidate_filters_date_collapse'] : '';
        ob_start();
        ?>
        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($date_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>"">
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
                            $last_hour_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $last_hour_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="lasthour<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'lasthour') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="lasthour" />
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
                            $last24_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $last24_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="last24<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'last24') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="last24" />
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
                            $days7_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $days7_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="7days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '7days') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="7days" />
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
                            $days14_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $days14_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="14days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '14days') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="14days" />
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
                            $days30_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $days30_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="30days<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == '30days') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="30days" />
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
                            $all_days_totnum = jobsearch_get_candidate_item_count($left_filter_count_switch, $args_count, $all_days_count_arr, $global_rand_id, 'posted');
                            ?>
                            <input id="all<?php echo absint($rand); ?>" type="radio" name="posted" <?php if ($posted == 'all' || $posted == '') echo 'checked="checked"'; ?> onchange="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);" value="all" />
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
        if ($candidate_date_filter == 'no') {
            $html = '';
        }
        return $html;
    }

    static function jobsearch_candidate_filter_sector_box_html_callback($html, $global_rand_id, $args_count, $left_filter_count_switch, $sh_atts) {
        global $jobsearch_form_fields;
        $sector_name = 'sector_cat';
        $sector = isset($_REQUEST[$sector_name]) ? $_REQUEST[$sector_name] : '';

        $candidate_sector_filter = isset($sh_atts['candidate_filters_sector']) ? $sh_atts['candidate_filters_sector'] : '';
        $sec_filter_collapse = isset($sh_atts['candidate_filters_sector_collapse']) ? $sh_atts['candidate_filters_sector_collapse'] : '';
        ob_start();
        ?>
        <div class="jobsearch-filter-responsive-wrap">
            <div class="jobsearch-search-filter-wrap jobsearch-search-filter-toggle <?php echo ($sec_filter_collapse == 'yes' ? 'jobsearch-remove-padding' : '') ?>">
                <h2><a href="javascript:void(0);" class="jobsearch-click-btn"><?php echo esc_html__('Sector', 'wp-jobsearch') ?></a></h2>
                <div class="jobsearch-checkbox-toggle" style="display: <?php echo ($sec_filter_collapse == 'yes' ? 'none' : 'block') ?>;"> 

                    <?php
                    // get all candidate types

                    $sector_parent_id = 0;
                    $sector_show_count = 0;
                    $input_type_sector = 'radio';   // if first level then select only sigle sector
                    if ($sector != '') {
                        $selected_spec = get_term_by('slug', $sector, 'sector');
                        if (isset($selected_spec->term_id))
                            $sector_parent_id = $selected_spec->term_id;
                        ?>
                        <ul class="jobsearch-checkbox">
                            <li><a href="javascript:void(0);" onclick="jobsearch_candidate_content_load(<?php echo absint($global_rand_id); ?>);"><?php echo esc_html__('View all sector', 'wp-jobsearch'); ?></a></li><li>&nbsp;</li>
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
                                $candidate_id_para = '';

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
                                                    'ext_attr' => ' onchange="jobsearch_candidate_content_load(' . absint($global_rand_id) . ');"' . $sector_selected
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
                                                    'ext_attr' => ' onchange="jobsearch_candidate_content_load(' . absint($global_rand_id) . ');"' . $sector_selected
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
        if ($candidate_sector_filter == 'no') {
            $html = '';
        }

        return $html;
    }

}

// class Jobsearch_CandidateFilterHTML 
$Jobsearch_CandidateFilterHTML_obj = new Jobsearch_CandidateFilterHTML();
global $Jobsearch_CandidateFilterHTML_obj;
