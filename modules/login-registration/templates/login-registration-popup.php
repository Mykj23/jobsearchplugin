<?php
/*
  Class : Login Registration Popup
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
    die;
}

// main plugin class
class JobSearch_Login_Registration_Popup {

    // hook things up
    public function __construct() {
        add_action('wp_footer', array($this, 'popup_html_callback'), 10, 1);
    }

    public function popup_html_callback($args = array()) {
        $rand_numb = rand(1000000, 9999999);
        if (!is_user_logged_in()) {
            $args = array(
                'type' => 'popup',
            );
            ?>
            <div class="jobsearch-modal jobsearch-typo-wrap fade" id="JobSearchModalLogin">
                <div class="modal-inner-area">&nbsp;</div>
                <div class="modal-content-area">
                    <div class="modal-box-area">
                        <?php do_action('login_reg_popup_html', $args) ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

}

// class JobSearch_Login_Registration_Template 
$JobSearch_Login_Registration_Popup_obj = new JobSearch_Login_Registration_Popup();
