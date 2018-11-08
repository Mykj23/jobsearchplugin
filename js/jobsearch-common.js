var $ = jQuery;
$(document).ready(function () {
    'use strict';

    jQuery('.user_field').on('click', function (e) {
        e.preventDefault();
        var this_id = jQuery(this).data('randid'),
                loaded = jQuery(this).data('loaded'),
                role = jQuery(this).data('role'),
                user_field = jQuery('#user_field_' + this_id),
                ajax_url = jobsearch_plugin_vars.ajax_url,
                force_std = jQuery(this).data('forcestd');
        if (loaded != true) {
            jQuery('.user_loader_' + this_id).html('<i class="fa fa-refresh fa-spin"></i>');
            var request = jQuery.ajax({
                url: ajax_url,
                method: "POST",
                data: {
                    force_std: force_std,
                    role: role,
                    action: 'jobsearch_load_all_users_data',
                },
                dataType: "json"
            });

            request.done(function (response) {
                if ('undefined' !== typeof response.html) {
                    user_field.html(response.html);
                    jQuery('.user_loader_' + this_id).html('');
                    user_field.data('loaded', true);

                }
            });

            request.fail(function (jqXHR, textStatus) {
            });
        }
        return false;

    });

    jQuery('.custom_post_field').on('click', function (e) {
        e.preventDefault();
        var this_id = jQuery(this).data('randid'),
                loaded = jQuery(this).data('loaded'),
                posttype = jQuery(this).data('posttype'),
                custom_field = jQuery('#custom_post_field_' + this_id),
                ajax_url = jobsearch_plugin_vars.ajax_url,
                force_std = jQuery(this).data('forcestd');
        if (loaded != true) {
            jQuery('.custom_post_loader_' + this_id).html('<i class="fa fa-refresh fa-spin"></i>');
            var request = jQuery.ajax({
                url: ajax_url,
                method: "POST",
                data: {
                    force_std: force_std,
                    posttype: posttype,
                    action: 'jobsearch_load_all_custom_post_data',
                },
                dataType: "json"
            });

            request.done(function (response) {
                if ('undefined' !== typeof response.html) {
                    custom_field.html(response.html);
                    jQuery('.custom_post_loader_' + this_id).html('');
                    custom_field.data('loaded', true);
                }
            });

            request.fail(function (jqXHR, textStatus) {
            });
        }
        return false;

    });

});

function jobsearch_multicap_all_functions() {
    var all_elements = jQuery(".g-recaptcha");
    for (var i = 0; i < all_elements.length; i++) {
        var id = all_elements[i].getAttribute('id');
        var site_key = all_elements[i].getAttribute('data-sitekey');
        if (null != id) {
            grecaptcha.render(id, {
                'sitekey': site_key
            });
        }
    }
}

function jobsearch_captcha_reload(admin_url, captcha_id) {
    "use strict";
    var dataString = '&action=jobsearch_captcha_reload&captcha_id=' + captcha_id;
    jQuery.ajax({
        type: "POST",
        url: admin_url,
        data: dataString,
        dataType: 'html',
        success: function (data) {
            jQuery("#" + captcha_id + "_div").html(data);
//            jQuery('.g-recaptcha').each(function () {
//                jQuery(this).find('iframe:first')
//                        .removeAttr('width')
//                        .addClass('img-responsive')
//                        .parent().parent()
//                        .css({'width': 'auto'});
//            });
        }
    });
}


jQuery(window).load(function () {
    var $iframe = $('iframe');
    $iframe.ready(function () {
        if ($iframe.length > 0) {
            $iframe.attr('id', 'gre-iframe');
        }
        //$iframe.contents().find("body").append('<div class="sdsdsdssdsdsdssd">fdg fgd gg dfg dfg dfg df gdfg</div>');
        $iframe.find("body").append('<div class="sdsdsdssdsdsdssd">fdg fgd gg dfg dfg dfg df gdfg</div>');
    });




});

window.djangoReCaptcha = {
    list: [],
    setup: function () {
        $('.g-recaptcha').each(function () {
            var $container = $(this);
            var config = $container.data();

            alert($container.attr('class'));

            djangoReCaptcha.init($container, config);
        });

        $(window).on('resize orientationchange', function () {
            $(djangoReCaptcha.list).each(function (idx, el) {
                djangoReCaptcha.resize.apply(null, el);
            });
        });
    },
    init: function ($container, config) {
        grecaptcha.render($container.get(0), config);
        alert(3434);
        var captchaSize, scaleFactor;
        var $iframe = $container.find('iframe').eq(0);

        $iframe.on('load', function () {
            $container.addClass('g-recaptcha-initted');
            captchaSize = captchaSize || {w: $iframe.width() - 2, h: $iframe.height()};
            djangoReCaptcha.resize($container, captchaSize);
            djangoReCaptcha.list.push([$container, captchaSize]);
        });
    },
};

window.djangoReCaptchaSetup = window.djangoReCaptcha.setup;

jQuery(document).on('click', '.load-more-team', function () {

    var _this = jQuery(this),
            total_pages = _this.attr('data-pages'),
            cur_page = _this.attr('data-page'),
            this_rand = _this.attr('data-rand'),
            employer_id = _this.attr('data-id'),
            ajax_url = jobsearch_plugin_vars.ajax_url;

    var members_holder = jQuery('#members-holder-' + this_rand);
    var this_html = _this.html();

    if (!_this.hasClass('jobsearch-loading')) {
        _this.addClass('jobsearch-loading');
        _this.html('<i class="fa fa-refresh fa-spin"></i> ' + jobsearch_plugin_vars.loading);
        var request = jQuery.ajax({
            url: ajax_url,
            method: "POST",
            data: {
                total_pages: total_pages,
                cur_page: cur_page,
                employer_id: employer_id,
                action: 'jobsearch_load_employer_team_next_page',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if ('undefined' !== typeof response.html && response.html != '') {
                members_holder.append(response.html);
                members_holder.find('.new-entries').slideDown().removeClass('new-entries');
                var current_page = parseInt(cur_page) + 1;
                _this.attr('data-page', current_page);
                if (current_page == total_pages) {
                    _this.hide();
                }
            }
            _this.html(this_html);
            _this.removeClass('jobsearch-loading');
        });

        request.fail(function (jqXHR, textStatus) {
            _this.html(this_html);
            _this.removeClass('jobsearch-loading');
        });
    }
    return false;

});

jQuery(document).on('click', ".jobsearch-click-btn", function () {
    jQuery(this).parents('.jobsearch-search-filter-toggle').find('.jobsearch-checkbox-toggle').slideToggle("slow");
    jQuery(this).parents('.jobsearch-search-filter-toggle').toggleClass("jobsearch-remove-padding");
    return false;
});