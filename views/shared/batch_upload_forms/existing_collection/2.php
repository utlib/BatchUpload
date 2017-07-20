<form method="post">
    <section class="seven columns alpha">
        <div class="field">
                <div id="headers-label" class="two columns alpha">
                    <label for="has_headers"><?php echo __("Use Headers?"); ?></label>
                </div>
                <div class="inputs five columns omega">
                    <input type="checkbox" name="has_headers" id="has_headers" checked="checked">
                </div>
            </div>
        <div class="field">
            <div id="name-label" class="two columns alpha">
                <label for="csv_file" class="required"><?php echo __("Input File"); ?></label>
            </div>
            <div class="inputs five columns omega">
                <input type="file" id="csv_file" accept=".csv,text/csv">
            </div>
        </div>
        <div id="phase2">
            <div class="field">
                <div id="headers-label" class="two columns alpha">
                    <label><?php echo __("Apply Mappings"); ?></label>
                </div>
                <div class="inputs four columns">
                    <select id="has_headers">
                        <option value="1">Example Mapping</option>
                    </select>
                </div>
                <div class="one column omega">
                    <button type="button" class="small blue button"><?php echo __("Apply"); ?></button>
                </div>
            </div>
            <div class="seven columns">
                <table id="metadata-table" data-selectcontent="<?php echo html_escape($available_properties_options); ?>" data-shortcutmappings="<?php echo html_escape(json_encode($available_properties, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>">
                    <thead>
                        <tr>
                            <th><?php echo __("Original"); ?></th>
                            <th><?php echo __("Example"); ?></th>
                            <th><?php echo __("Property"); ?></th>
                            <th><?php echo __("HTML"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <button type="button" class="green button"><?php echo __("Save as New Mapping Template..."); ?></button>
            </div>
            <input type="hidden" id="csv-data" name="csv_data">
        </div>
    </section>

    <section class="three columns omega">
        <div id="save" class="panel">
            <input type="submit" value="<?php echo html_escape(__("Next Step")); ?>" class="big green button" id="next-step" disabled="disabled">
            <a href="<?php echo html_escape(admin_url(array(), 'batchupload_root')); ?>" class="big blue button"><?php echo __("Cancel and Return"); ?></a>
            <a href="<?php echo html_escape(admin_url(array('controller' => 'jobs', 'action' => 'delete-confirm', 'id' => $batch_upload_job->id), 'batchupload_id')); ?>" class="big red button delete-confirm"><?php echo __("Delete"); ?></a>
            <div id="example-nav">
                <hr>
                <p><?php echo __("Navigate Examples") ?> (<span id="mapping-current-index">-</span>/<span id="mapping-current-count">-</span>)</p>
                <button class="blue button" type="button" disabled="disabled" id="mapping-goto-first">&laquo;</button>
                <button class="blue button" type="button" disabled="disabled" id="mapping-goto-previous">&lt;</button>
                <button class="blue button" type="button" disabled="disabled" id="mapping-goto-random">Random</button>
                <button class="blue button" type="button" disabled="disabled" id="mapping-goto-next">&gt;</button>
                <button class="blue button" type="button" disabled="disabled" id="mapping-goto-last">&raquo;</button>
            </div>
        </div>
    </section>
</form>
