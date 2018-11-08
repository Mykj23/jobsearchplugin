var $ = jQuery;
jQuery(document).ready(function () {
    jQuery(".gal-all-imgs").sortable({
        handle: '.el-drag',
        cursor: 'move',
        items: '.gal-item',
    });
});

function jobsearch_dashboard_read_file_url(input) {

    if (input.files && input.files[0]) {

        var loader_con = jQuery('#user_avatar').parents('figcaption').find('.fileUpLoader');

        var img_file = input.files[0];
        var img_size = img_file.size;

        img_size = parseFloat(img_size / 1024).toFixed(2);

        if (img_size <= 1024) {
            loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
            var formData = new FormData();
            formData.append('avatar_file', img_file);
            formData.append('action', 'jobsearch_dashboard_updating_user_avatar_img');

            var request = $.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json"
            });
            request.done(function (response) {
                if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
                    loader_con.html(response.err_msg);
                    return false;
                }
                if (typeof response.imgUrl !== 'undefined') {
                    jQuery('#com-img-holder').find('img').attr('src', response.imgUrl);
                }
                loader_con.html('');
            });

            request.fail(function (jqXHR, textStatus) {
                loader_con.html(jobsearch_dashboard_vars.error_msg);
                loader_con.html('');
            });

        } else {
            alert(jobsearch_dashboard_vars.com_img_size);
        }
    }
}

jQuery(document).on('click', '.jobsearch-userdel-profilebtn', function () {
    jobsearch_modal_popup_open('JobSearchModalUserProfileDel');
});

jQuery(document).on('click', '.jobsearch-userdel-profile', function () {

    var loader_con = jQuery(this).parents('.profile-del-con').find('.loader-con');
    var msg_con = jQuery(this).parents('.profile-del-con').find('.msge-con');

    loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
    var typeu = jQuery(this).attr('data-type');
    var u_pass = jQuery(this).parents('.profile-del-con').find('#d_user_pass');
    var request = $.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            'type': typeu,
            'u_pass': u_pass.val(),
            'action': 'jobsearch_user_profile_delete_for'
        },
        dataType: "json"
    });
    request.done(function (response) {
        if (typeof response.success !== 'undefined' && response.success == '1') {
            msg_con.html(response.msg);
            var doin_refresh = setInterval(function () {
                window.location.reload(true);
                clearInterval(doin_refresh);
            }, 2000);
        } else {
            msg_con.html(response.msg);
        }
        loader_con.html('');
    });

    request.fail(function (jqXHR, textStatus) {
        loader_con.html('');
    });
});

jQuery(document).on('change', '#user_avatar', function () {
    jobsearch_dashboard_read_file_url(this);
});

function jobsearch_dashboard_read_cover_photo_url(input) {

    if (input.files && input.files[0]) {

        var loader_con = jQuery('#user_cvr_photo').parents('figcaption').find('.file-loader');

        var img_file = input.files[0];
        var img_size = img_file.size;

        img_size = parseFloat(img_size / 1024).toFixed(2);

        if (img_size <= 1024) {
            loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
            var formData = new FormData();
            formData.append('user_cvr_photo', img_file);
            formData.append('action', 'jobsearch_dashboard_updating_employer_cover_img');

            var request = $.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json"
            });
            request.done(function (response) {
                if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
                    loader_con.html(response.err_msg);
                    return false;
                }
                if (typeof response.imgUrl !== 'undefined') {
                    jQuery('#com-cvrimg-holder').find('img').attr('src', response.imgUrl);
                    jQuery('.jobsearch-employer-cvr-img').find('a.employer-remove-coverimg').show();
                }
                loader_con.html('');
            });

            request.fail(function (jqXHR, textStatus) {
                loader_con.html(jobsearch_dashboard_vars.error_msg);
                loader_con.html('');
            });

        } else {
            alert(jobsearch_dashboard_vars.com_img_size);
        }
    }
}

jQuery(document).on('change', '#user_cvr_photo', function () {
    jobsearch_dashboard_read_cover_photo_url(this);
});

jQuery(document).on('click', '.employer-remove-coverimg', function () {
    var _this = jQuery(this);
    var this_loader = _this.find('i');

    this_loader.attr('class', 'fa fa-refresh fa-spin');
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            cover_img: 'remove',
            action: 'jobsearch_employer_cover_img_remove',
        },
        dataType: "json"
    });

    request.done(function (response) {
        this_loader.attr('class', 'fa fa-times');
        _this.hide();
        _this.parents('.jobsearch-employer-cvr-img').find('figure img').attr('src', '');
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.attr('class', 'fa fa-times');
    });
});

