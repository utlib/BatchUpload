<?php
queue_js_file('MappingEditor', 'js');
echo head(array(
    'title' => __('Batch Uploader') . ' | ' . __('Edit Mapping Template'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<form method="post">
    <?php require(__DIR__ . "/mapping-sets-form.php"); ?>
    <section class="three columns omega">
        <div id="save" class="panel">
            <input type="submit" value="<?php echo html_escape(__("Save")); ?>" class="big green button">
            <a href="<?php echo html_escape(admin_url(array(), 'batchupload_root')); ?>" class="big blue button"><?php echo __("Cancel and Return"); ?></a>
            <a href="<?php echo html_escape(admin_url(array('controller' => 'mapping-sets', 'action' => 'delete-confirm', 'id' => $batch_upload_mapping_set->id), 'batchupload_id')); ?>" class="big red button delete-confirm"><?php echo __("Delete"); ?></a>
        </div>
    </section>
</form>

<?php
echo foot();
