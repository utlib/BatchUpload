<?php
echo head(array(
    'title' => __('Batch Uploader'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<?php if (is_allowed('Items', 'add')): ?>
    <a href="<?php echo html_escape(url('batch-upload/jobs/add')); ?>" class="add button small green"><?php echo __('Start New Job'); ?></a>
<?php endif; ?>

<?php echo pagination_links(); ?>
    
<table id="jobs">
    <thead>
        <tr>
            <?php
            echo browse_sort_links(array(
                __('Name') => 'name',
                __('Row') => null,
                __('Target') => null,
                __('Date') => 'added',
            ), array('link_tag' => 'th scope="col"', 'list_tag' => ''));
            ?>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Stow #1</td>
            <td>- / -</td>
            <td>Items</td>
            <td>June 1 2017</td>
            <td>
                <ul class="action-links group">
                    <li><a href="<?php html_escape(url('batch-upload/jobs/edit/1')); ?>"><?php echo __("Continue"); ?></a></li>
                    <li><a href="<?php html_escape(url('batch-upload/jobs/delete-confirm/1')); ?>"><?php echo __("Delete"); ?></a></li>
                </ul>
            </td>
        </tr>
    </tbody>
</table>

<?php echo pagination_links(); ?>
    
<?php
echo foot();
?>