function jobsearch_dashboard_cv_upload_url(input) {

    if (input.files && input.files[0]) {

        var loader_con = jQuery('#jobsearch-upload-cv-main').find('.fileUpLoader');

        var cv_file = input.files[0];
        var file_size = cv_file.size;
        var file_type = cv_file.type;
        var file_name = cv_file.name;
        jQuery('#jobsearch-uploadfile').attr('placeholder', file_name);
        jQuery('#jobsearch-uploadfile').val(file_name);

        var allowed_types = ["application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/pdf"];

        file_size = parseFloat(file_size / 1024).toFixed(2);

        if (file_size <= 1024) {
            if (allowed_types.indexOf(file_type) >= 0) {
                loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
                var formData = new FormData();
                formData.append('candidate_cv_file', cv_file);
                formData.append('action', 'jobsearch_dashboard_updating_candidate_cv_file');

                var request = $.ajax({
                    url: jobsearch_dashboard_vars.ajax_url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json"
                });
                request.done(function (response) {
                    if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
                        loader_con.html(response.err_msg);
                        return false;
                    }
                    if (typeof response.filehtml !== 'undefined' && response.filehtml != '') {
                        if (jobsearch_dashboard_vars.multiple_cvs_allow == 'on') {
                            jQuery('#com-file-holder').append(response.filehtml);
                            window.location.reload(true);
                        } else {
                            jQuery('#com-file-holder').html(response.filehtml);
                            jQuery('#com-file-holder').find('.jobsearch-cv-manager-list').slideDown();
                            jQuery('#jobsearch-upload-cv-main').slideUp();
                        }
                    }
                    loader_con.html('');
                });

                request.fail(function (jqXHR, textStatus) {
                    loader_con.html(jobsearch_dashboard_vars.error_msg);
                    loader_con.html('');
                });
            } else {
                alert(jobsearch_dashboard_vars.cv_file_types);
            }

        } else {
            alert(jobsearch_dashboard_vars.com_file_size);
        }
    }
}

jQuery(document).on('change', 'input[name="candidate_cv_file"]', function () {
    jobsearch_dashboard_cv_upload_url(this);
});

jQuery(document).on('click', '.user-dashboard-ajax-click', function () {
    var _this = jQuery(this);
    var dashboard_user_type = _this.attr('data-user-type');
    var dashboard_part = _this.attr('data-ajax-part');
    var dashboard_tab = _this.attr('data-ajax-tab');
    var dashboard_loader = jQuery('.user-dashboard-loader');

    var dashboard_url = jobsearch_dashboard_vars.dashboard_url;

    dashboard_loader.html('Loading...');
    dashboard_loader.show();
    if (_this.hasClass('has-loaded')) {
        var load_interval = setInterval(function () {

            if (dashboard_url.indexOf('?') != -1) {
                dashboard_url = dashboard_url + '&' + 'tab=' + dashboard_part;
            } else {
                dashboard_url = dashboard_url + '?' + 'tab=' + dashboard_part;
            }

            dashboard_loader.html('');
            dashboard_loader.hide();
            jQuery('.main-tab-section').hide();
            jQuery('#' + dashboard_tab).show();

            //
            _this.parents('ul').find('li').removeClass('active');
            _this.parents('li').addClass('active');
            //

            if (typeof history !== 'undefined' && history.pushState) {
                history.pushState({}, null, dashboard_url);
            }

            clearInterval(load_interval);
        }, 500);
    } else {
        var request = $.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                'user_type': dashboard_user_type,
                'template_name': dashboard_part,
                'action': 'jobsearch_user_dashboard_show_template',
            },
            dataType: "json"
        });

        if (dashboard_url.indexOf('?') != -1) {
            dashboard_url = dashboard_url + '&' + 'tab=' + dashboard_part;
        } else {
            dashboard_url = dashboard_url + '?' + 'tab=' + dashboard_part;
        }

        request.done(function (response) {
            if (typeof response.template_html !== 'undefined') {
                dashboard_loader.html('');
                dashboard_loader.hide();
                jQuery('.main-tab-section').hide();
                jQuery('#' + dashboard_tab).html(response.template_html);
                jQuery('#' + dashboard_tab).show();
                //
                _this.parents('ul').find('li').removeClass('active');
                _this.parents('li').addClass('active');
                //
                _this.addClass('has-loaded');
                if (typeof history !== 'undefined' && history.pushState) {
                    history.pushState({}, null, dashboard_url);
                }
            }
        });

        request.fail(function (jqXHR, textStatus) {
            dashboard_loader.html('');
            dashboard_loader.hide();
        });
    }
});

jQuery(document).on('click', '.jobsearch-trash-job', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    if (this_id > 0) {
        var conf = confirm('Are you sure!');
        if (conf) {
            _this.removeClass('jobsearch-icon');
            _this.removeClass('jobsearch-trash-job');
            _this.addClass('fa fa-refresh fa-spin');
            var request = jQuery.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: {
                    'job_id': this_id,
                    'action': 'jobsearch_user_dashboard_job_delete',
                },
                dataType: "json"
            });

            request.done(function (response) {
                _this.addClass('jobsearch-icon');
                _this.addClass('jobsearch-trash-job');
                _this.removeClass('fa fa-refresh fa-spin');
                if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
                    _this.removeClass('jobsearch-trash-job').html(response.err_msg);
                    return false;
                }
                if (typeof response.msg !== 'undefined' && response.msg == 'deleted') {
                    _this.parents('.jobsearch-managejobs-tbody').fadeOut();
                }
            });

            request.fail(function (jqXHR, textStatus) {
                _this.addClass('jobsearch-trash-job');
            });
        }
    }
});

