var $ = jQuery;

jQuery(document).on('click', '.jobsearch-subscribe-cv-pkg', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    var this_loader = jQuery(this).next('.pkg-loding-msg');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_packages_vars.ajax_url,
        method: "POST",
        data: {
            pkg_id: this_id,
            action: 'jobsearch_user_cv_pckg_subscribe',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);
        }
        if (typeof response.redirect_url !== 'undefined' && response.redirect_url != '') {
            window.location.replace(response.redirect_url);
            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_packages_vars.error_msg);
    });
});

jQuery(document).on('click', '.jobsearch-subscribe-candidate-pkg', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    var this_loader = jQuery(this).next('.pkg-loding-msg');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_packages_vars.ajax_url,
        method: "POST",
        data: {
            pkg_id: this_id,
            action: 'jobsearch_user_candidate_pckg_subscribe',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);
        }
        if (typeof response.redirect_url !== 'undefined' && response.redirect_url != '') {
            window.location.replace(response.redirect_url);
            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_packages_vars.error_msg);
    });
});

jQuery(document).on('click', '.jobsearch-subscribe-job-pkg', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    var this_loader = jQuery(this).next('.pkg-loding-msg');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_packages_vars.ajax_url,
        method: "POST",
        data: {
            pkg_id: this_id,
            action: 'jobsearch_user_job_pckg_subscribe',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);
        }
        if (typeof response.redirect_url !== 'undefined' && response.redirect_url != '') {
            window.location.replace(response.redirect_url);
            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_packages_vars.error_msg);
    });
});