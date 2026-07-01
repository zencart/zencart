// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v6.0.0
//
$(function(){
    // -----
    // If there's no outer-wrapper identified, create an outer-wrapper for each
    // 'attributeWrapper', changing the name of the attributeWrapper to be that
    // just-added wrapping class.  This makes the location of the various options/images layouts
    // to be more straight-forward.
    //
    if (attributeWrapper === wrapperAttribsOptions) {
        $(attributeWrapper).each(function() {
            $(this).nextUntil(wrapperAttribsOptions).addBack().wrapAll('<div class="posm-wrapper"></div>');
        });
        attributeWrapper = '.posm-wrapper';
    }

    // -----
    // Search each attribute group, disabling all but the first of the dropdown / radio-button groups.
    //
    // The following variables are set by zc_plugins/POSM/{version}/catalog/includes/templates/default/jscript/posm_dependencies_jscript.php which
    // also causes this jQuery script to be loaded.
    //
    // 1. wrapperAttribsOptions.  Identifies the 'wrapper' around each option's values' grouping.
    // 2. inputTypes.  Identifies the jQuery selector(s) used to locate the dependent attribute types.
    // 3. inputTypesFirst.  Identifies the jQuery selector(s) used to locate the first option-value for a given option.
    // 4. ignoreOptionsList.  Contains an array of options to ignore for the dependent attributes handling.
    // 5. isSingleOption.  A binary value that identifies whether/not the current product has a single managed option.
    // 6. optionNameSelector.  Identifies the jQuery selector used to locate an option's name.
    // 7. attributeWrapper.  Identifies the outer selector that encompasses an option's selections.
    // 8. attribImgSelector.  Identifies the selector for attributes' images' blocks' wrapper.
    // 9. showModelNum.  Identifies whether/not each variant's model-number is shown when the final attribute choices are displayed.
    //
    function escapeHtml(unsafe) {
        return unsafe.replace(/[&<"']/g, function (m) {
            switch (m) {
                case '&':
                    return '&amp;';
                case '<':
                    return '&lt;';
                case '>':
                    return '&gt;';
                case '"':
                    return '&quot;';
                case "'":
                    return '&#039;';
                default:
                    return m;
            }
        });
    }
    let firstGroup = null;
    let firstGroupIsImage = false;
    let optionID = 0;
    $(attributeWrapper).each(function() {
        let isFirstGroup = false;

        // -----
        // If the current wrapper contains input types that might be managed ...
        //
        if ($(this).find(inputTypes).length > 0) {
            let theOptionID = $(this).find(inputTypesFirst).attr('name').match(/\d+/g);  //-option names are encoded as 'id[xxx]' where 'xxx' is the options_id
            if (ignoreOptionsList.indexOf(Number(theOptionID[0])) === -1) {
                if (firstGroup === null) {
                    optionID = theOptionID;
                    $(this).addClass('posm-active');
                    firstGroup = $(this);
                    isFirstGroup = true;
                    if (!isSingleOption) {
                        if ($(this).find('select').length > 0) {
                            $(this).find('option:selected').prop('selected', false);
                        } else {
                            $(this).find('input[type="radio"]:checked').prop('checked', false);
                        }
                    }
                } else {
                    $(this).removeClass('posm-active');
                    $(this).find(inputTypesFirst).each(function() {
                        theOptionID = $(this).attr('name').match(/\d+/g);
                        if (ignoreOptionsList.indexOf(Number(theOptionID[0])) === -1) {
                            $(this).prop('disabled', true);
                        }
                    });
                }
                if (!isSingleOption) {
                    if ($(this).find('select').length > 0) {
                        $(this).find('option:selected').prop('selected', false);
                    } else {
                        $(this).find('input[type="radio"]:checked').prop('checked', false);
                        if (!isFirstGroup) {
                            $(this).children().children().not(optionNameSelector).hide();
                            $(this).find(attribImgSelector).hide();
                            $(this).find(optionNameSelector).append(' <span class="posm-prev-choices">' + radioButtonChoose + '<\/span>');
                        }
                    }
                }
            }
        }
    });

    // -----
    // Add the default "Please Choose" option to each select tag, if enabled in the configuration.
    //
    if (insertPleaseChoose) {
        let foundFirstSelect = false;
        $(wrapperAttribsOptions + ' select').each(function() {
            let theOptionID = $(this).attr('name').match(/\d+/g);  //-option names are encoded as 'id[xxx]' where 'xxx' is the options_id
            if (ignoreOptionsList.indexOf(Number(theOptionID[0])) === -1) {
                if (foundFirstSelect) {
                    $(this).prepend($('<option value="0" selected="selected">' + pleaseChooseNextText + '<\/option>'));
                } else {
                    foundFirstSelect = true;
                    $(this).prepend($('<option value="0" selected="selected">' + pleaseChooseText + '<\/option>'));
                }
            }
        });
    }

    if (typeof(console.log) === 'function') {
        console.log('firstGroup optionID: '+optionID);
    }

    // -----
    // Make sure that the default options are deselected and then, for the first visible select-option, select the first option.  Since these
    // operations might have caused a change, make sure that any events attached to the change-trigger are run.
    //
    if (isSingleOption === false) {
        $(firstGroup).find('input[type="radio"]:checked').prop('checked', false);
        $(firstGroup).find('select :selected').prop('selected', false);
        $(firstGroup).find('select option:first').prop('selected', true);

        $(document).on('click', '#productAttributes option:selected, #productAttributes input[type="radio"]:checked', function() {
            $(this).trigger('change');
        });
    }

    // -----
    // Retrieve the currently-configured options and their status for the 1st option group.
    //
    zcJS.ajax({
        url: "ajax.php?act=ajaxOptionsStockDependencies&method=availableOptionValues",
        data: {
            products_id: $('input[name="products_id"]').val(),
            options_id: optionID,
            selected_values: '',
            calling_page: "'" + callingPage + "'",
            calling_pid: callingPid
        }
    }).done(function(response) {
        if (response.error === true) {
            $('#posm_message').html(response.error_message);
            if (window.console) {
                if (typeof(console.log) === 'function') {
                    console.log(response.error_message);
                }
            }
        } else {
            lastSelection = response.last_selection;
            $(firstGroup).children().children().not(optionNameSelector).hide();
            for (let i = 0, n = response.option_values.length; i < n; i++) {
                let currentOption = $(firstGroup).find('select option[value=' + response.option_values[i]['options_values_id'] + ']');
                if (currentOption.length !== 0) {
                    currentOption.prop('disabled', false);
                }
                let currentRadio = $(firstGroup).find('input[type="radio"][value="' + response.option_values[i]['options_values_id'] + '"]');
                if (lastSelection) {
                    if (response.option_values[i]['quantity'] > 0) {
                        outOfStockClass = 'in-stock';
                        outOfStockMessage = inStockMessage.replace('%u', response.option_values[i]['quantity']);
                    } else {
                        outOfStockClass = 'no-stock';
                        outOfStockMessage = response.option_values[i]['oos_message'];
                    }
                    if (currentOption.length !== 0) {
                        if (showModelNum && response.option_values[i]['model'].length !== 0) {
                            currentOption.append(' <span class="posm-model-num">[' + response.option_values[i]['model'] + ']<\/span>');
                        }
                        if (outOfStockMessage !== '') {
                            currentOption.append(' <span class="posm-stock-msg">[' + outOfStockMessage + ']<\/span>');
                        }
                        if (response.option_values[i]['extra_info'] !== '') {
                            currentOption.append(' <span class="posm-stock-msg">' + response.option_values[i]['extra_info'] + '<\/span>');
                        }
                        currentOption.removeClass();
                        currentOption.addClass(outOfStockClass);
                    } else if (currentRadio.length !== 0) {
                        if (showModelNum && response.option_values[i]['model'].length !== 0) {
                            currentRadio.next().append(' <span class="posm-model-num">[' + response.option_values[i]['model'] + ']<\/span>');
                        }
                        if (outOfStockMessage !== '') {
                            currentRadio.next().append(' <span class="' + outOfStockClass + ' posm-stock-msg">[' + outOfStockMessage + ']<\/span>');
                        }
                        if (response.option_values[i]['extra_info'] !== '') {
                            currentRadio.next().append(' <span class="posm-stock-msg">' + response.option_values[i]['extra_info'] + '<\/span>');
                        }
                    }
                    if (!allowCheckout && outOfStockClass === 'no-stock') {
                        if (currentOption.length !== 0) {
                            currentOption.prop('disabled', true);
                            currentOption.prop('selected', false);
                        } else if (currentRadio.length !== 0) {
                            currentRadio.prop('disabled', true);
                            currentRadio.prop('checked', false);
                        }
                    }
                }
                $(firstGroup).show().children().children().show();
            }
        }
    });

    // -----
    // Register a handler for any select/radio-button changes within the product's attributes.
    //
    let nextOptionGroup;
    $(document).on('change', '#productAttributes select, #productAttributes input[type="radio"]', function(event) {
        // -----
        // Capture the element associated with the change-event into a named variable.
        //
        let changedItem = $(event.target);

        // -----
        // Remove any previously-issued error messages.
        //
        $('.posm-error').remove();

        // -----
        // Make sure the the changed item is selected or checked (select vs. radio-button set).
        //
        let optionValue = event.target.value;
        if (window.console) {
            if (typeof(console.log) === 'function') {
                console.log('Changed value: ' + optionValue);
            }
        }

        // -----
        // Disable all attributes' blocks containing dropdown or radio buttons that come *after* the currently-changed item.
        //
        optionID = 0;
        let changedItemWrapper = $(changedItem).closest(attributeWrapper);
        $(changedItemWrapper).nextAll(attributeWrapper).each(function() {
            if ($(this).find(inputTypes).length === 0) {
                return;
            }

            $(this).removeClass('posm-active');
            let selectedOption = $(this).find(inputTypes);
            if ($(selectedOption).length > 0) {
                let theOptionID = $(selectedOption).attr('name').match(/\d+/g);
                if (ignoreOptionsList.indexOf(Number(theOptionID[0])) === -1) {
                    $(this).find('.posm-prev-choices').remove();
                    if (optionID === 0) {
                        nextOptionGroup = $(this);
                        optionID = theOptionID;
                    } else {
                        $(selectedOption).prop('disabled', true);
                    }
                    if ($(selectedOption).is('select')) {
                        $(selectedOption).find('option:selected').prop('selected', false);
                        $(selectedOption).find('option:first').prop('selected', true);
                    } else {
                        $(selectedOption).prop('checked', false);
                        if (optionID !== theOptionID) {
                            $(this).find(optionNameSelector).append(' <span class="posm-prev-choices">' + radioButtonChoose + '<\/span>');
                            $(this).children().children().not(optionNameSelector).hide();
                            $(this).find(attribImgSelector).hide();
                        }
                    }
                }
            }
        });
        if (window.console) {
            if (typeof(console.log) == 'function') {
                console.log('nextOptionGroup optionID: ' + optionID);
            }
        }

        // -----
        // If the optionID value is still 0, the changed item was the last in the series of dependent attributes.  Nothing left to do here ...
        //
        if (optionID === 0) {
            return;
        }

        // -----
        // Search through the currently-active attributes' groups, to determine the options_id/options_values_id pairs
        // that are currently selected.
        //
        let currentSelections = '';
        $('#productAttributes ' + attributeWrapper + '.posm-active').each(function() {
            let selectedOption = $(this).find('select option:selected, input[type="radio"]:checked');
            if ($(selectedOption).length > 0) {
                if (currentSelections !== '') {
                    currentSelections += ',';
                }
                if ($(selectedOption).is('option')) {
                    currentSelections += $(selectedOption).parent('select').attr('name').match(/\d+/g);
                } else {
                    currentSelections += $(selectedOption).attr('name').match(/\d+/g);
                }
                currentSelections += (':' + $(selectedOption).val());
            }
        });
        if (window.console) {
            if (typeof(console.log) === 'function') {
                console.log('currentSelections: (' + currentSelections + ')');
            }
        }

        // -----
        // Make the call to retrieve the available options for the next selection, based on the current selections.
        //
        zcJS.ajax({
            url: "ajax.php?act=ajaxOptionsStockDependencies&method=availableOptionValues",
            data: {
                products_id: $('input[name="products_id"]').val(),
                options_id: optionID,
                selected_values: "'" + currentSelections + "'",
                calling_page: "'" + callingPage + "'",
                calling_pid: callingPid
            }

        }).done(function(response) {
            if (response.error === true) {
                $('#posm_message').html(response.error_message);
                if (window.console) {
                    if (typeof(console.log) === 'function') {
                        console.log(response.error_message);
                    }
                }
            } else {
                lastSelection = response.last_selection;
                $(nextOptionGroup).addClass('posm-active');

                $(nextOptionGroup).children().children().not(optionNameSelector).hide();
                $(nextOptionGroup).find('select option').hide();
                $(nextOptionGroup).find('select option').prop('disabled', true);

                // -----
                // Options' layout is fairly consistent, except for image layout-style 0, where the
                // 'attribImgSelector' is rendered as a group of images directly below the actual
                // radio-buttons' listing.
                //
                let showAttribImageBlock = false;
                if ($(nextOptionGroup).find(attribImgSelector+' > input[type="radio"]').length === 0) {
                    showAttribImageBlock = true;
                    $(nextOptionGroup).find('input[type="radio"]').nextUntil('input[type="radio"]').addBack().hide();
                } else {
                    $(nextOptionGroup).find(attribImgSelector).hide();
                }

                $(nextOptionGroup).find(inputTypes).each(function() {
                    $(this).prop('disabled', false);
                });

                $(nextOptionGroup).find('.posm-stock-msg, .posm-model-num').each(function() {
                    $(this).remove();
                });

                for (let i = 0, n = response.option_values.length; i < n; i++) {
                    let currentOption = $(nextOptionGroup).find('select option[value=' + response.option_values[i]['options_values_id'] + ']');
                    let currentRadio = $(nextOptionGroup).find('input[type="radio"][value="' + response.option_values[i]['options_values_id'] + '"]');
                    if (lastSelection) {
                        if (response.option_values[i]['quantity'] > 0) {
                            outOfStockClass = 'in-stock';
                            outOfStockMessage = inStockMessage.replace('%u', response.option_values[i]['quantity']);
                        } else {
                            outOfStockClass = 'no-stock';
                            outOfStockMessage = response.option_values[i]['oos_message'];
                        }

                        if (currentOption.length !== 0) {
                            currentOption.prop('disabled', false);
                            if (showModelNum && response.option_values[i]['model'].length !== 0) {
                                currentOption.append(' <span class="posm-model-num">[' + response.option_values[i]['model'] + ']<\/span>');
                            }
                            if (outOfStockMessage !== '') {
                                currentOption.append(' <span class="posm-stock-msg">[' + outOfStockMessage + ']<\/span>');
                            }
                            if (response.option_values[i]['extra_info'] !== '') {
                                currentOption.append(' <span class="posm-stock-msg">' + response.option_values[i]['extra_info'] + '<\/span>');
                            }
                            currentOption.removeClass();
                            currentOption.addClass(outOfStockClass);
                        }
                        if (currentRadio.length !== 0) {
                            if (showModelNum && response.option_values[i]['model'].length !== 0) {
                                currentRadio.next().append(' <span class="posm-model-num">[' + response.option_values[i]['model'] + ']<\/span>');
                            }
                            if (outOfStockMessage !== '') {
                                currentRadio.next().append(' <span class="' + outOfStockClass + ' posm-stock-msg">[' + outOfStockMessage + ']<\/span>');
                            }
                            if (response.option_values[i]['extra_info'] !== '') {
                                currentRadio.next().append(' <span class="posm-stock-msg">' + response.option_values[i]['extra_info'] + '<\/span>');
                            }
                        }
                        if (!allowCheckout && outOfStockClass === 'no-stock') {
                            if (currentOption.length !== 0) {
                                currentOption.prop('disabled', true);
                                currentOption.prop('selected', false);
                            } else if (currentRadio.length !== 0) {
                                currentRadio.prop('disabled', true);
                                currentRadio.prop('checked', false);
                            }
                        }
                    }
                    if (currentOption.length !== 0) {
                        currentOption.closest(attributeWrapper).show().children().children().show();
                        currentOption.prop('disabled', false);
                        currentOption.show();
                    } else if (currentRadio.length !== 0) {
                        currentRadio.closest(attributeWrapper).show().children().children().show();
                        if (currentRadio.parent().hasClass(attribImgSelector.substring(1, attribImgSelector.length))) {
                            currentRadio.parent().show();
                        } else {
                            currentRadio.nextUntil('input[type="radio"]').addBack().show();
                        }
                    }
                }

                if (showAttribImageBlock) {
                    $(nextOptionGroup).find(attribImgSelector).show();
                }
            }

            if (response.extra_functions.length !== 0) {
                if (typeof(console.log) === 'function') {
                    console.log('Processing extra functions...');
                }
                $.each(response.extra_functions, function(funcName, funcParms) {
                    window[funcName](funcParms);
                });
            }
        });
    });

    // -----
    // On-submit processing -- make sure that at least one value is selected for each active group of options.
    //
    $(document).on('submit', 'form[name="cart_quantity"]', function(event) {
        let submitAllowed = true;

        // -----
        // Remove any previously-issued error messages
        //
        $('.posm-error').remove();

        // -----
        // Ensure that at least one option is selected in each active group of options
        //
        $(attributeWrapper + '.posm-active').each(function() {
            if ($(this).find(inputTypes).length > 0) {
                if ($(this).find('option:selected, input[type="radio"]:checked').length === 0 || $(this).find('option[value=0]:selected').length !== 0) {
                    submitAllowed = false;
                    let optionName = escapeHtml($(this).find(optionNameSelector).text().replace(/:/g, ''));
                    $(this).find(optionNameSelector).after('<span class="posm-error">' + noSelectionText + optionName + '<\/span>');
                }
            }
        });

        // -----
        // If an option needs to be selected, scroll to that element so that
        // the customer can see it!
        //
        if (submitAllowed === false) {
            event.stopImmediatePropagation();
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: $(this).find(optionNameSelector).parent().offset().top
            }, 1000);
        }
        return submitAllowed;
    });
});
