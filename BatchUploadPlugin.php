<?php
/**
 * The main plugin class.
 * 
 * @package BatchUpload
 */
class BatchUploadPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * List of hooks used by this plugin.
     * 
     * @var array
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'initialize',
        'define_routes',
        'after_save_batch_upload_job',
    );

    /**
     * List of filters used by this plugin.
     * 
     * @var array
     */
    protected $_filters = array(
        'admin_navigation_main',
        'batch_upload_register_job_type',
    );
    
    protected $_supported_job_types = array(
        'new_collection',
        'existing_collection',
        'individual_items',
    );

    /**
     * HOOK: Installing the plugin.
     */
    public function hookInstall()
    {
        $db = get_db();
        $prefix = $db->prefix;
        $db->query("CREATE TABLE IF NOT EXISTS `{$prefix}batch_upload_jobs` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `step` int(10) NOT NULL DEFAULT 1,
            `job_type` varchar(128) NOT NULL,
            `target_type` varchar(64),
            `target_id` int(10),
            `data` LONGTEXT,
            `owner_id` int(10) UNSIGNED NOT NULL,
            `finished` timestamp NULL DEFAULT NULL,
            `added` timestamp NOT NULL DEFAULT '2000-01-01 05:00:00',
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`owner_id`) REFERENCES `{$prefix}users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        $db->query("CREATE TABLE IF NOT EXISTS `{$prefix}batch_upload_rows` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `job_id` int(10) NOT NULL,
            `order` int(10) NOT NULL,
            `data` LONGTEXT,
            FOREIGN KEY (`job_id`) REFERENCES `{$prefix}batch_upload_jobs`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        $db->query("CREATE TABLE IF NOT EXISTS `{$prefix}batch_upload_mapping_sets` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(64) NOT NULL UNIQUE,
            `job_id` int(10) UNIQUE,
            `owner_id` int(10) UNSIGNED NOT NULL,
            `added` timestamp NOT NULL DEFAULT '2000-01-01 05:00:00',
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`job_id`) REFERENCES `{$prefix}batch_upload_jobs`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`owner_id`) REFERENCES `{$prefix}users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        $db->query("CREATE TABLE IF NOT EXISTS `{$prefix}batch_upload_mappings` (
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `header` varchar(64) NOT NULL,
            `order` int(10) NOT NULL,
            `property` int(10) NOT NULL,
            `html` tinyint(4) NOT NULL DEFAULT 0,
            `mapping_set_id` int(10),
            FOREIGN KEY (`mapping_set_id`) REFERENCES `{$prefix}batch_upload_mapping_sets`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }
    
    /**
     * HOOK: Uninstalling the plugin.
     */
    public function hookUninstall()
    {
        $db = get_db();
        $prefix = $db->prefix;
        $db->query("DROP TABLE IF EXISTS `{$prefix}batch_upload_rows`;");
        $db->query("DROP TABLE IF EXISTS `{$prefix}batch_upload_mappings`;");
        $db->query("DROP TABLE IF EXISTS `{$prefix}batch_upload_mapping_sets`;");
        $db->query("DROP TABLE IF EXISTS `{$prefix}batch_upload_jobs`;");
    }

    /**
     * HOOK: Upgrading the plugin.
     * 
     * Run migrations in version ascending order, starting from the last unrun migration.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $doMigrate = false;

        $versions = array();
        foreach (glob(dirname(__FILE__) . '/libraries/BatchUpload/Migration/*.php') as $migrationFile)
        {
            $className = 'BatchUpload_Migration_' . basename($migrationFile, '.php');
            include $migrationFile;
            $versions[$className::$version] = new $className();
        }
        uksort($versions, 'version_compare');

        foreach ($versions as $version => $migration)
        {
            if (version_compare($version, $oldVersion, '>'))
            {
                $doMigrate = true;
            }
            if ($doMigrate)
            {
                $migration->up();
                if (version_compare($version, $newVersion, '>'))
                {
                    break;
                }
            }
        }
    }
    
    /**
     * HOOK: Initialization. Add wizard integrations.
     */
    public function hookInitialize()
    {
        foreach ($this->_supported_job_types as $supported_job_type)
        {
            $klass = "BatchUpload_Wizard_" . Inflector::camelize($supported_job_type);
            $wizard = new $klass();
            $wizard->integrate();
        }
    }
    
    /**
     * HOOK: Defining routes.
     * 
     * @param array $args
     */
    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(dirname(__FILE__) . '/routes.ini', 'routes'));
    }
    
    /**
     * HOOK: After batch upload job is saved.
     * Run the "new job" hook to initialize data in the job.
     * 
     * @param array $args
     */
    public function hookAfterSaveBatchUploadJob($args)
    {
        if ($args['insert'])
        {
            $batch_upload_job = $args['record'];
            fire_plugin_hook('batch_upload_' . Inflector::underscore($batch_upload_job->job_type) . '_job_new', array('job' => $batch_upload_job));
            $batch_upload_job->save();
        }
    }
    
    /**
     * FILTER: Add entry to admin navigation menu.
     * 
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Batch Upload'),
            'uri' => url(array(), 'batchupload_root'),
        );
        return $nav;
    }
    
    /**
     * FILTER: Register job types available to this plugin.
     * 
     * @param array $jobTypes
     * @return array
     */
    public function filterBatchUploadRegisterJobType($jobTypes)
    {
        foreach ($this->_supported_job_types as $supported_job_type)
        {
            $klass = "BatchUpload_Wizard_" . Inflector::camelize($supported_job_type);
            $wizard = new $klass();
            $jobTypes[$supported_job_type] = __($wizard->getTypeDescription());
        }
        return $jobTypes;
    }
}
