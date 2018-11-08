jQuery('.location_location1').on('change', function (e) {
    e.preventDefault();
    var this_id = jQuery(this).data('randid'),
            nextfieldelement = jQuery(this).data('nextfieldelement'),
            nextfieldval = jQuery(this).data('nextfieldval'),
            ajax_url = jobsearch_location_common_vars.ajax_url,
            location_location1 = jQuery('#location_location1_' + this_id),
            location_location2 = jQuery('#location_location2_' + this_id);
    jQuery('.location_location2_' + this_id).html('<i class="fa fa-refresh fa-spin"></i>');
    var request = jQuery.ajax({
        url: ajax_url,
        method: "POST",
        data: {
            location_location: location_location1.val(),
            nextfieldelement: nextfieldelement,
            nextfieldval: nextfieldval,
            action: 'jobsearch_location_load_location2_data',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if ('undefined' !== typeof response.html) {
            if (jQuery('.location_location2_selectize').length > 0) {
                if (jQuery('.location_location2_selectize').hasClass('location2_selectize_ajax')) {
                    jQuery('.location2_selectize_ajax').selectize()[0].selectize.destroy();
                }
            }
            location_location2.html(response.html);
            if (typeof location_location2.parent('.jobsearch-profile-select').find('.selectize-control') !== 'undefined') {
                location_location2.parent('.jobsearch-profile-select').find('.selectize-control').remove();
                location_location2.removeAttr('style');
                location_location2.removeAttr('tabindex');
                location_location2.removeClass('location2_selectize_ajax');
                location_location2.removeClass('selectized');
            }
            jQuery('.location_location2_' + this_id).html('');
            if (nextfieldval != '') {
                jQuery('.location_location2').trigger('change');
            }
            //
            if (jQuery('.location_location2_selectize').length > 0) {
                if (!jQuery('.location_location2_selectize').hasClass('location2_selectize_ajax')) {
                    jQuery('.location_location2_selectize').addClass('location2_selectize_ajax');

                    jQuery('.location2_selectize_ajax').selectize({
                        //allowEmptyOption: true,
                    });
                }
            }
        }
    });

    request.fail(function (jqXHR, textStatus) {
    });
    return false;

});

jQuery('.location_location2').on('change', function (e) {
    e.preventDefault();
    var this_id = jQuery(this).data('randid'),
            nextfieldelement = jQuery(this).data('nextfieldelement'),
            nextfieldval = jQuery(this).data('nextfieldval'),
            ajax_url = jobsearch_location_common_vars.ajax_url,
            location_location2 = jQuery('#location_location2_' + this_id),
            location_location3 = jQuery('#location_location3_' + this_id);
    jQuery('.location_location3_' + this_id).html('<i class="fa fa-refresh fa-spin"></i>');
    var request = jQuery.ajax({
        url: ajax_url,
        method: "POST",
        data: {
            location_location: location_location2.val(),
            nextfieldelement: nextfieldelement,
            nextfieldval: nextfieldval,
            action: 'jobsearch_location_load_location2_data',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if ('undefined' !== typeof response.html) {
            if (jQuery('.location_location3_selectize').length > 0) {
                if (jQuery('.location_location3_selectize').hasClass('location3_selectize_ajax')) {
                    jQuery('.location3_selectize_ajax').selectize()[0].selectize.destroy();
                }
            }
            location_location3.html(response.html);
            if (typeof location_location3.parent('.jobsearch-profile-select').find('.selectize-control') !== 'undefined') {
                location_location3.parent('.jobsearch-profile-select').find('.selectize-control').remove();
                location_location3.removeAttr('style');
                location_location3.removeAttr('tabindex');
                location_location3.removeClass('location2_selectize_ajax');
                location_location3.removeClass('selectized');
            }
            jQuery('.location_location3_' + this_id).html('');
            if (nextfieldval != '') {
                jQuery('.location_location3').trigger('change');
            }
            //
            if (jQuery('.location_location3_selectize').length > 0) {
                if (!jQuery('.location_location3_selectize').hasClass('location3_selectize_ajax')) {
                    jQuery('.location_location3_selectize').addClass('location3_selectize_ajax');

                    jQuery('.location3_selectize_ajax').selectize({
                        //allowEmptyOption: true,
                    });
                }
            }
        }
    });

    request.fail(function (jqXHR, textStatus) {
    });
    return false;

});

jQuery('.location_location3').on('change', function (e) {
    e.preventDefault();
    var this_id = jQuery(this).data('randid'),
            nextfieldelement = jQuery(this).data('nextfieldelement'),
            nextfieldval = jQuery(this).data('nextfieldval'),
            ajax_url = jobsearch_location_common_vars.ajax_url,
            location_location3 = jQuery('#location_location3_' + this_id),
            location_location4 = jQuery('#location_location4_' + this_id);
    jQuery('.location_location4_' + this_id).html('<i class="fa fa-refresh fa-spin"></i>');
    var request = jQuery.ajax({
        url: ajax_url,
        method: "POST",
        data: {
            location_location: location_location3.val(),
            nextfieldelement: nextfieldelement,
            nextfieldval: nextfieldval,
            action: 'jobsearch_location_load_location2_data',
        },
        dataType: "json"
    });

    request.done(function (response) {
        if ('undefined' !== typeof response.html) {
            if (jQuery('.location_location4_selectize').length > 0) {
                if (jQuery('.location_location4_selectize').hasClass('location4_selectize_ajax')) {
                    jQuery('.location4_selectize_ajax').selectize()[0].selectize.destroy();
                }
            }
            location_location4.html(response.html);
            if (typeof location_location4.parent('.jobsearch-profile-select').find('.selectize-control') !== 'undefined') {
                location_location4.parent('.jobsearch-profile-select').find('.selectize-control').remove();
                location_location4.removeAttr('style');
                location_location4.removeAttr('tabindex');
                location_location4.removeClass('location2_selectize_ajax');
                location_location4.removeClass('selectized');
            }
            jQuery('.location_location4_' + this_id).html('');
            //
            if (jQuery('.location_location4_selectize').length > 0) {
                if (!jQuery('.location_location4_selectize').hasClass('location4_selectize_ajax')) {
                    jQuery('.location_location4_selectize').addClass('location4_selectize_ajax');

                    jQuery('.location4_selectize_ajax').selectize({
                        //allowEmptyOption: true,
                    });
                }
            }
        }
    });

    request.fail(function (jqXHR, textStatus) {
    });
    return false;

});