var $ = jQuery;
(function ($) {
    "use strict";
    $.fn.jobsearch_req_field_loop = function (callback, thisArg) {
        var me = this;
        return this.each(function (index, element) {
            return callback.call(thisArg || element, element, index, me);
        });
    };
})(jQuery);

jQuery(document).ready(function(){
    jQuery('#job-sector').find('option').first().val('');
    jQuery('#job-type').find('option').first().val('');
});

function jobsearch_validate_form(that) {
    "use strict";
    var req_class = 'jobsearch-req-field',
            _this_form = $(that),
            form_validity = 'valid';
    var errors_counter = 1;
    _this_form.find('.' + req_class).jobsearch_req_field_loop(function (element, index, set) {

        var job_desc_max_len = parseInt(jobsearch_posting_vars.desc_len_exceed_num);
        var eror_str = '';
        if ($(element).val() == '') {
            form_validity = 'invalid';
            eror_str = jobsearch_posting_vars.blank_field_msg;

        } else if ($(element).attr('id') == 'job_detail') {
            var field_val = $(element).val();
            if (field_val.length > job_desc_max_len) {
                form_validity = 'invalid';
                eror_str = jobsearch_posting_vars.desc_len_exceed_msg;

            } else {
                $(element).parents('.wp-editor-container').css({"border": "1px solid #eceeef"});
                $(element).parents('.form-field-sec').find('.field-error').html('');
            }
        } else if ($(element).attr('name') == 'reg_user_uname') {
            var field_val = $(element).val();
            if (!field_val.match(/^([a-zA-Z0-9_-]+)$/)) {
                form_validity = 'invalid';
                eror_str = 'Username can contain only alphanumeric characters, underscore(_), dash(-).';
            } else if (field_val.length > 20 || field_val.length < 3) {
                form_validity = 'invalid';
                eror_str = 'Username length should be between 3 to 20 characters.';
            } else {
                $(element).css({"border": "1px solid #eceeef"});
                $(element).parents('.form-field-sec').find('.field-error').html('');
            }
        } else if ($(element).attr('name') == 'reg_user_email') {
            var field_val = $(element).val();
            var email_pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
            if (field_val == '' || !email_pattern.test(field_val)) {
                form_validity = 'invalid';
                eror_str = 'Please enter proper email address.';
            } else {
                $(element).css({"border": "1px solid #eceeef"});
                $(element).parents('.form-field-sec').find('.field-error').html('');
            }
        }

        if (eror_str != '') {
            //var animate_to = element;
            if ($(element).attr('id') == 'job_detail') {
                element = $(element).parents('.wp-editor-container');
            }
            $(element).css({"border": "1px solid #ff0000"});
            var animate_to = $(element);

            $(element).parents('li').find('.field-error').html(eror_str);

            if (errors_counter == 1) {
                $('html, body').animate({scrollTop: animate_to.offset().top - 70}, 1000);
            }

            errors_counter++;
        }
    });

    if (form_validity == 'valid') {
        return true;
    } else {
        return false;
    }
}

jQuery(document).on('submit', 'form#job-posting-form', function () {
    return jobsearch_validate_form(jQuery(this));
});

jQuery(document).on('change', 'select[name="job_salary_currency"]', function () {
    var _this = jQuery(this);
    var sel_curr = _this.find(':selected').attr('data-cur');
    jQuery('.salary-input').find('span').html(sel_curr);
});

function jobsearch_job_attach_files_url(event) {

    if (window.File && window.FileList && window.FileReader) {

        var file_types_str = jobsearch_posting_vars.job_files_mime_types;
        if (file_types_str != '') {
            var file_types_array = file_types_str.split('|');
        } else {
            var file_types_array = [];
        }
        var file_allow_size = jobsearch_posting_vars.job_files_max_size;
        var num_files_allow = jobsearch_posting_vars.job_num_files_allow;

        num_files_allow = parseInt(num_files_allow);
        file_allow_size = parseInt(file_allow_size);

        jQuery('#attach-files-holder').find('.adding-file').remove();
        var files = event.target.files;
        for (var i = 0; i < files.length; i++) {

            var _file = files[i];
            var file_type = _file.type;
            var file_size = _file.size;
            var file_name = _file.name;

            file_size = parseFloat(file_size / 1024).toFixed(2);

            if (file_size <= file_allow_size) {
                if (file_types_array.indexOf(file_type) >= 0) {
                    var file_icon = 'fa fa-file-text-o';
                    if (file_type == 'image/png' || file_type == 'image/jpeg') {
                        file_icon = 'fa fa-file-image-o';
                    } else if (file_type == 'application/msword' || file_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                        file_icon = 'fa fa-file-word-o';
                    } else if (file_type == 'application/vnd.ms-excel' || file_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                        file_icon = 'fa fa-file-excel-o';
                    } else if (file_type == 'application/pdf') {
                        file_icon = 'fa fa-file-pdf-o';
                    }
                    
                    var rand_number = Math.floor((Math.random() * 99999999) + 1);
                    var ihtml = '\
                    <div class="jobsearch-column-3 adding-file">\
                        <div class="file-container">\
                            <a><i class="' + file_icon + '"></i> ' + file_name + '</a>\
                        </div>\
                    </div>';
                    jQuery('#attach-files-holder').append(ihtml);
                        
                } else {
                    alert(jobsearch_posting_vars.file_type_error);
                    return false;
                }
            } else {
                alert(jobsearch_posting_vars.file_size_error);
                return false;
            }

            if (i >= num_files_allow) {
                break;
            }
        }
    }
}

jQuery(document).on('click', '.jobsearch-company-gallery .el-remove', function () {
    var e_target = jQuery(this).parent('li');
    e_target.fadeOut('slow', function () {
        e_target.remove();
    });
});

jQuery(document).on('change', '#jobsearch_job_apply_type', function () {
    if (jQuery(this).val() == 'external') {
        jQuery('#job-apply-external-url').slideDown();
        jQuery('#job-apply-by-email').hide();
    } else if (jQuery(this).val() == 'with_email') {
        jQuery('#job-apply-by-email').slideDown();
        jQuery('#job-apply-external-url').hide();
    } else {
        jQuery('#job-apply-external-url').hide();
        jQuery('#job-apply-by-email').hide();
    }
});

jQuery(document).on('click', 'input[name="job_package_featured"]', function () {
    var _this = jQuery(this);
    if (_this.is(':checked')) {
        jQuery('.jobsearch-pkgs-boughtnew').slideUp();
    }
});