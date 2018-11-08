<?php

/**
 * Class JobsearchTwitter
 */
class JobsearchTwitter {

    /**
     * Twitter APP ID
     *
     * @var string
     */
    private $consumer_key = '';
    private $consumer_secret = '';
    private $access_token = '';
    private $token_secret = '';
    private $redirect_url = '';
    //
    private $twitter_details;

    /**
     * JobsearchTwitter constructor.
     */
    public function __construct() {

        global $jobsearch_plugin_options;

        $consumer_key = isset($jobsearch_plugin_options['jobsearch-twitter-consumer-key']) ? $jobsearch_plugin_options['jobsearch-twitter-consumer-key'] : '';
        $consumer_secret = isset($jobsearch_plugin_options['jobsearch-twitter-consumer-secret']) ? $jobsearch_plugin_options['jobsearch-twitter-consumer-secret'] : '';
        $access_token = isset($jobsearch_plugin_options['jobsearch-twitter-access-token']) ? $jobsearch_plugin_options['jobsearch-twitter-access-token'] : '';
        $token_secret = isset($jobsearch_plugin_options['jobsearch-twitter-token-secret']) ? $jobsearch_plugin_options['jobsearch-twitter-token-secret'] : '';

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->access_token = $access_token;
        $this->token_secret = $token_secret;

        $this->redirect_url = home_url('/');

        // We register our shortcode
        add_shortcode('jobsearch_twitter_login', array($this, 'renderShortcode'));

        if (!isset($_GET['jobsearch_instagram_login'])) {
            //
            add_action('wp_ajax_jobsearch_twitter', array($this, 'twitter_connect'));
            add_action('wp_ajax_nopriv_jobsearch_twitter', array($this, 'twitter_connect'));
            add_action('wp_ajax_twitter_callback', array($this, 'twitter_callback'));
            add_action('wp_ajax_nopriv_twitter_callback', array($this, 'twitter_callback'));
        }
    }

    /**
     * Render the shortcode [jobsearch_twitter_login/]
     *
     * It displays our Login / Register button
     */
    public function renderShortcode() {

        // Messages
        if (get_transient('jobsearch_twitter_message')) {

            $message = get_transient('jobsearch_twitter_message');
            echo '<div class="alert alert-danger jobsearch-twitter-message">' . $message . '</div>';
            // We remove them from the session
            delete_transient('jobsearch_twitter_message');
        }
        echo '<li><a class="jobsearch-twitter-bg" data-original-title="twitter" href="' . admin_url('admin-ajax.php?action=jobsearch_twitter') . '"><i class="fa fa-twitter"></i>' . __('Login with Twitter', 'wp-jobsearch') . '</a></li>';
    }

    public function twitter_connect() {

        if (!class_exists('TwitterOAuth')) {
            require_once jobsearch_plugin_get_path('includes/twitter-tweets/twitteroauth.php');
        }
        $consumer_key = $this->consumer_key;
        $consumer_secret = $this->consumer_secret;
        $twitter_oath_callback = admin_url('admin-ajax.php?action=twitter_callback');
        if ($consumer_key != '' && $consumer_secret != '') {

            $connection = new TwitterOAuth($consumer_key, $consumer_secret);

            $request_token = $connection->getRequestToken($twitter_oath_callback);

            if (!empty($request_token)) {
                set_transient('oauth_token', $request_token['oauth_token'], (60 * 60 * 24));
                set_transient('oauth_token_secret', $request_token['oauth_token_secret'], (60 * 60 * 24));
                $token = $request_token['oauth_token'];
            }

            switch ($connection->http_code) {
                case 200:
                    $url = $connection->getAuthorizeURL($token);
                    wp_redirect($url);
                    break;
                default:
                    echo esc_html($connection->http_code);
                    esc_html_e('There is problem while connecting to twitter', 'wp-jobsearch');
            }
            exit();
        }
        wp_die();
    }

