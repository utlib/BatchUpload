<?php
queue_css_file('general');
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
                __('Progress') => null,
                __('Target') => null,
                __('Date') => 'added',
            ), array('link_tag' => 'th scope="col"', 'list_tag' => ''));
            ?>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($batch_upload_jobs as $batch_upload_job): ?>
            <?php
                if (!isset($availableJobTypes[$batch_upload_job->job_type])) continue;
                $jobRow = apply_filters('batch_upload_' . Inflector::underscore($batch_upload_job->job_type) . '_job_row', array(
                    'job' => $batch_upload_job,
                    'name' => html_escape($batch_upload_job->name),
                    'progress' => $batch_upload_job->isFinished() ? __("Finished") : '',
                    'target' => $availableJobTypes[$batch_upload_job->job_type],
                ));
            ?>
            <tr>
                <td><?php echo $jobRow['name']; ?></td>
                <td><?php echo $jobRow['progress']; ?></td>
                <td><?php echo $jobRow['target']; ?></td>
                <td><?php echo format_date($batch_upload_job->added); ?></td>
                <td>
                    <ul class="action-links group">
                        <?php if (!$batch_upload_job->isFinished()) : ?>
                            <li><a href="<?php echo html_escape(admin_url(array('controller' => 'jobs', 'id' => $batch_upload_job->id, 'action' => 'wizard'), 'batchupload_id')); ?>"><?php echo __("Continue"); ?></a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo html_escape(admin_url(array('controller' => 'jobs', 'id' => $batch_upload_job->id, 'action' => 'delete-confirm'), 'batchupload_id')); ?>" class="delete-confirm"><?php echo __("Delete"); ?></a></li>
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
