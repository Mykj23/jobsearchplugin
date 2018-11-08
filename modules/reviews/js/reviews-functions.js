jQuery(document).ready(function () {
    if (jQuery('select[id^="review-stars-selector"]').length > 0) {
        jQuery('select[id^="review-stars-selector"]').barrating({
            theme: 'fontawesome-stars',
            showSelectedRating: false
        });
    }
});

jQuery(document).on('click', '.jobsearch-go-to-review-form', function () {
    var _this_target = jQuery(this).attr('data-target');
    if (jQuery("#" + _this_target).length > 0) {
        jQuery('html, body').animate({
            scrollTop: eval(jQuery("#" + _this_target).offset().top - 100)
        }, 1000);
    }
});

jQuery(document).on('change', '.review-stars-holder select[id^="review-stars-selector"]', function () {
    var _this = jQuery(this);
    var total_rating = 0;
    var count_ratings = 0;
    jQuery('.review-stars-sec select[id^="review-stars-selector"]').each(function (index, element) {
        var each_this = jQuery(this);
        total_rating += parseInt(each_this.val());
        count_ratings++;
    });
    if (count_ratings > 0) {
        var avg_rating = total_rating/count_ratings;
        jQuery('.review-overall-stars-sec .rating-num').html(avg_rating.toFixed(1));
        
        var avg_rating_perc = (avg_rating / 5) * 100;
        jQuery('.review-overall-stars-sec .jobsearch-company-rating-box').css({'width': avg_rating_perc + '%'});
    }
});

jQuery('form#jobsearch-review-form').on('submit', function (e) {
    e.preventDefault();
    var _form = jQuery(this);
    var submit_btn = _form.find('#jobsearch-review-submit-btn');
    var msg_con = _form.find('.jobsearch-review-msg');
    var loader_con = _form.find('.jobsearch-review-loader');
    var review_desc = _form.find('textarea[name="user_comment"]');
    var form_data = _form.serialize();

    if (review_desc.val() == '') {
        review_desc.css({"border": "1px solid #ff0000"});
        return false;
    }

    if (!submit_btn.hasClass('jobsearch-loading')) {
        review_desc.css({"border": "1px solid #eceeef"});
        submit_btn.addClass('jobsearch-loading');
        msg_con.hide();
        loader_con.show();
        loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_plugin_vars.ajax_url,
            method: "POST",
            data: form_data,
            dataType: "json"
        });

        request.done(function (response) {
            if ('undefined' !== typeof response.msg && response.msg != '') {
                msg_con.html(response.msg);
                msg_con.slideDown();
            }
            if ('undefined' !== typeof response.error && response.error == '1') {
                msg_con.removeClass('alert-success').addClass('alert-danger');
            } else {
                msg_con.removeClass('alert-danger').addClass('alert-success');
            }
            submit_btn.removeClass('jobsearch-loading');
            loader_con.hide();
            loader_con.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            submit_btn.removeClass('jobsearch-loading');
            loader_con.hide();
            loader_con.html('');
        });
    }

    return false;
});

jQuery(".jobsearch-company-review figure").hover(function(){
    jQuery(this).parent('li').find('.review-detail-popover').slideDown();
},function(){
    jQuery(this).parent('li').find('.review-detail-popover').slideUp();
});