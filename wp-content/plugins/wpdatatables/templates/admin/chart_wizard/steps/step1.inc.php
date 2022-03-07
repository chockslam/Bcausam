<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<div class="row">

    <div class="alert alert-info alert-dismissible wdt-custom" role="alert" hidden>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
        </button>
        <i class="wpdt-icon-info-circle-full m-r-5"></i>
        <?php _e('NEW awesome features!!! From version wpDataTables Lite 2.1 you can create all Google charts', 'wpdatatables'); ?>
    </div>

    <!-- .col-sm-4 -->
    <div class="col-sm-6 col-md-6 chart-name">
        <h4 class="c-title-color m-b-2">
            <?php _e('Chart name', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Please define the title of the chart that you will use to identify it', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <div class="row">
                    <div class="col-sm-12">
                        <input type="text" name="chart-name" id="chart-name" class="form-control input-sm"
                               value="<?php echo empty($chartId) ? __('New wpDataTable Chart', 'wpdatatables') : $chartObj->getTitle(); ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col-sm-4 -->

    <!-- .col-sm-4 -->
    <div class="col-sm-6 col-md-6 render-engine">
        <h4 class="c-title-color m-b-2">
            <?php _e('Chart render engine', 'wpdatatables'); ?>

            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Please choose the render engine.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" name="chart-render-engine" id="chart-render-engine">
                        <option value="" <?php echo empty($chartId) ? 'selected="selected"' : ''; ?> ><?php _e('Pick the render engine', 'wpdatatables'); ?></option>
                        <option value="google"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'google')){ ?>selected="selected"<?php } ?> >
                            <?php _e('Google Charts', 'wpdatatables'); ?>
                        </option>
                        <option disabled class="wdt-premium-option-disabled" value="apexcharts" data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #FFC078;'></i><span'><?php _e('ApexCharts', 'wpdatatables'); ?><span class='wdt-premium'><?php _e('Available in Premium', 'wpdatatables'); ?></span></span>"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'apexcharts')){ ?>selected="selected"<?php } ?> >
                        </option>
                        <option disabled class="wdt-premium-option-disabled" value="highcharts" data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #FFC078;'></i><span'><?php _e('HighCharts', 'wpdatatables'); ?><span class='wdt-premium'><?php _e('Available in Premium', 'wpdatatables'); ?></span></span>"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'highcharts')){ ?>selected="selected"<?php } ?> >
                        </option>
                        <option disabled class="wdt-premium-option-disabled" value="chartjs"  data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #FFC078;'></i><span'><?php _e('Chart.js', 'wpdatatables'); ?><span class='wdt-premium'><?php _e('Available in Premium', 'wpdatatables'); ?></span></span>"
                                <?php if (!empty($chartId) && ($chartObj->getEngine() == 'chartjs')){ ?>selected="selected"<?php } ?> >
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col-sm-4 -->

</div>
<!--/.row -->

<!-- .row -->
<div class="row">

    <!-- div.google-charts-type -->
    <div class="charts-type google-charts-type col-sm-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/google_charts.inc.php'; ?>

    </div>
    <!-- /div.google-charts-type -->

    <!-- div.highcharts-charts-type -->
    <div class="charts-type highcharts-charts-type disabled col-sm-12 col-md-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/highcharts.inc.php'; ?>

    </div>
    <!-- /div.highcharts-charts-type -->

    <!-- div.chartjs-charts-type -->
    <div class="charts-type chartjs-charts-type disabled col-sm-12 col-md-12">

        <?php include WDT_TEMPLATE_PATH . 'admin/chart_wizard/steps/charts_pick/chartjs.inc.php'; ?>

    </div>
    <!-- /div.chartjs-charts-type -->

</div>
<!--/.row -->