jQuery(document).on('click', '.jobsearch-del-user-cv', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    if (this_id > 0) {
        var conf = confirm('Are you sure!');
        if (conf) {
            _this.find('i').attr('class', 'fa fa-refresh fa-spin');
            var request = jQuery.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: {
                    'attach_id': this_id,
                    'action': 'jobsearch_act_user_cv_delete',
                },
                dataType: "json"
            });

            request.done(function (response) {
                if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
                    _this.find('i').removeAttr('class').html(response.err_msg);
                    return false;
                }
                _this.parents('.jobsearch-cv-manager-list').slideUp();
                jQuery('#jobsearch-upload-cv-main').slideDown();
                window.location.reload(true);
            });

            request.fail(function (jqXHR, textStatus) {
                _this.parents('.jobsearch-cv-manager-list').slideUp();
            });
        }
    }
});

jQuery(document).on('click', '.jobsearch-delete-fav-job', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    var this_loader = jQuery(this).find('i');

    var this_loader_b_icon = this_loader.attr('class');

    this_loader.attr('class', 'fa fa-refresh fa-spin');
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            job_id: this_id,
            action: 'jobsearch_remove_user_fav_job_from_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
            this_loader.removeAttr('class').html(response.err_msg);
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {

            _this.parents('tr').fadeOut();
            this_loader.attr('class', this_loader_b_icon);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.attr('class', this_loader_b_icon);
        this_loader.html(jobsearch_dashboard_vars.error_msg);
    });
});

jQuery(document).on('click', '.jobsearch-delete-applied-job', function () {
    var _this = jQuery(this);
    var this_id = _this.attr('data-id');
    var this_key = _this.attr('data-key');
    var this_loader = jQuery(this).find('i');

    var this_loader_b_icon = this_loader.attr('class');

    this_loader.attr('class', 'fa fa-refresh fa-spin');
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            job_id: this_id,
            job_key: this_key,
            action: 'jobsearch_remove_user_applied_job_from_list',
        },
        dataType: "json"
    });

    request.done(function (response) {

        if (typeof response.err_msg !== 'undefined' && response.err_msg != '') {
            this_loader.removeAttr('class').html(response.err_msg);
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {

            _this.parents('li').fadeOut();
            this_loader.attr('class', this_loader_b_icon);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.attr('class', this_loader_b_icon);
        this_loader.html(jobsearch_dashboard_vars.error_msg);
    });
});

//
(function ($) {
    "use strict";
    $.fn.jobsearch_req_field_loop = function (callback, thisArg) {
        var me = this;
        return this.each(function (index, element) {
            return callback.call(thisArg || element, element, index, me);
        });
    };
})(jQuery);

function jobsearch_validate_dashboard_form(that) {
    "use strict";
    var req_class = 'jobsearch-req-field',
            _this_form = $(that),
            form_validity = 'valid';

    _this_form.find('.' + req_class).jobsearch_req_field_loop(function (element, index, set) {

        var eror_str = '';
        if ($(element).val() == '') {
            form_validity = 'invalid';
            eror_str = 'has_error';
        } else {
            $(element).css({"border": "1px solid #eceeef"});
        }

        if (eror_str != '') {
            $(element).css({"border": "1px solid #ff0000"});
        }
    });

    if (form_validity == 'valid') {
        return true;
    } else {
        return false;
    }
}
//

jQuery(document).on('click', '#add-education-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.edu-loding-msg');

    var title = jQuery('#add-edu-title');
    var year = jQuery('#add-edu-year');
    var institute = jQuery('#add-edu-institute');
    var desc = jQuery('#add-edu-desc');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            year: year.val(),
            institute: institute.val(),
            desc: desc.val(),
            action: 'jobsearch_add_resume_education_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-resume-edu-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            year.val('');
            institute.val('');
            desc.val('');

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

jQuery(document).on('click', '#add-experience-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.expr-loding-msg');

    var title = jQuery('#add-expr-title');
    var start_date = jQuery('#add-expr-date-start');
    var end_date = jQuery('#add-expr-date-end');
    var company = jQuery('#add-expr-company');
    var desc = jQuery('#add-expr-desc');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            start_date: start_date.val(),
            end_date: end_date.val(),
            company: company.val(),
            desc: desc.val(),
            action: 'jobsearch_add_resume_experience_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-resume-expr-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            start_date.val('');
            end_date.val('');
            company.val('');
            desc.val('');

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

jQuery(document).on('click', '#add-resume-skills-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.skills-loding-msg');

    var title = jQuery('#add-skill-title');
    var skill_percentage = jQuery('#add-skill-percentage');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            skill_percentage: skill_percentage.val(),
            action: 'jobsearch_add_resume_skill_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-resume-skills-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            skill_percentage.val('');

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

jQuery(document).on('click', '#add-resume-awards-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.awards-loding-msg');

    var title = jQuery('#add-award-title');
    var award_year = jQuery('#add-award-year');
    var award_desc = jQuery('#add-award-desc');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            award_year: award_year.val(),
            award_desc: award_desc.val(),
            action: 'jobsearch_add_resume_award_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);
            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-resume-awards-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            award_year.val('');
            award_desc.val('');

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

