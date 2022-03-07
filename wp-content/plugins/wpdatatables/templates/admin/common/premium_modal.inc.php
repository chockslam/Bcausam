<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- #wdt-premium-modal -->
<div class="modal fade" id="wdt-premium-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">

    <!-- .modal-dialog -->
    <div class="modal-dialog">

        <!-- .modal-content -->
        <div class="modal-content">

            <!-- .modal-header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><i class="wpdt-icon-star-full m-r-5" style="color: #FFC078;"></i><?php _e('This is a premium feature', 'wpdatatables') ?></h4>
            </div>
            <!--/ .modal-header -->

            <!-- .modal-body -->
            <div class="modal-body">
                <!-- .row -->
                <div class="row">
                    <div class="col-sm-12 p-0">
                        <span><?php _e('This feature is available only in premium version of wpDataTables', 'wpdatatables'); ?></span>
                    </div>
                </div>
                <!--/ .row -->
            </div>
            <!--/ .modal-body -->

            <!-- .modal-footer -->
            <div class="modal-footer">
                <a id="wdt-premium-compare-link"><?php _e('Compare', 'wpdatatables') ?></a>
                <span><?php _e('or', 'wpdatatables') ?></span>
                <a href="https://wpdatatables.com/pricing/" target="_blank"><?php _e('View Pricing', 'wpdatatables') ?></a>
            </div>
            <!--/ .modal-footer -->
        </div>
        <!--/ .modal-content -->
    </div>
    <!--/ .modal-dialog -->
</div>
<!--/ #wdt-premium-modal -->