<?php

include_once ('js/import_full_js.php');
include_once ('../../helpers/view_helper.php');
?>
<div class="col-md-12">
    <div id="export_settings" class="col-md-12 config_default_style">
        <?php ViewHelper::render_config_title( __("Import", 'tainacan') ); ?>
        <div class="col-md-12 no-padding">
            <div role="tabpanel">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation"><a id="click_zip" href="#zip" aria-controls="zip" role="tab" data-toggle="tab"><?php _e('AIP','tainacan') ?></a></li>
                    <li role="presentation"><a id="click_csv" href="#csv" aria-controls="csv" role="tab" data-toggle="tab"><?php _e('CSV','tainacan') ?></a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Tab panes -->
                    <div role="tabpanel" class="tab-pane active" id="zip">
                        <form id="form_export_zip" method="post" action="<?php echo get_template_directory_uri() ?>/controllers/import/zip_controller.php">
                            <div class="export-container">
                                <input type="hidden" id="operation_import_aip" name="operation" value="import_full_aip" />
                                <select disabled="disabled" class="form-control">
                                    <option selected="selected"><?php _e('Dspace Format','tainacan') ?></option>
                                </select>
                            </div>

                            <button type="submit" id="export_zip" class="btn btn-primary tainacan-blue-btn-bg"><?php _e('Import AIP','tainacan'); ?></button>
                        </form>
                    </div>
                    <!-- Tab panes -->
                    <div role="tabpanel" class="tab-pane active" id="csv">
                        <form id="form_export_zip" method="post" action="<?php echo get_template_directory_uri() ?>/controllers/export/zip_controller.php">
                            <div class="export-container">
                                <input type="hidden" id="operation_import_aip" name="operation" value="import_full_csv" />
                                <select disabled="disabled" class="form-control">
                                    <option selected="selected"><?php _e('Dspace Format','tainacan') ?></option>
                                </select>
                            </div>

                            <button type="submit" id="export_zip" class="btn btn-primary tainacan-blue-btn-bg"><?php _e('Import AIP','tainacan'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>