jQuery(document).on('click', '#add-resume-portfolio-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.portfolio-loding-msg');

    var title = jQuery('#add-portfolio-title');
    var portfolio_img = jQuery('#add-portfolio-img-input');
    var portfolio_url = jQuery('#add-portfolio-url');
    var portfolio_vurl = jQuery('#add-portfolio-vurl');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            portfolio_img: portfolio_img.val(),
            portfolio_url: portfolio_url.val(),
            portfolio_vurl: portfolio_vurl.val(),
            action: 'jobsearch_add_resume_portfolio_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);

            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-resume-portfolio-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            portfolio_url.val('');
            portfolio_vurl.val('');
            portfolio_img.val('');
            if (portfolio_img.parents('.upload-img-holder-sec').find('img').length > 0) {
                portfolio_img.parents('.upload-img-holder-sec').find('img').attr('src', '');
            }

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

jQuery(document).on('click', '#add-team-member-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var this_pcon = _this.parents('.jobsearch-add-resume-item-popup');
    jobsearch_validate_dashboard_form(this_pcon);

    var this_loader = _this.parent('li').find('.portfolio-loding-msg');

    var title = jQuery('#team_title');
    var portfolio_img = jQuery('#team_image_input');
    var team_designation = jQuery('#team_designation');
    var team_experience = jQuery('#team_experience');
    var team_facebook = jQuery('#team_facebook');
    var team_google = jQuery('#team_google');
    var team_twitter = jQuery('#team_twitter');
    var team_linkedin = jQuery('#team_linkedin');
    var team_description = jQuery('#team_description');

    this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
    this_loader.css({'background-color': '#32cd32'});
    this_loader.show();
    var request = jQuery.ajax({
        url: jobsearch_dashboard_vars.ajax_url,
        method: "POST",
        data: {
            title: title.val(),
            team_image: portfolio_img.val(),
            team_designation: team_designation.val(),
            team_experience: team_experience.val(),
            team_facebook: team_facebook.val(),
            team_google: team_google.val(),
            team_twitter: team_twitter.val(),
            team_linkedin: team_linkedin.val(),
            team_description: team_description.val(),
            action: 'jobsearch_add_team_member_to_list',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if (typeof response.error !== 'undefined' && response.error == '1') {
            //
            this_loader.html(response.msg);

            this_loader.css({'background-color': '#e40000'});
            return false;
        }
        if (typeof response.msg !== 'undefined' && response.msg != '') {
            this_loader.html(response.msg);

            if (typeof response.html !== 'undefined' && response.html != '') {
                jQuery('#jobsearch-team-members-con').find('>ul').append(response.html);
            } else {
                return false;
            }

            title.val('');
            team_designation.val('');
            team_experience.val('');
            team_facebook.val('');
            team_google.val('');
            team_twitter.val('');
            team_linkedin.val('');
            team_description.val('');
            portfolio_img.val('');
            if (portfolio_img.parents('.upload-img-holder-sec').find('img').length > 0) {
                portfolio_img.parents('.upload-img-holder-sec').find('img').attr('src', '');
            }

            return false;
        }
    });

    request.fail(function (jqXHR, textStatus) {
        this_loader.html(jobsearch_dashboard_vars.error_msg);
        this_loader.css({'background-color': '#e40000'});
    });
});

function jobsearch_dashboard_read_portfolio_file_url(input) {

    if (input.files && input.files[0]) {

        var _this = jQuery(input);
        var loader_con = _this.parents('.upload-img-holder-sec').find('.file-loader');

        var img_file = input.files[0];
        var img_size = img_file.size;

        img_size = parseFloat(img_size / 1024).toFixed(2);

        if (img_size <= 1024) {
            loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
            loader_con.show();
            var formData = new FormData();
            formData.append('add_portfolio_img', img_file);
            formData.append('action', 'jobsearch_dashboard_adding_portfolio_img_url');

            var request = $.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json"
            });
            request.done(function (response) {
                if (typeof response.img_url !== 'undefined') {
                    _this.parents('.upload-img-holder-sec').find('img').attr('src', response.img_url);
                    if (_this.parents('.upload-img-holder-sec').find('#add-portfolio-img-input').length > 0) {
                        _this.parents('.upload-img-holder-sec').find('#add-portfolio-img-input').val(response.img_url);
                    } else if (_this.parents('.upload-img-holder-sec').find('.img-upload-save-field').length > 0) {
                        _this.parents('.upload-img-holder-sec').find('.img-upload-save-field').val(response.img_url);
                    }
                }
                loader_con.html('');
            });

            request.fail(function (jqXHR, textStatus) {
                loader_con.html(jobsearch_dashboard_vars.error_msg);
                loader_con.html('');
            });

        } else {
            alert(jobsearch_dashboard_vars.com_img_size);
        }
    }
}

jQuery(document).on('change', 'input[name="add_portfolio_img"]', function () {
    jobsearch_dashboard_read_portfolio_file_url(this);
});

