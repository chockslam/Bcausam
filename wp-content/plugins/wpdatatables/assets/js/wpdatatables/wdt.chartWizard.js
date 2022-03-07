if (typeof constructedChartData == 'undefined') {
    var constructedChartData = {};
}

var wdtChartColumnsData = {};


(function ($) {
    var wdtChart = null;
    var nextStepButton = $('#wdt-chart-wizard-next-step');
    var previousStepButton = $('#wdt-chart-wizard-previous-step');

    $('.wdt-chart-wizard-chart-selecter-block .card').on('click', function () {
        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').addClass('not-selected');
        $(this).addClass('selected').removeClass('not-selected');
        nextStepButton.prop('disabled', false);

    });

    nextStepButton.click(function (e) {
        e.preventDefault();

        var curStep = $('div.chart-wizard-step:visible').data('step');
        $('div.chart-wizard-step').hide();
        $('li.chart_wizard_breadcrumbs_block').removeClass('active');
        $('.wdt-preload-layer').animateFadeIn();

        switch (curStep) {
            case 'step1':
                // Data source
                constructedChartData.chart_type = $('.wdt-chart-wizard-chart-selecter-block .card.selected').data('type');
                constructedChartData.min_columns = parseInt($('.card.selected').data('min_columns'));
                constructedChartData.max_columns = parseInt($('.card.selected').data('max_columns'));
                $('div.chart-wizard-step.step2').show();
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                constructedChartData.chart_title = $('#chart-name').val();
                constructedChartData.engine = 'google';
                $("#chart-js-container").hide();
                $("#google-chart-container").show();

                $(".highcharts").hide();
                $(".chartjs").hide();
                $(".google").show();

                $('#curve-type-row').hide();
                $('#three-d-row').hide();
                $('#background_color_row').show();
                $('#border_width_row').show();
                $('#border_color_row').show();
                $('#border_radius_row').show();
                $('#plot_background_color_row').show();
                $('#plot-border-width-row').show();
                $('#plot_border_color_row').show();
                $('#font-size-row').show();
                $('#font-name-row').show();
                $('.series').show();
                $('.axes').show();
                $('#show-grid-row').show();
                $('#horizontal-axis-crosshair-row').show();
                $('#vertical-axis-crosshair-row').show();
                $('.title').show();
                $('#title-floating-row').show();
                $('.tooltip').show();
                $('.legend').show();
                $('#inverted-row').show();

                switch (constructedChartData.chart_type) {
                    case 'google_column_chart':
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        break;
                    case 'google_histogram':
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        break;
                    case 'google_bar_chart':
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        break;
                    case 'google_stacked_bar_chart':
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        $('#inverted-row').hide();
                        break;
                    case 'google_line_chart':
                        $('#curve-type-row').show();
                        break;
                    case 'google_stepped_area_chart':
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        break;
                    case 'google_pie_chart':
                        $('#plot_background_color_row').hide();
                        $('#plot-border-width-row').hide();
                        $('#plot_border_color_row').hide();
                        $('#three-d-row').show();
                        $('.axes').hide();
                        $('#title-floating-row').hide();
                        $('.series').hide();
                        break;
                    case 'google_bubble_chart':
                        $('.legend').hide();
                        break;
                    case 'google_donut_chart':
                        $('#plot_background_color_row').hide();
                        $('#plot-border-width-row').hide();
                        $('#plot_border_color_row').hide();
                        $('.axes').hide();
                        $('#title-floating-row').hide();
                        $('.series').hide();
                        break;
                    case 'google_gauge_chart':
                        $('#background_color_row').hide();
                        $('#border_width_row').hide();
                        $('#border_color_row').hide();
                        $('#border_radius_row').hide();
                        $('#plot_background_color_row').hide();
                        $('#plot-border-width-row').hide();
                        $('#plot_border_color_row').hide();
                        $('#font-size-row').hide();
                        $('#font-name-row').hide();
                        $('#show-grid-row').hide();
                        $('.axes').hide();
                        $('.title').hide();
                        $('.tooltip').hide();
                        $('.legend').hide();
                        $('.series').hide();
                        break;
                    case 'google_scatter_chart':
                        $('#inverted-row').hide();
                        break;
                    case 'google_candlestick_chart':
                        $('.series').hide();
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        $('.legend').hide();
                        break;
                    case 'google_waterfall_chart':
                        $('.series').hide();
                        $('#horizontal-axis-crosshair-row').hide();
                        $('#vertical-axis-crosshair-row').hide();
                        $('.legend').hide();
                        break;
                }

                previousStepButton.prop('disabled', false);
                previousStepButton.animateFadeIn();
                $('#wpdatatables-chart-source').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step2':
                // Data range
                $('.wdt-preload-layer').animateFadeOut();
                applyDragula();
                nextStepButton.prop('disabled', true);
                nextStepButton.hide();
                constructedChartData.wpdatatable_id = $('#wpdatatables-chart-source').val();
                $('div.chart-wizard-step.step3').show();
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'wpdatatables_get_columns_data_by_table_id',
                        table_id: constructedChartData.wpdatatable_id,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    success: function (columns) {
                        wdtChartColumnsData = columns;
                        var columnChartTemplate = $.templates("#wdt-chart-column-block");
                        var columnChartBlockHtml = columnChartTemplate.render({columns: columns});
                        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container').html(columnChartBlockHtml);

                        if ((typeof constructedChartData.selected_columns !== 'undefined')
                            || (typeof editing_chart_data !== 'undefined')) {
                            var columns = (typeof editing_chart_data !== 'undefined') ? editing_chart_data.selected_columns : constructedChartData.selected_columns;
                            $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container .chart-column-block').remove();
                            for (var i in columns) {
                                $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block[data-orig_header="' + columns[i] + '"]')
                                    .appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container');
                            }
                        }
                        $('#wdt-add-chart-columns').click();
                        $('div.alert-warning').show();
                        nextStepButton.show();
                    }
                });
                break;
            case 'step3':
                // Formatting
                if (typeof constructedChartData.selected_columns == 'undefined') {
                    constructedChartData.selected_columns = {};
                }

                // Move string column on first place
                if ($('div.chosen_columns div.chart-column-block.string,' +
                    'div.chosen_columns div.chart-column-block.date,' +
                    'div.chosen_columns div.chart-column-block.datetime,' +
                    'div.chosen_columns div.chart-column-block.time').length
                    && (!$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('float')
                        || !$('div.chosen_columns div.chart-column-block:eq(0)').hasClass('int'))) {
                    $('div.chosen_columns div.chart-column-block.string,' +
                        'div.chosen_columns div.chart-column-block.date,' +
                        'div.chosen_columns div.chart-column-block.datetime,' +
                        'div.chosen_columns div.chart-column-block.time')
                        .eq(0)
                        .prependTo('div.wdt-chart-wizard-chosen-columns-container')
                }

                constructedChartData.selected_columns = {};
                constructedChartData.series_data = {};
                $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
                    constructedChartData.selected_columns[parseInt($(this).index())] = $(this).data('orig_header');
                });

                if (typeof editing_chart_data !== 'undefined') {
                    if (!_.isEqual(constructedChartData.selected_columns, editing_chart_data.selected_columns)) {
                        editing_chart_data.render_data.series = editing_chart_data.render_data.series.filter(function (editColumns) {
                            return Object.values(constructedChartData.selected_columns).indexOf(editColumns.orig_header) !== -1;
                        });
                    }
                }


                // Set initial width for preview
                if (constructedChartData.width == null) {
                    constructedChartData.width = 400;
                }

                $('#wdt-chart-row-range-type').change();

                $('#series-settings-container').empty();

                getInputData();
                nextStepButton.hide();
                // Render chart first time in preview
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_show_chart_from_data',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function (data) {
                        $('div.chart-wizard-step.step4').show();
                        $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');

                        //Series
                        var seriesBlockTemplate = $.templates("#wdt-chart-series-setting-block");

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i = 0; i < data.series.length; i++) {
                                    for (j = 0; j < editing_chart_data.render_data.series.length; j++) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header) {
                                            data.series[i].label = data.columns[i + 1].label = editing_chart_data.render_data.series[j].label;
                                        }
                                    }
                                }
                            }
                            seriesBlockTemplateHtml = seriesBlockTemplate.render({series: data.series});

                        }
                        $('#series-settings-container').html(seriesBlockTemplateHtml);

                        if (constructedChartData.engine == 'google') {
                            if (typeof editing_chart_data != 'undefined') {
                                for (i in data.series) {
                                    for (j in editing_chart_data.render_data.series) {
                                        if (data.series[i].orig_header === editing_chart_data.render_data.series[j].orig_header &&
                                            typeof (editing_chart_data.render_data.options.series[j]) !== 'undefined') {
                                            $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(editing_chart_data.render_data.options.series[j].color);
                                            data.options.series[i] = {
                                                color: editing_chart_data.render_data.options.series[j].color
                                            }
                                        }
                                    }
                                }
                            } else {
                                for (i in data.series) {
                                    $('#series-settings-container div.chart-series-block:eq(' + i + ')').find('div.chart-series-color input').val(data.series[i].color);
                                }
                            }
                        }

                        $(".chart-series-color .wdt-add-picker").each(function (i) {
                            jQuery(this).addClass('pickr');
                            jQuery(this)
                                .closest('.wdt-color-picker')
                                .find('.wpcolorpicker-icon i')
                                .css("background", this.value);
                        });


                        if (constructedChartData.engine == 'google') {
                            wdtChart = new wpDataTablesGoogleChart();
                            wdtChart.setType(data.type);
                            wdtChart.setColumns(data.columns);
                            wdtChart.setRows(data.rows);
                            wdtChart.setOptions(data.options);
                            wdtChart.setContainer('google-chart-container');
                            wdtChart.setColumnIndexes(data.column_indexes);
                        }
                        wdtChart.render();
                        if (constructedChartData.chart_type == 'google_bubble_chart') {
                            $('.chart-series-color').hide();
                        }

                        $('.selectpicker').selectpicker('refresh');

                        var eTop = $('.chart-preview-container').offset().top;
                        var eWidth = $('.chart-preview-container').width();

                        $(window).scroll(function () {
                            if (eTop - $(window).scrollTop() <= 30) {
                                $('.chart-preview-container').css('position', 'fixed').css('right', 48).css('top', 30).css('width', eWidth);
                            } else {
                                eWidth = $('.chart-preview-container').width();
                                $('.chart-preview-container').css('position', 'relative').css('right', '').css('top', '').css('width', '');
                            }
                        });

                        $('#chart-series-color,' +
                            '#background-color-container,' +
                            '#border-color-container,' +
                            '#plot-background-color-container,' +
                            '#plot-border-color-container,' +
                            '#font-color-container,' +
                            '#title-font-color-container input.title-font-color,' +
                            '#tooltip-background-color-container,' +
                            '#tooltip-border-color-container,' +
                            '#legend_background_color,' +
                            '#legend_border_color,' +
                            '#exporting_button_color_container input.exporting-button-color'
                        ).on('change', function (e, ui) {
                            e.stopImmediatePropagation();
                            e.preventDefault();
                            renderChart(false);
                        });

                        // Render chart on changing chart options
                        $('div.step4 input:not(#group-chart), div.step4 select')
                            .on('change', function () {
                                renderChart(false);
                            });

                        nextStepButton.show().addClass('wdt-save-chart').html('<i class="wpdt-icon-save"></i>' + wpdatatablesEditStrings.saveChart)

                        $('.wdt-preload-layer').animateFadeOut();
                    }
                });
                break;
            case 'step4':
                getInputData();
                // Save and get shortcode
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wpdatatable_save_chart_get_shortcode',
                        chart_data: constructedChartData,
                        wdtNonce: $('#wdtNonce').val(),
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        $('div.chart-wizard-step.step5').show();
                        $('li.chart_wizard_breadcrumbs_block.step5').addClass('active');
                        $('#wdt-chart-shortcode-id').html(data.shortcode);
                        constructedChartData.chart_id = data.id;
                        $('#wp-data-chart-id').val(data.id);
                        $('.wdt-preload-layer').animateFadeOut();
                        nextStepButton.prop('disabled', true);
                        nextStepButton.hide();
                        $('#finishButton').show();
                    }
                });
                break;
        }
    });

    function renderChart(reloadNeeded) {
        if (typeof reloadNeeded == 'undefined') {
            reloadNeeded = true;
        }

        getInputData();

        if (reloadNeeded) {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'wpdatatable_show_chart_from_data',
                    chart_data: constructedChartData,
                    wdtNonce: $('#wdtNonce').val(),
                },
                dataType: 'json',
                type: 'post',
                success: function (data) {
                    if (constructedChartData.engine == 'google') {
                        wdtChart = new wpDataTablesGoogleChart();
                        wdtChart.setType(data.type);
                        wdtChart.setColumns(data.columns);
                        wdtChart.setRows(data.rows);
                        wdtChart.setOptions(data.options);
                        wdtChart.setContainer('google-chart-container');
                        wdtChart.setColumnIndexes(data.column_indexes);
                    }
                    wdtChart.render();
                    $('.wdt-preload-layer').animateFadeOut();

                }
            });
        } else {
            wdtChart.setChartConfig(constructedChartData);
            wdtChart.render();
        }

    }

    // Get input fields data
    function getInputData() {
        //Chart
        constructedChartData.width = parseInt($('#chart-width').val());
        constructedChartData.height = parseInt($('#chart-height').val());
        constructedChartData.background_color = $('input.background-color').val();
        constructedChartData.border_width = parseInt($('#border-width').val() ? $('#border-width').val() : 0);
        constructedChartData.border_color = $('input.border_color').val();
        constructedChartData.border_radius = parseInt($('#border-radius').val() ? $('#border-radius').val() : 0);
        constructedChartData.plot_background_color = $('input.plot-background-color').val();
        constructedChartData.plot_background_image = $('#plot-background-image').val();
        constructedChartData.plot_border_width = $('#plot-border-width').val();
        constructedChartData.plot_border_color = $('input.plot-border-color').val();
        constructedChartData.three_d = $('#three-d').is(':checked') ? 1 : 0;
        // Series
        if (typeof constructedChartData.series_data == 'undefined') {
            constructedChartData.series_data = {};
        }
        if (constructedChartData.engine == 'google') {
            $('div.chart-series-block').each(function (e) {
                constructedChartData.series_data[$(this).data('orig_header')] = {
                    label: $(this).find('input.series-label').val(),
                    color: $(this).find('input.series-color').val()
                }
            });
        }

        // Axes
        constructedChartData.show_grid = $('#show-grid').is(':checked') ? 1 : 0;
        constructedChartData.horizontal_axis_label = $('#horizontal-axis-label').val();
        constructedChartData.vertical_axis_label = $('#vertical-axis-label').val();
        // Title
        constructedChartData.show_title = $('#show-chart-title').is(':checked') ? 1 : 0;
        constructedChartData.title_floating = $('#title-floating').is(':checked') ? 1 : 0;
        // Tooltip
        constructedChartData.tooltip_enabled = $('#tooltip-enabled').is(':checked') ? 1 : 0;
        // Legend
        constructedChartData.legend_position = $('#legend_position').val();
        constructedChartData.legend_vertical_align = $('#legend_vertical_align').val();
    }

    /**
     * Steps switcher (Prev)
     */
    previousStepButton.click(function (e) {
        e.preventDefault();

        $('.wdt-preload-layer').animateFadeIn();
        var curStep = $('div.chart-wizard-step:visible').data('step');

        switch (curStep) {
            case 'step2':
                previousStepButton.prop('disabled', true);
                previousStepButton.hide();
                $('div.chart-wizard-step.step1').show();
                $('div.chart-wizard-step.step2').hide();
                $('li.chart_wizard_breadcrumbs_block.step2').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step1').addClass('active');
                $('#chart-render-engine').change();
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step3':
                $('div.chart-wizard-step.step2').show();
                $('div.chart-wizard-step.step3').hide();
                $('li.chart_wizard_breadcrumbs_block.step3').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step2').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                break;
            case 'step4':
                $('div.chart-wizard-step.step3').show();
                $('div.chart-wizard-step.step4').hide();
                $('li.chart_wizard_breadcrumbs_block.step4').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step3').addClass('active');
                $('.wdt-preload-layer').animateFadeOut();
                nextStepButton.removeClass('wdt-save-chart').html('Next ')
                nextStepButton.prop('disabled', false);
                break;
            case 'step5':
                $('div.chart-wizard-step.step4').show();
                $('div.chart-wizard-step.step5').hide();
                $('li.chart_wizard_breadcrumbs_block.step5').removeClass('active');
                $('li.chart_wizard_breadcrumbs_block.step4').addClass('active');
                nextStepButton.prop('disabled', false);
                nextStepButton.show();
                $('#finishButton').hide();
                $('.wdt-preload-layer').animateFadeOut();
                break;
        }
    });

    /**
     * Open chart browser on finish
     */
    $('#finishButton').click(function (e) {
        e.preventDefault();
        window.location = $('#wdt-browse-charts-url').val();
    });


    /**
     * Pick the data type
     */
    $('#wpdatatables-chart-source').change(function (e) {
        e.preventDefault();
        if ($(this).val() == '') {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    });


    /**
     * Select all columns in the column selecter
     */
    $('button.select-all-columns, button.deselect-all-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('select-all-columns')) {
            $(this).closest('.card').find('div.chart-column-block').addClass('selected');
            $(this).text('Deselect All');
        } else {
            $(this).closest('.card').find('div.chart-column-block').removeClass('selected');
            $(this).text('Select All');
        }
        $(this).toggleClass('select-all-columns deselect-all-columns');

    });

    /**
     * Select a column in chart row range picker
     */
    $(document).on('click', 'div.wdt-chart-column-picker-container div.chart-column-block', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });

    /**
     * Check for limit of string columns
     */
    function checkColumnsLimit() {
        // 1 - Checking for string columns
        var string_columns = 0;
        var valid = true;
        $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').each(function () {
            if (
                $(this).hasClass('string')
                || $(this).hasClass('link')
                || $(this).hasClass('email')
                || $(this).hasClass('image')
                || $(this).hasClass('date')
                || $(this).hasClass('datetime')
                || $(this).hasClass('time')
            ) {
                string_columns++;
            }
        });
        if (string_columns > 1) {
            $('div.chosen_columns div.strings-error').show();
            valid = false;
        } else {
            $('div.chosen_columns div.strings-error').hide();
        }
        // 2 - Checking for min and max columns limit
        var totalColumnCount = $('div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').length;
        if (totalColumnCount < constructedChartData.min_columns) {
            $('div.chosen_columns div.min-columns-error').show();
            $('div.chosen_columns div.min-columns-error span.columns').html(constructedChartData.min_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.min-columns-error').hide();
        }
        if ((constructedChartData.max_columns > 0)
            && (totalColumnCount > constructedChartData.max_columns)) {
            $('div.chosen_columns div.max-columns-error').show();
            $('div.chosen_columns div.max-columns-error span.columns').html(constructedChartData.max_columns);
            valid = false;
        } else {
            $('div.chosen_columns div.max-columns-error').hide();
        }
        if (!valid) {
            nextStepButton.prop('disabled', true);
        } else {
            nextStepButton.prop('disabled', false);
        }
    }

    /**
     * Add columns to chart
     */
    $('#wdt-add-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container');
        });
        checkColumnsLimit();
    });

    /**
     * Add all columns to chart
     */
    $('#wdt-add-all-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-add-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').removeClass('selected');
        checkColumnsLimit();
    });

    /**
     * Remove columns from chart series
     */
    $('#wdt-remove-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block.selected').each(function () {
            $(this).appendTo('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container ');
        });
        checkColumnsLimit();
    });

    /**
     * Remove all columns from chart
     */
    $('#wdt-remove-all-chart-columns').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizard-chosen-columns-container div.chart-column-block').addClass('selected');
        $('#wdt-remove-chart-columns').click();
        $('div.wdt-chart-column-picker-container div.wdt-chart-wizart-existing-columns-container div.chart-column-block').removeClass('selected');
    });


    /**
     * Load data for editing existing charts
     */
    $(document).ready(function () {
        if ($('#wp-data-chart-id').val() != '') {

            $('#chart-render-engine').change();
            constructedChartData.chart_id = $('#wp-data-chart-id').val();
            constructedChartData.chart_title = editing_chart_data.title;
            // General settings
            $('.charts-type').find("[data-type='" + editing_chart_data.type + "']").click();
            $('#wpdatatables-chart-source').val(editing_chart_data.wpdatatable_id);

            if (editing_chart_data.range_type == 'picked_range') {
                $('#wdt-chart-row-range-type').val('pick_rows').change();
                constructedChartData.range_data = editing_chart_data.row_range;
                $('#range_picked_info span').html(constructedChartData.range_data.length);
            }


            // Chart
            if (typeof editing_chart_data.render_data.options.width !== 'undefined') {
                $('#chart-width').val(editing_chart_data.render_data.options.width);
                $('#chart-responsive-width').prop('checked', '');
            }
            $('#chart-height').val(editing_chart_data.render_data.options.height);


            // Axes
            if (editing_chart_data.render_data.show_grid == null) {
                $('#show-grid').prop('checked', 'checked');
            } else {
                if (editing_chart_data.render_data.show_grid) {
                    $('#show-grid').prop('checked', 'checked');
                } else {
                    $('#show-grid').prop('checked', '');
                }
            }
            $('#horizontal-axis-label').val(editing_chart_data.render_data.options.hAxis.title);
            $('#vertical-axis-label').val(editing_chart_data.render_data.options.vAxis.title);

            // Title
            if (editing_chart_data.render_data.options.title) {
                $('#show-chart-title').prop('checked', 'checked');
            } else {
                $('#show-chart-title').prop('checked', '');
            }

            if (editing_chart_data.engine == 'google') {
                // Chart
                if (editing_chart_data.render_data.options.backgroundColor == null) {
                    $('input.background-color').val('');
                    $('#border-width').val('');
                    $('input.border_color').val('');
                    $('#border-radius').val('');

                } else {
                    if (editing_chart_data.render_data.options.backgroundColor.fill) {
                        $('#background-color').val(editing_chart_data.render_data.options.backgroundColor.fill)
                                              .closest('.wdt-color-picker')
                                              .find('.wpcolorpicker-icon i')
                                              .css("background", editing_chart_data.render_data.options.backgroundColor.fill);
                    }
                    $('#border-width').val(editing_chart_data.render_data.options.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.backgroundColor.stroke) {
                        $('#border_color').val(editing_chart_data.render_data.options.backgroundColor.stroke)
                          .closest('.wdt-color-picker')
                          .find('.wpcolorpicker-icon i')
                          .css("background", editing_chart_data.render_data.options.backgroundColor.stroke);
                    }
                    $('#border-radius').val(editing_chart_data.render_data.options.backgroundColor.rx);
                }

                if (editing_chart_data.render_data.options.chartArea == null) {
                    $('input.plot-background-color').val('');
                    $('#plot-border-width').val('');
                    $('input.plot-border-color').val('');
                } else {
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.fill) {
                        $('#plot-background-color').val(editing_chart_data.render_data.options.chartArea.backgroundColor.fill)
                           .closest('.wdt-color-picker')
                           .find('.wpcolorpicker-icon i')
                           .css("background", editing_chart_data.render_data.options.chartArea.backgroundColor.fill);
                    }
                    $('#plot-border-width').val(editing_chart_data.render_data.options.chartArea.backgroundColor.strokeWidth);
                    if (editing_chart_data.render_data.options.chartArea.backgroundColor.stroke) {
                        $('#plot-border-color').val(editing_chart_data.render_data.options.chartArea.backgroundColor.stroke)
                            .closest('.wdt-color-picker')
                            .find('.wpcolorpicker-icon i')
                            .css("background", editing_chart_data.render_data.options.chartArea.backgroundColor.stroke);
                    }
                }

                if (editing_chart_data.render_data.options.is3D) {
                    $('#three-d').prop('checked', 'checked');
                } else {
                    $('#three-d').prop('checked', '');
                }


                // Title
                if (editing_chart_data.render_data.options.titlePosition == null) {
                    $('#title-floating').prop('checked', '');
                } else {
                    if (editing_chart_data.render_data.options.titlePosition == 'in') {
                        $('#title-floating').prop('checked', 'checked');
                    }
                }

                // Tooltip
                if (editing_chart_data.render_data.options.tooltip == null) {
                    $('#tooltip-enabled').prop('checked', 'checked');
                } else {
                    if (editing_chart_data.render_data.options.tooltip.trigger == 'none') {
                        $('#tooltip-enabled').prop('checked', '');
                    } else {
                        $('#tooltip-enabled').prop('checked', 'checked');
                    }
                }

                // Legend
                if (editing_chart_data.render_data.options.legend == null) {
                    $('#legend_position').val('right');
                    $('#legend_vertical_align').val("bottom");
                } else {
                    $('#legend_position').val(editing_chart_data.render_data.options.legend.position);
                    if (editing_chart_data.render_data.options.legend.alignment == 'end') {
                        $('#legend_vertical_align').val("bottom");
                    } else if (editing_chart_data.render_data.options.legend.alignment == 'center') {
                        $('#legend_vertical_align').val("middle");
                    } else {
                        $('#legend_vertical_align').val("top");
                    }
                }
            }

        }
    });

    function applyDragula() {
        var drake = dragula([document.querySelector('.wdt-chart-wizart-existing-columns-container'), document.querySelector('.wdt-chart-wizard-chosen-columns-container')], {
            invalid: function (el, target) {
                if (el.classList.contains('alert')) {
                    return true;
                }
            }
        });
        drake.on('drop', function () {
            checkColumnsLimit();
        });
    }


    /**
     * Pick the chart type
     */
    $('#chart-render-engine').change(function (e) {
        e.preventDefault();

        $('.wdt-chart-wizard-chart-selecter-block .card').removeClass('selected').removeClass('not-selected');
        $('div.charts-type').hide();
        if ($(this).val() != '') {
            constructedChartData.chart_engine = $(this).val();
            if ($(this).val() == 'google') {
                $('div.google-charts-type').show();
                $('div.alert-info').show();
                $('div.alert-warning').hide();
            } else if ($(this).val() == 'highcharts') {
                $('div.highcharts-charts-type').show();
                $('div.alert-info').hide();
                $('div.alert-warning').show();
                $('#wdt-chart-wizard-next-step').prop('disabled',true);
            } else if ($(this).val() == 'chartjs') {
                $('div.chartjs-charts-type').show();
                $('div.alert-info').hide();
                $('div.alert-warning').show();
                $('#wdt-chart-wizard-next-step').prop('disabled',true);
            }
        } else {
            $('div.alert-info').hide();
            $('div.alert-warning').hide();
        }
    });


})(jQuery);

