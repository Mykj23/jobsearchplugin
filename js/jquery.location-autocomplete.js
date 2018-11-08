jQuery(document).ready(function (jQuery) {
    "use strict";
    jQuery.fn.extend({
        cityAutocomplete: function (options) {

            return this.each(function () {

                var input = jQuery(this), opts = jQuery.extend({}, jQuery.cityAutocomplete);
                var autocompleteService = new google.maps.places.AutocompleteService();
                var predictionsDropDown = jQuery('<div class="jobsearch_location_autocomplete" class="city-autocomplete"></div>').appendTo(jQuery(this).parent());
                var request_var = 1;
                input.keyup(function () {

                    jQuery(this).parent(".jobsearch_searchloc_div").find('.loc-loader').html("<i class='fa fa-refresh fa-spin'></i>");
                    if (request_var == 1) {
                        var searchStr = jQuery(this).val();
                        // Min Number of characters
                        var num_of_chars = 0;
                        if (searchStr.length > num_of_chars) {
                            var params = {
                                input: searchStr,
                                bouns: 'upperbound',
                                //types: ['address']
                            };
                            params.componentRestrictions = ''; //{country: window.country_code}

                            autocompleteService.getPlacePredictions(params, updatePredictions);
                        } else {
                            jQuery(".jobsearch_searchloc_div").find('.loc-loader').html('');
                        }
                    }
                });
                predictionsDropDown.delegate('div', 'click', function () {
                    if (jQuery(this).text() != jobsearch_plugin_vars.var_address_str && jQuery(this).text() != jobsearch_plugin_vars.var_other_locs_str) {
                        // address with slug
                        var jobsearch_address_html = jQuery(this).text();
                        // slug only
                        var jobsearch_address_slug = jQuery(this).find('span').html();
                        // remove slug
                        jQuery(this).find('span').remove();
                        input.val(jQuery(this).text());
                        input.next('.loc_search_keyword').val(jobsearch_address_slug);
                        predictionsDropDown.hide();
                        input.next('.loc_search_keyword').closest("form.side-loc-srch-form").submit();
                    }
                });
                jQuery(document).mouseup(function (e) {
                    predictionsDropDown.hide();
                });
                jQuery(window).resize(function () {
                    updatePredictionsDropDownDisplay(predictionsDropDown, input);
                });
                updatePredictionsDropDownDisplay(predictionsDropDown, input);
                function updatePredictions(predictions, status) {

                    var google_results = '';
                    if (google.maps.places.PlacesServiceStatus.OK == status) {

                        // AJAX GET ADDRESS FROM GOOGLE
                        google_results += '<div class="address_headers"><h5>' + jobsearch_plugin_vars.var_address_str + '</h5></div>';
                        jQuery.each(predictions, function (i, prediction) {
                            google_results += '<div class="jobsearch_google_suggestions"><i class="icon-location-arrow"></i> ' + prediction.description + '<span style="display:none">' + prediction.description + '</span></div>';
                        });
                        request_var = 0;
                    } else {
                        predictionsDropDown.empty();
                    }
                    // AJAX GET Locations
                    var dataString = 'action=jobsearch_get_all_db_locations' + '&keyword=' + jQuery('.jobsearch_search_location_field').val();
                    var plugin_url = jobsearch_plugin_vars.ajax_url;
                    var request = jQuery.ajax({
                        type: "POST",
                        url: plugin_url,
                        data: dataString,
                    });

                    request.done(function (data) {
                        jQuery(".jobsearch_searchloc_div").find('.loc-loader').html('');
                        var results = jQuery.parseJSON(data);
                        predictionsDropDown.empty();
                        predictionsDropDown.append(google_results);
                        if (results != '') {

                            predictionsDropDown.append('<div class="address_headers"><h5>' + jobsearch_plugin_vars.var_other_locs_str + '</h5></div>');
                            jQuery(results).each(function (key, value) {
                                if (value.hasOwnProperty('child')) {
                                    jQuery(value.child).each(function (child_key, child_value) {
                                        predictionsDropDown.append('<div class="jobsearch_location_child">' + child_value.value + '<span style="display:none">' + child_value.slug + '</span></div');
                                    })
                                } else {
                                    predictionsDropDown.append('<div class="jobsearch_location_parent">' + value.value + '<span style="display:none">' + value.slug + '</span></div');
                                }
                            })
                        }
                        request_var = 1;
                    });

                    request.fail(function (jqXHR, textStatus) {
                        jQuery(".jobsearch_searchloc_div").find('.loc-loader').html('');
                    });

                    predictionsDropDown.show();
                }
                return input;
            });
        }
    });
    function updatePredictionsDropDownDisplay(dropDown, input) {
        if (typeof (input.offset()) !== 'undefined') {
            dropDown.css({
                'width': input.outerWidth(),
                'left': input.offset().left,
                'top': input.offset().top + input.outerHeight()
            });
        }
    }

    jQuery('.jobsearch_search_location_field').cityAutocomplete();
    jQuery(document).on('click', '.jobsearch_searchloc_div', function () {
        jQuery('.jobsearch_search_location_field').prop('disabled', false);
    });
    jQuery(document).on('click', 'form', function () {
        var src_loc_val = jQuery(this).find('.jobsearch_search_location_field');
        src_loc_val.next('.loc_search_keyword').val(src_loc_val.val());
    });
}(jQuery));