function jobsearch_dashboard_read_team_file_url(input) {

    if (input.files && input.files[0]) {

        var _this = jQuery(input);
        var loader_con = _this.parents('.upload-img-holder-sec').find('.file-loader');

        var img_file = input.files[0];
        var img_size = img_file.size;

        img_size = parseFloat(img_size / 1024).toFixed(2);

        if (img_size <= 1024) {
            loader_con.html('<i class="fa fa-refresh fa-spin"></i>');
            loader_con.show();
            var formData = new FormData();
            formData.append('team_image', img_file);
            formData.append('action', 'jobsearch_dashboard_adding_team_img_url');

            var request = $.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json"
            });
            request.done(function (response) {
                if (typeof response.img_url !== 'undefined') {
                    _this.parents('.upload-img-holder-sec').find('img').attr('src', response.img_url);
                    if (_this.parents('.upload-img-holder-sec').find('#team_image_input').length > 0) {
                        _this.parents('.upload-img-holder-sec').find('#team_image_input').val(response.img_url);
                    } else if (_this.parents('.upload-img-holder-sec').find('.img-upload-save-field').length > 0) {
                        _this.parents('.upload-img-holder-sec').find('.img-upload-save-field').val(response.img_url);
                    }
                }
                loader_con.html('');
            });

            request.fail(function (jqXHR, textStatus) {
                loader_con.html(jobsearch_dashboard_vars.error_msg);
                loader_con.html('');
            });

        } else {
            alert(jobsearch_dashboard_vars.com_img_size);
        }
    }
}

jQuery(document).on('change', 'input[name="team_image"]', function () {
    jobsearch_dashboard_read_team_file_url(this);
});

jQuery(document).on('click', '.upload-port-img-btn', function () {
    jQuery(this).parents('.upload-img-holder-sec').find('input[type="file"]').trigger('click');
});

//
jQuery(".jobsearch-resume-addbtn").click(function () {
    var _this = jQuery(this);
    if (_this.hasClass('jobsearch-portfolio-add-btn')) {
        var total_ports = _this.parents('.jobsearch-candidate-resume-wrap').find('.jobsearch-portfolios-list-con > li').length;
        var max_port_allow = jobsearch_dashboard_vars.max_portfolio_allow;
        var max_port_allow_msg = jobsearch_dashboard_vars.max_portfolio_allow_msg;

        if (max_port_allow <= total_ports) {
            alert(max_port_allow_msg);
            return false;
        }
    }
    _this.parents('.jobsearch-candidate-resume-wrap').find('.jobsearch-add-resume-item-popup').slideToggle("slow", function () {
        jQuery(this).find('span.edu-loding-msg').hide();
    });
    return false;
});

jQuery(document).on('click', '.close-popup-item', function () {
    var e_target = jQuery(this).parent('div');
    e_target.slideUp("slow");
});

jQuery(document).on('click', '.del-resume-item', function () {
    var e_target = jQuery(this).parents('li');
    e_target.fadeOut('slow', function () {
        e_target.remove();
    });
});

jQuery(document).on('click', '.update-resume-item', function () {
    var e_target = jQuery(this).parents('li').find('.jobsearch-update-resume-items-sec');
    e_target.slideToggle("slow");
});

jQuery(document).on('click', '.update-resume-list-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var e_target = _this.parents('.jobsearch-update-resume-items-sec');
    jobsearch_update_resume_items(_this);
    e_target.slideUp("slow");
    return false;
});

function jobsearch_update_resume_items(_this) {
    var main_parent = _this.parents('li.resume-list-item');
    var update_con = main_parent.find('.jobsearch-resume-education-wrap');
    if (_this.parents('li.resume-list-item').hasClass('resume-list-edu')) {
        var title_val = main_parent.find('input[name^="jobsearch_field_education_title"]').val();
        update_con.find('> h2 > a').html(title_val);
        var year_val = main_parent.find('input[name^="jobsearch_field_education_year"]').val();
        update_con.find('> small').html(year_val);
        var inst_val = main_parent.find('input[name^="jobsearch_field_education_academy"]').val();
        update_con.find('> span').html(inst_val);
    } else if (_this.parents('li.resume-list-item').hasClass('resume-list-exp')) {
        var title_val = main_parent.find('input[name^="jobsearch_field_experience_title"]').val();
        update_con.find('> h2 > a').html(title_val);
        var comp_val = main_parent.find('input[name^="jobsearch_field_experience_company"]').val();
        update_con.find('> span').html(comp_val);
    } else if (_this.parents('li.resume-list-item').hasClass('resume-list-port')) {
        update_con = main_parent.find('>figure');
        var title_val = main_parent.find('input[name^="jobsearch_field_portfolio_title"]').val();
        update_con.find('> figcaption span').html(title_val);
        var img_val = main_parent.find('input[name^="jobsearch_field_portfolio_image"]').val();
        update_con.find('>a>span').css({'background-image': 'url(' + img_val + ')'});
    } else if (_this.parents('li.resume-list-item').hasClass('resume-list-skill')) {
        update_con = main_parent.find('.jobsearch-add-skills-wrap');
        var title_val = main_parent.find('input[name^="jobsearch_field_skill_title"]').val();
        update_con.find('> h2 > a').html(title_val);
        var skill_val = main_parent.find('input[name^="jobsearch_field_skill_percentage"]').val();
        update_con.find('> span').html(skill_val);
    } else if (_this.parents('li.resume-list-item').hasClass('resume-list-award')) {
        var title_val = main_parent.find('input[name^="jobsearch_field_award_title"]').val();
        update_con.find('> h2 > a').html(title_val);
        var year_val = main_parent.find('input[name^="jobsearch_field_award_year"]').val();
        update_con.find('> small').html(year_val);
    }
}

