<?php
echo head(array(
    'title' => __('Batch Uploader') . ' | ' . __('Mappings'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<?php if (is_allowed('Items', 'add')): ?>
    <a href="<?php echo html_escape(url('batch-upload/mapping-sets/add')); ?>" class="add button small green"><?php echo __('Create New Mapping Template'); ?></a>
<?php endif; ?>

<?php echo pagination_links(); ?>

<table id="mapping-sets">
    <thead>
        <tr>
            <?php
            echo browse_sort_links(array(
                __('Name') => 'name',
                __('Mappings') => null,
                __('Date') => 'added',
            ), array('link_tag' => 'th scope="col"', 'list_tag' => ''));
            ?>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($batch_upload_mapping_sets as $batch_upload_mapping_set): ?>
            <tr>
                <td><?php echo $batch_upload_mapping_set->name; ?></td>
                <td><?php echo $batch_upload_mapping_set->countMappings(); ?></td>
                <td><?php echo format_date($batch_upload_mapping_set->added); ?></td>
                <td>
                    <ul class="action-links group">
                        <li><a href="<?php echo html_escape(admin_url(array('controller' => 'mapping-sets', 'id' => $batch_upload_mapping_set->id, 'action' => 'edit'), 'batchupload_id')); ?>"><?php echo __("Edit"); ?></a></li>
                        <li><a href="<?php echo html_escape(admin_url(array('controller' => 'mapping-sets', 'id' => $batch_upload_mapping_set->id, 'action' => 'delete-confirm'), 'batchupload_id')); ?>" class="delete-confirm"><?php echo __("Delete"); ?></a></li>
                        <li><a href="<?php echo html_escape(admin_url(array('controller' => 'mapping-sets', 'id' => $batch_upload_mapping_set->id, 'action' => 'template'), 'batchupload_id')); ?>"><?php echo __('CSV Template'); ?></a></li>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php echo pagination_links(); ?>

<?php
echo foot();
?>
