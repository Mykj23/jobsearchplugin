var $ = jQuery;
$(document).on('click', '.email-jobs-top', function () {
    $(".job-alert-container-top .validation").slideUp();
    $(".job-alert-container-top").slideToggle();
    $(".job-alert-container-top").parent('.jobsearch-search-filter-wrap').toggleClass('jobsearch-add-padding');
    return false;
});

$(document).on('click', '.btn-close-job-alert-box', function () {
    $(".job-alert-container-top").slideToggle();
    return false;
});

$(document).on('click', '.jobalert-submit', function () {
    $(".job-alert-container-top .validation").slideUp();
    $(".jobalert-submit").attr('disabled', true);
    $(".jobalert-submit").html('<i class="fa fa-refresh fa-spin"></i>');
    var email = $(".email-input-top").val();

    var name = $(".name-input-top").val();

    var frequency = $('input[name="alert-frequency"]:checked').val();
    if (typeof frequency == "undefined") {
        frequency = "never";
    }
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (re.test(email)) {
        var request = $.ajax({
            "type": "POST",
            "url": jobsearch_jobalerts_vars.ajax_url,
            "data": {
                "action": "jobsearch_create_job_alert",
                "email": email,
                "name": name,
                "frequency": frequency,
                "window_location": window.location.toString(),
                "search_query": $(".jobs_query").text(),
            },
            "dataType": "json",
        });
        request.done(function (response) {
            if (response.success == true) {
                $(".job-alert-container-top .validation").removeClass("error").addClass("success").slideDown();
                $(".job-alert-container-top .validation label").text(response.message);
            } else {
                $(".job-alert-container-top .validation").removeClass("success").addClass("error").slideDown();
                $(".job-alert-container-top .validation label").text(response.message);
            }
            $(".jobalert-submit").html(jobsearch_jobalerts_vars.submit_txt);
            $(".jobalert-submit").removeAttr('disabled');
        });
        request.fail(function (jqXHR, textStatus) {
            $(".jobalert-submit").html(jobsearch_jobalerts_vars.submit_txt);
            $(".jobalert-submit").removeAttr('disabled');
        });
    } else {
        $(".jobalert-submit").html(jobsearch_jobalerts_vars.submit_txt);
        $(".jobalert-submit").removeAttr('disabled');
        $(".job-alert-container-top .validation").addClass("error").slideDown();
    }
    return false;
});

jQuery(document).on('click', '.jobsearch-del-user-job-alert', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    if (this_id > 0) {
        var conf = confirm('Are you sure!');
        if (conf) {
            _this.find('i').attr('class', 'fa fa-refresh fa-spin');
            var request = jQuery.ajax({
                url: jobsearch_jobalerts_vars.ajax_url,
                method: "POST",
                data: {
                    'alert_id': this_id,
                    'action': 'jobsearch_user_job_alert_delete',
                },
                dataType: "json"
            });

            request.done(function (response) {
                _this.parents('tr').fadeOut();
            });

            request.fail(function (jqXHR, textStatus) {
                _this.parents('tr').fadeOut();
            });
        }
    }
});