function jobsearch_gallry_read_file_url(event) {

    if (window.File && window.FileList && window.FileReader) {

        var files = event.target.files;
        for (var i = 0; i < files.length; i++) {
            var img_file = files[i];
            var img_size = img_file.size;

            img_size = parseFloat(img_size / 1024).toFixed(2);

            if (img_size <= 1024) {
                jQuery('#gallery-imgs-holder').find('>div.jobsearch-column-3').remove();
                var reader = new FileReader();

                reader.onload = function (e) {
                    var rand_number = Math.floor((Math.random() * 99999999) + 1);
                    var ihtml = '\
                    <div class="jobsearch-column-3">\
                        <figure>\
                            <a><img src="' + e.target.result + '" alt=""></a>\
                        </figure>\
                    </div>';

                    jQuery('#gallery-imgs-holder').append(ihtml);
                    jQuery('.jobsearch-company-gal-photo').hide();
                    jQuery('#upload-more-gal-imgs').show();
                }

                reader.readAsDataURL(files[i]);
            } else {
                alert(jobsearch_dashboard_vars.com_img_size);
                return false;
            }
        }
    }
}

jQuery(document).on('click', '#upload-more-gal-imgs', function () {
    jQuery('#company_gallery_imgs').trigger('click');
});

jQuery(document).on('click', '.gal-item .el-remove', function () {
    var _this = jQuery(this);
    _this.parents('li').fadeOut('slow', 'linear', function () {
        _this.parents('li').remove();
        var imgs_cont = jQuery('#gallery-imgs-holder').find('>ul > li');
        if (imgs_cont.length <= 0) {
            jQuery('.jobsearch-company-gal-photo').show();
            jQuery('#upload-more-gal-imgs').hide();
        }
    });
});


// applicants scripts
jQuery(document).on('click', '#select-all-job-app', function () {
    var _this = jQuery(this);
    if (_this.is(':checked')) {
        jQuery('input[type="checkbox"][name*="app_candidate_sel"]').attr('checked', true);
        jQuery('input[type="checkbox"][name*="app_candidate_sel"]').trigger('change');
    } else {
        jQuery('input[type="checkbox"][name*="app_candidate_sel"]').attr('checked', false);
        jQuery('input[type="checkbox"][name*="app_candidate_sel"]').trigger('change');
    }
});

jQuery(document).on('change', 'input[type="checkbox"][name*="app_candidate_sel"]', function () {
    var checked_box_count = jQuery('input[type="checkbox"][name*="app_candidate_sel"]:checked').length;
    if (checked_box_count > 1) {
        jQuery('#sort-more-field-sec').show();
    } else {
        jQuery('#sort-more-field-sec').hide();
    }
});

jQuery(document).on('click', '.candidate-more-acts-con .more-actions', function () {
    var _this = jQuery(this);
    var all_boxes = jQuery('.candidate-more-acts-con');
    //
    all_boxes.find('ul').slideUp();
    all_boxes.find('.more-actions').removeClass('open-options');
    //
    var this_parent = _this.parent('.candidate-more-acts-con');
    if (_this.hasClass('open-options')) {
        this_parent.find('ul').slideUp();
        _this.removeClass('open-options')
    } else {
        this_parent.find('ul').slideDown();
        _this.addClass('open-options')
    }
});

jQuery(document).on('click', 'body', function (evt) {
    var target = evt.target;
    var this_box = jQuery('.candidate-more-acts-con');
    if (!this_box.is(evt.target) && this_box.has(evt.target).length === 0) {
        this_box.find('ul').slideUp();
        this_box.find('.more-actions').removeClass('open-options');
    }

    var more_box = jQuery('.more-fields-act-btn');
    if (!more_box.is(evt.target) && more_box.has(evt.target).length === 0) {
        more_box.find('ul').slideUp();
        more_box.find('.more-actions').removeClass('open-options');
    }
});

jQuery(document).on('click', '.more-fields-act-btn .more-actions', function () {
    var _this = jQuery(this);

    var this_parent = _this.parent('.more-fields-act-btn');
    if (_this.hasClass('open-options')) {
        this_parent.find('ul').slideUp();
        _this.removeClass('open-options')
    } else {
        this_parent.find('ul').slideDown();
        _this.addClass('open-options')
    }
});

jQuery(document).on('change', '#jobsearch-applicants-sort', function (evt) {
    var _this = jQuery(this);
    _this.parent('form').submit();
});

