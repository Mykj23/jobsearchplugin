
var ajaxEmailTemplateSaveRequest;
jQuery(document).on('click', '.email-templates-submit', function () {
    "use strict"; 
    tinyMCE.triggerSave();
    var ajax_url = jobsearch_emailtemplate_common_vars.ajax_url,
            $this = jQuery(this),
            randid = $this.data('randid'),
            entitytype = $this.data('entitytype'),
            btntext = $this.data('btntext'),
            submited_form = jQuery('#jobsearch-email-template-form-' + randid);
    $this.html('<i class="fa fa-refresh fa-spin"></i>');
    jQuery('.email-template-msg-' + randid).html('');
    var dataString = 'action=jobsearch_email_templates_save&entitytype=' + entitytype + '&' + submited_form.serialize();
    if (typeof (ajaxEmailTemplateSaveRequest) != 'undefined') {
        ajaxEmailTemplateSaveRequest.abort();
    }
    ajaxEmailTemplateSaveRequest = jQuery.ajax({
        type: "POST",
        url: ajax_url,
        data: dataString,
        dataType: "json",
        success: function (response) {
            $this.html(btntext);
            if (response != 'error') {
                jQuery('.email-template-msg-' + randid).html(response.msg);
            } else {
                jQuery('.email-template-msg-' + randid).html(' There is an error.');
            }
            jQuery('.email-template-msg-' + randid).show();
        }
    });
    return false;
});
jQuery(document).ready(function ($) {

    jQuery(document).on('click', '.add-email-var', function () {

        var variable = jQuery(this).data('variable');
        var editorID = jQuery(this).data('editorid');//'jobsearch_email_template_job_submitted_admin_content';
        var content;
        if (jQuery('#wp-' + editorID + '-wrap').hasClass("tmce-active")) {
            content = tinyMCE.get(editorID).getContent({format: 'raw'});
        } else {
            content = jQuery('#' + editorID).val();
        }
        variable = variable + content;
        if (jQuery('#wp-' + editorID + '-wrap').hasClass('tmce-active') && tinyMCE.get(editorID)) {
            tinyMCE.get(editorID).setContent(variable);
        } else {
            jQuery('#' + editorID).val(variable);
        }
    });

    jQuery(document).on('click', '.jobsearch-email-reset-content', function () {
        var confirm_msg = jQuery(this).data('confirmmsg');
        if (confirm(confirm_msg)) {
            var variable = jQuery(this).data('variable');
            var editorID = jQuery(this).data('editorid');//'jobsearch_email_template_job_submitted_admin_content';

            if (jQuery('#wp-' + editorID + '-wrap').hasClass('tmce-active') && tinyMCE.get(editorID)) {
                tinyMCE.get(editorID).setContent(variable);
            } else {
                jQuery('#' + editorID).val(variable);
            }
        } else {
            return false;
        }


    });
});
