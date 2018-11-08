<?php
if (!function_exists('jobsearch_mailchimp_list')) {

    /**
     * Mailchimp list.
     *
     * @param string $apikey mailchimp shortcode api key.
     */
    function jobsearch_mailchimp_list($apikey) {

        $MailChimp = new MailChimp($apikey);
        $mailchimp_list = $MailChimp->call('lists/list');
        return $mailchimp_list;
    }

    /**
     * Mailchimp list.
     */
    if (!function_exists('jobsearch_mailchimp')) {

        add_action('wp_ajax_nopriv_jobsearch_mailchimp', 'jobsearch_mailchimp');
        add_action('wp_ajax_jobsearch_mailchimp', 'jobsearch_mailchimp');

        /**
         * Mailchimp.
         */
        function jobsearch_mailchimp() {
            global $jobsearch_plugin_options, $counter;
            $msg = array();

            $mailchimp_key = '';
            $jobsearch_list_id = '';
            if (isset($jobsearch_plugin_options['jobsearch-mailchimp-api-key'])) {
                $mailchimp_key = $jobsearch_plugin_options['jobsearch-mailchimp-api-key'];
            }
            if (isset($jobsearch_plugin_options['jobsearch-mailchimp-list'])) {
                $jobsearch_list_id = $jobsearch_plugin_options['jobsearch-mailchimp-list'];
            }
            $cp_email = isset($_POST['cp_email']) ? $_POST['cp_email'] : '';
            $cp_name = isset($_POST['cp_fname']) ? $_POST['cp_fname'] : '';
            if (isset($cp_email) && !empty($jobsearch_list_id) && '' !== $mailchimp_key) {
                if ($mailchimp_key <> '') {
                    $MailChimp = new MailChimp($mailchimp_key);
                }
                $email = $cp_email;
                $list_id = $jobsearch_list_id;
                $request_arr = array(
                    'id' => $list_id,
                    'email' => array('email' => $email),
                    'merge_vars' => array("FNAME" => $cp_name,
                        "LNAME" => ""
                        ), 
                    'double_optin' => false,
                    'update_existing' => false,
                    'replace_interests' => false,
                    'send_welcome' => true,
                ); 
                $result = $MailChimp->call('lists/subscribe', $request_arr);
                if ('' !== $result) { 
                    if (isset($result['status']) && 'error' === $result['status']) {
                        $msg['type'] = 'error';
                        $msg['msg'] = $result['error'];
                    } else {
                        $msg['type'] = 'success';
                        $msg['msg'] = __('Subscribed Successfully.', 'wp-jobsearch');
                    }
                }
            } else {
                $msg['type'] = 'error';
                $msg['msg'] = __('Please enter a valid API key.', 'wp-jobsearch');
            }
            echo json_encode($msg);
            die();
        }

    }
}

/**
 * Mailchimp frontend form.
 */
if (!function_exists('jobsearch_custom_mailchimp')) {

    /**
     * Mailchimp frontend form.
     *
     * @param bolean $under_construction checking under construction.
     */
    function jobsearch_custom_mailchimp($newsletter_place = '') {

        global $jobsearch_plugin_options, $counter;
        $jobsearch_email_address_str = __('Email Address', 'wp-jobsearch');
        $counter ++;
        ?>

        <script>
            function jobsearch_mailchimp_submit(counter, admin_url) {
                'use strict';
                var $ = jQuery;
                $('#newsletter_error_div_' + counter).fadeOut();
                $('#newsletter_success_div_' + counter).fadeOut();
                $('#process_' + counter).show();
                $('#process_' + counter).html('<i class="fa fa-refresh fa-spin"></i>');
                $.ajax({
                    type: 'POST',
                    url: admin_url,
                    data: $('#mcform_' + counter).serialize() + '&action=jobsearch_mailchimp',
                    dataType: "json",
                    success: function (response) {
                        $('#mcform_' + counter).get(0).reset();
                        if (response.type === 'error') {
                            $('#process_' + counter).hide();
                            $('#newsletter_mess_error_' + counter).html(response.msg);
                            $('#newsletter_error_div_' + counter).fadeIn();
                        } else {
                            $('#process_' + counter).hide();
                            $('#newsletter_mess_success_' + counter).html(response.msg);
                            $('#newsletter_success_div_' + counter).fadeIn();
                        }
                        $('#newsletter_mess_' + counter).fadeIn(600);
                        $('#newsletter_mess_' + counter).html(response);
                        $('#process_' + counter).html('');
                    }
                });
            }
            function hide_div(div_hide) {
                jQuery('#' + div_hide).hide();
            }
        </script>
        <div class="jobsearch-newsletter" id="process_newsletter_<?php echo intval($counter); ?>">
            <form action="javascript:jobsearch_mailchimp_submit('<?php echo esc_js($counter); ?>','<?php echo admin_url('admin-ajax.php'); ?>')" id="mcform_<?php echo intval($counter); ?>" method="post">
                <div class="input-holder">
                    <input id="jobsearch_list_id<?php echo intval($counter); ?>" type="hidden" name="jobsearch_list_id" value="<?php
                    if (isset($jobsearch_plugin_options['jobsearch-mailchimp-list'])) {
                        echo esc_attr($jobsearch_plugin_options['jobsearch-mailchimp-list']);
                    }
                    ?>" />
                    <input type="text" id="cp_email<?php echo intval($counter); ?>" name="cp_email" placeholder=" <?php echo esc_html($jobsearch_email_address_str); ?>">
                    <?php
                    if ($newsletter_place == 'widget') {
                        ?>
                        <input id="btn_newsletter_<?php echo intval($counter); ?>" type="submit" value=""><i class="fa fa-paper-plane"></i>
                        <?php
                    } else {
                        ?>
                        <label><input class="jobsearch-bgcolor" id="btn_newsletter_<?php echo intval($counter); ?>" type="submit" value="<?php //_e('Subscribe', 'wp-jobsearch') ?>"><i class="jobsearch-icon-note"></i></label>
                        <?php
                    }
                    ?>
                </div>
                <div id="process_<?php echo intval($counter); ?>" class="status status-message" style="display:none"></div>
            </form>
            <div id="newsletter_error_div_<?php echo intval($counter); ?>" style="display:none" class="alert alert-danger">
                <button class="close" type="button" onclick="hide_div('newsletter_error_div_<?php echo intval($counter); ?>')" aria-hidden="true">×</button>
                <p><i class="icon-warning"></i>
                    <span id="newsletter_mess_error_<?php echo intval($counter); ?>"></span></p>
            </div> 
            <div id="newsletter_success_div_<?php echo intval($counter); ?>" style="display:none" class="alert alert-success">
                <button class="close" type="button" onclick="hide_div('newsletter_success_div_<?php echo intval($counter); ?>')" aria-hidden="true">×</button>
                <p><i class="icon-checkmark"></i><span id="newsletter_mess_success_<?php echo intval($counter); ?>"></span></p>
            </div>
        </div>
        <?php
    }

}