jQuery(document).on('click', '.applicantto-email-submit-btn', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _this_rand = _this.attr('data-randid');
    var _job_id = _this.attr('data-jid');
    var _candidate_id = _this.attr('data-cid');
    var _employer_id = _this.attr('data-eid');

    var this_form = _this.parents('form');
    var this_loader = this_form.find('.loader-box-' + _this_rand);
    var this_msg_con = this_form.find('.message-box-' + _this_rand);

    var email_subject = this_form.find('input[name="send_message_subject"]');
    var email_content = this_form.find('textarea[name="send_message_content"]');

    var error = 0;
    if (email_subject.val() == '') {
        error = 1;
        email_subject.css({"border": "1px solid #ff0000"});
    } else {
        email_subject.css({"border": "1px solid #d3dade"});
    }
    if (email_content.val() == '') {
        error = 1;
        email_content.css({"border": "1px solid #ff0000"});
    } else {
        email_content.css({"border": "1px solid #d3dade"});
    }

    if (error == 0) {

        this_msg_con.hide();
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_id: _candidate_id,
                _employer_id: _employer_id,
                email_subject: email_subject.val(),
                email_content: email_content.val(),
                action: 'jobsearch_send_email_to_applicant_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            var msg_before = '';
            var msg_after = '';
            if (typeof response.error !== 'undefined') {
                if (response.error == '1') {
                    msg_before = '<div class="alert alert-danger"><i class="fa fa-times"></i> ';
                    msg_after = '</div>';
                } else if (response.error == '0') {
                    msg_before = '<div class="alert alert-success"><i class="fa fa-check"></i> ';
                    msg_after = '</div>';
                }
            }
            if (typeof response.msg !== 'undefined') {
                this_msg_con.html(msg_before + response.msg + msg_after);
                this_msg_con.slideDown();
                if (typeof response.error !== 'undefined' && response.error == '0') {
                    email_subject.val('');
                    email_content.val('');
                    this_form.find('ul.email-fields-list').slideUp();
                }
            } else {
                this_msg_con.html(jobsearch_job_application.error_msg);
            }
            this_loader.html('');

        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html(jobsearch_dashboard_vars.error_msg);
        });
    }
});

jQuery(document).on('click', '.multi-applicantsto-email-submit', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');
    var _employer_id = _this.attr('data-eid');

    var _selected_apps_str = '';
    var _selected_apps_arr = [];
    var _selected_apps = jQuery('input[type="checkbox"][name*="app_candidate_sel"]:checked');
    _selected_apps.each(function (index, element) {
        if (jQuery(this).val() != '') {
            _selected_apps_arr.push(jQuery(this).val());
        }
    });
    if (_selected_apps_arr.length > 0) {
        _selected_apps_str = _selected_apps_arr.join(",");
    }

    if (_selected_apps_str != '') {
        var this_form = _this.parents('form');
        var this_loader = this_form.find('.loader-box-' + _job_id);
        var this_msg_con = this_form.find('.message-box-' + _job_id);

        var email_subject = this_form.find('input[name="send_message_subject"]');
        var email_content = this_form.find('textarea[name="send_message_content"]');

        var error = 0;
        if (email_subject.val() == '') {
            error = 1;
            email_subject.css({"border": "1px solid #ff0000"});
        } else {
            email_subject.css({"border": "1px solid #d3dade"});
        }
        if (email_content.val() == '') {
            error = 1;
            email_content.css({"border": "1px solid #ff0000"});
        } else {
            email_content.css({"border": "1px solid #d3dade"});
        }

        if (error == 0) {

            this_msg_con.hide();
            this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
            var request = jQuery.ajax({
                url: jobsearch_dashboard_vars.ajax_url,
                method: "POST",
                data: {
                    _job_id: _job_id,
                    _employer_id: _employer_id,
                    _candidate_ids: _selected_apps_str,
                    email_subject: email_subject.val(),
                    email_content: email_content.val(),
                    action: 'jobsearch_send_email_to_multi_applicants_by_employer',
                },
                dataType: "json"
            });

            request.done(function (response) {
                var msg_before = '';
                var msg_after = '';
                if (typeof response.error !== 'undefined') {
                    if (response.error == '1') {
                        msg_before = '<div class="alert alert-danger"><i class="fa fa-times"></i> ';
                        msg_after = '</div>';
                    } else if (response.error == '0') {
                        msg_before = '<div class="alert alert-success"><i class="fa fa-check"></i> ';
                        msg_after = '</div>';
                    }
                }
                if (typeof response.msg !== 'undefined') {
                    this_msg_con.html(msg_before + response.msg + msg_after);
                    this_msg_con.slideDown();
                    if (typeof response.error !== 'undefined' && response.error == '0') {
                        email_subject.val('');
                        email_content.val('');
                        this_form.find('ul.email-fields-list').slideUp();
                    }
                } else {
                    this_msg_con.html(jobsearch_job_application.error_msg);
                }
                this_loader.html('');

            });

            request.fail(function (jqXHR, textStatus) {
                this_loader.html(jobsearch_dashboard_vars.error_msg);
            });
        }
    }
});

