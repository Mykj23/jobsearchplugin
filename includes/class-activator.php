<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    JobSearch_plugin
 * @subpackage JobSearch_plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 */
class JobSearch_plugin_Activator {
    
     public function __construct() {
         
     }
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        update_option('wp_jobsearch_plugin_active', 'yes');
        do_action('jobsearch_plugin_activator_hook');
    }
}
