<?php
echo head(array(
    'title' => __('Batch Uploader') . ' | ' . __('Generate CSV Template'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<form id="csv-template-form" method="POST">

    <section class="seven columns alpha">
        <h2><?php echo html_escape(__("List Files")); ?></h2>
        <p><?php echo html_escape(__("This mapping set contains one or more file columns. Please indicate all local files that you plan to upload (if any). A CSV template containing the names of your files will be generated.")); ?></p>
        <?php foreach ($file_mappings as $file_mapping) : ?>
            <div class="field">
                <div class="two columns alpha"><label><?php echo html_escape($file_mapping->header); ?></label></div>
                <div class="five columns omega">
                    <input type="file" class="csv-template-filefield" data-mapping-id="<?php echo $file_mapping->id; ?>" multiple="multiple">
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="three columns omega">
        <div id="save" class="panel">
            <input type="submit" value="<?php echo html_escape(__("Save")); ?>" class="big green button">
            <a href="<?php echo html_escape(admin_url(array('controller' => 'mapping-sets', 'action' => 'browse'), 'batchupload_id')); ?>" class="big blue button"><?php echo __("Cancel and Return"); ?></a>
        </div>
    </section>

</form>

<?php
echo foot();