jQuery(document).on('click', '.shortlist-cand-to-intrview', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');
    var _candidate_id = _this.attr('data-cid');

    var this_loader = _this.find('.app-loader');
    var this_msg_con = _this;

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_id: _candidate_id,
                action: 'jobsearch_applicant_to_interview_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                this_msg_con.html(response.msg);
                _this.removeClass('ajax-enable');
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '.reject-cand-to-intrview', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');
    var _candidate_id = _this.attr('data-cid');

    var this_loader = _this.parent('li').find('.app-loader');
    var this_msg_con = _this;

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_id: _candidate_id,
                action: 'jobsearch_applicant_to_reject_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                this_msg_con.html(response.msg);
                _this.removeClass('ajax-enable');
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '.delete-cand-from-job', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');
    var _candidate_id = _this.attr('data-cid');

    var this_loader = _this.parent('li').find('.app-loader');
    var this_msg_con = _this;

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_id: _candidate_id,
                action: 'jobsearch_delete_applicant_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                this_msg_con.html(response.msg);
                _this.removeClass('ajax-enable');
                _this.parents('li.jobsearch-column-12').slideUp();
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '.shortlist-cands-to-intrview', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');

    var _selected_apps_str = '';
    var _selected_apps_arr = [];
    var _selected_apps = jQuery('input[type="checkbox"][name*="app_candidate_sel"]:checked');
    _selected_apps.each(function (index, element) {
        if (jQuery(this).val() != '') {
            _selected_apps_arr.push(jQuery(this).val());
        }
    });
    if (_selected_apps_arr.length > 0) {
        _selected_apps_str = _selected_apps_arr.join(",");
    }

    var this_loader = _this.parent('li').find('.app-loader');
    var this_msg_con = _this;

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_ids: _selected_apps_str,
                action: 'jobsearch_multi_apps_to_interview_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                this_msg_con.html(response.msg + ' <span class="app-loader"><i class="fa fa-refresh fa-spin"></i></span>');
                _this.removeClass('ajax-enable');
                window.location.reload(true);
                return false;
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '.reject-cands-to-intrview', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-jid');

    var _selected_apps_str = '';
    var _selected_apps_arr = [];
    var _selected_apps = jQuery('input[type="checkbox"][name*="app_candidate_sel"]:checked');
    _selected_apps.each(function (index, element) {
        if (jQuery(this).val() != '') {
            _selected_apps_arr.push(jQuery(this).val());
        }
    });
    if (_selected_apps_arr.length > 0) {
        _selected_apps_str = _selected_apps_arr.join(",");
    }

    var this_loader = _this.parent('li').find('.app-loader');
    var this_msg_con = _this;

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                _candidate_ids: _selected_apps_str,
                action: 'jobsearch_multi_apps_to_reject_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                this_msg_con.html(response.msg + ' <span class="app-loader"><i class="fa fa-refresh fa-spin"></i></span>');
                _this.removeClass('ajax-enable');
                window.location.reload(true);
                return false;
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '.apps-view-btn', function () {
    var _this = jQuery(this);
    var view_input = jQuery('input[name="ap_view"]');
    if (_this.attr('data-view') == 'more') {
        view_input.val('more');
    } else {
        view_input.val('less');
    }
    view_input.parent('form').submit();
});

function jobsearch_is_valid_phone_number(that) {
    var val = that.value;
    var element = jQuery(that);
    var matches = val.match(/^[0-9\-\(\)\/\+\s]*$/);
    if (matches) {
        element.css({"border-color": "#eceeef"});
    } else {
        element.css({"border-color": "#ff0000"});
    }
}

jQuery(document).on('click', '.jobsearch-feature-pkg-sbtn', function () {
    var _this = $(this);
    var this_id = _this.attr('data-id');
    var this_con = jQuery('#fpkgs-lista-' + this_id);

    var ajax_url = jobsearch_dashboard_vars.ajax_url;
    var loader_con = this_con.find('.fpkgs-loader');
    var msg_con = this_con.find('.fpkgs-msg');

    var pkg_id = this_con.find('input[name="feature_pkg"]:checked');

    msg_con.html('');
    loader_con.html('<i class="fa fa-refresh fa-spin"></i>');

    var request = jQuery.ajax({
        url: ajax_url,
        method: "POST",
        data: {
            'job_id': this_id,
            'pkg_id': pkg_id.val(),
            'action': 'jobsearch_doing_mjobs_feature_job',
        },
        dataType: "json"
    });

    request.done(function (response) {

        var msg_before = '';
        var msg_after = '';
        if (typeof response.error !== 'undefined' && response.error == '1') {
            msg_before = '<div class="alert alert-danger"><i class="fa fa-times"></i> ';
            msg_after = '</div>';
        }
        if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '1') {
            loader_con.html('');
            msg_con.html(msg_before + response.msg + msg_after);
        } else {
            msg_con.html(response.msg);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        loader_con.html('');
    });

    return false;

});

jQuery(document).on('click', '.jobsearch-fill-the-job', function (e) {
    e.preventDefault();
    var _this = jQuery(this);
    var _job_id = _this.attr('data-id');

    var this_loader = _this.parent('.jobsearch-filledjobs-links').find('.fill-job-loader');

    if (_this.hasClass('ajax-enable')) {
        this_loader.html('<i class="fa fa-refresh fa-spin"></i>');
        var request = jQuery.ajax({
            url: jobsearch_dashboard_vars.ajax_url,
            method: "POST",
            data: {
                _job_id: _job_id,
                action: 'jobsearch_job_filled_by_employer',
            },
            dataType: "json"
        });

        request.done(function (response) {
            if (typeof response.msg !== 'undefined' && typeof response.error !== 'undefined' && response.error == '0') {
                _this.removeClass('ajax-enable');
                _this.parents('.jobsearch-table-row').find('.job-filled').html(response.msg);
                _this.append('<i class="fa fa-check"></i>');
                _this.removeAttr('href');
            }
            this_loader.html('');
        });

        request.fail(function (jqXHR, textStatus) {
            this_loader.html('');
        });
    }
});

jQuery(document).on('click', '#skill-detail-popup-btn', function () {
    jobsearch_modal_popup_open('JobSearchModalSkillsDetail');
});