    public function twitter_callback() {
        if (!class_exists('TwitterOAuth')) {
            require_once jobsearch_plugin_get_path('includes/twitter-tweets/twitteroauth.php');
        }
        $consumer_key = $this->consumer_key;
        $consumer_secret = $this->consumer_secret;

        $oauth_token = get_transient('oauth_token');
        $oauth_token_secret = get_transient('oauth_token_secret');
        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
            $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
            set_transient('access_token', $access_token, (3600 * 60) * 24);
            delete_transient('oauth_token');
            delete_transient('oauth_token_secret');
        }

        if (200 == $connection->http_code) {
            set_transient('status', 'verified', (3600 * 60) * 24);
            $user = $connection->get('account/verify_credentials');
            $member_profile_image = $user->profile_image_url;

            $name = $user->name;
            $screen_name = $user->screen_name;
            $twitter_id = $user->id;
            $signature = $this->social_generate_signature($twitter_id);
            $this->social_login_verify_signature($twitter_id, $signature, $this->redirect_url);

            $this->twitter_details = $user;

            // We first try to login the user
            $this->loginUser();

            // Otherwise, we create a new account
            $this->createUser();
            // Redirect the user
            header("Location: " . $this->redirect_url, true);
        } else {
            esc_html_e('There is problem while connecting to twitter', 'wp-jobsearch');
        }
        die;
    }

    private function loginUser() {

        // We look for the `eo_facebook_id` to see if there is any match
        $wp_users = get_users(array(
            'meta_key' => 'jobsearch_twitter_id',
            'meta_value' => $this->twitter_details->id,
            'number' => 1,
            'count_total' => false,
            'fields' => 'id',
        ));

        if (empty($wp_users[0])) {
            return false;
        }

        // Log the user ?
        wp_set_auth_cookie($wp_users[0]);
        header("Location: " . $this->redirect_url, true);
        exit();
    }

    /**
     * Create a new WordPress account using Facebook Details
     */
    private function createUser() {

        $_user = $this->twitter_details;

        $site_url = parse_url(site_url());
        $user_email = 'tw_' . md5($_user->id) . '@' . $site_url['host'];

        if (isset($_user->email)) {
            $user_email = $_user->email;
            
            $_social_user_obj = get_user_by('email', $user_email);
            if (is_object($_social_user_obj) && isset($_social_user_obj->ID)) {
                update_user_meta($_social_user_obj->ID, 'jobsearch_twitter_id', $_user->id);
                $this->loginUser();
            }
        }

        // Create an username
        $username = sanitize_user(str_replace(' ', '_', strtolower($_user->name)));
        
        if (username_exists($username)) {
            $username .= '_' . rand(10000, 99999);
        }

        // Creating our user
        $new_user = wp_create_user($username, wp_generate_password(), $user_email);

        if (is_wp_error($new_user)) {
            // Report our errors
            set_transient('jobsearch_twitter_message', $new_user->get_error_message(), 60 * 60 * 24 * 30);
            echo $new_user->get_error_message();
            die;
        } else {

            // user role
            $user_role = 'jobsearch_candidate';
            wp_update_user( array( 'ID' => $new_user, 'role' => $user_role ) );
            
            // Setting the meta
            update_user_meta($new_user, 'first_name', (isset($_user->first_name) ? $_user->first_name : ''));
            update_user_meta($new_user, 'last_name', (isset($_user->last_name) ? $_user->last_name : ''));
            update_user_meta($new_user, 'jobsearch_twitter_id', (isset($_user->id) ? $_user->id : ''));

            // Log the user ?
            wp_set_auth_cookie($new_user);
        }
    }

    private function social_generate_signature($data) {
        return hash('SHA256', AUTH_KEY . $data);
    }

    private function social_login_verify_signature($data, $signature, $redirect_to) {
        $generated_signature = $this->social_generate_signature($data);
        if ($generated_signature != $signature) {
            wp_safe_redirect($redirect_to);
            exit();
        }
    }

}

/*
 * Starts our plugins, easy!
 */
new JobsearchTwitter();
