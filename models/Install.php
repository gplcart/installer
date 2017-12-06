<?php

/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\installer\models;

use gplcart\core\Database,
    gplcart\core\Config,
    gplcart\core\Module;
use gplcart\core\helpers\Zip as ZipHelper,
    gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\Job as JobModel,
    gplcart\core\models\Module as ModuleModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\modules\backup\models\Backup as ModuleBackupModel;

/**
 * Manages basic behaviors and data related to Installer module
 */
class Install
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Module model instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Zip helper class instance
     * @var \gplcart\core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * Url helper class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module_model
     */
    protected $module_model;

    /**
     * Backup model instance
     * @var \gplcart\modules\backup\models\Backup $backup
     */
    protected $backup;

    /**
     * The latest validation error
     * @var string
     */
    protected $error;

    /**
     * The temporary renamed module directory
     * @var string
     */
    protected $tempname;

    /**
     * The module directory
     * @var string
     */
    protected $destination;

    /**
     * The module ID
     * @var string
     */
    protected $module_id;

    /**
     * An array of module data
     * @var array
     */
    protected $data;

    /**
     * Whether an original module has been temporary renamed
     * @var boolean
     */
    protected $renamed;

    /**
     * @param Database $db
     * @param Config $config
     * @param Module $module
     * @param ModuleModel $module_model
     * @param LanguageModel $language
     * @param ModuleBackupModel $backup
     * @param JobModel $job
     * @param ZipHelper $zip
     * @param UrlHelper $url
     */
    public function __construct(Database $db, Config $config, Module $module,
            ModuleModel $module_model, LanguageModel $language, ModuleBackupModel $backup,
            JobModel $job, ZipHelper $zip, UrlHelper $url)
    {
        $this->db = $db;
        $this->config = $config;
        $this->module = $module;

        $this->zip = $zip;
        $this->url = $url;
        $this->job = $job;
        $this->backup = $backup;
        $this->language = $language;
        $this->module_model = $module_model;
    }

    /**
     * Installs a module from a ZIP file
     * @param string $zip
     * @return string|bool
     */
    public function fromZip($zip)
    {
        $this->data = null;
        $this->error = null;
        $this->renamed = false;
        $this->tempname = null;
        $this->module_id = null;
        $this->destination = null;

        if (!$this->setModuleId($zip)) {
            return $this->error;
        }

        if (!$this->extract()) {
            $this->rollback();
            return $this->error;
        }

        if (!$this->validate()) {
            $this->rollback();
            return $this->error;
        }

        if (!$this->backup()) {
            return $this->error;
        }

        return true;
    }

    /**
     * Install modules from multiple URLs
     * @param array $sources
     */
    public function fromUrl(array $sources)
    {
        $total = count($sources);
        $finish_message = $this->language->text('New modules: %inserted, updated: %updated');
        $vars = array('@url' => $this->url->get('', array('download_errors' => true)));
        $errors_message = $this->language->text('New modules: %inserted, updated: %updated, errors: %errors. <a href="@url">See error log</a>', $vars);

        $data = array(
            'total' => $total,
            'data' => array('sources' => $sources),
            'id' => 'installer_download_module',
            'log' => array('errors' => $this->getErrorLogFile()),
            'redirect_message' => array('finish' => $finish_message, 'errors' => $errors_message)
        );

        $this->job->submit($data);
    }

    /**
     * Returns path to error log file
     * @return string
     */
    public function getErrorLogFile()
    {
        return gplcart_file_private_temp('installer-download-errors.csv');
    }

    /**
     * Backup the previous version of the updated module
     */
    protected function backup()
    {
        if (empty($this->tempname)) {
            return true;
        }

        $module = $this->data;

        $module += array(
            'directory' => $this->tempname,
            'module_id' => $this->module_id
        );

        $result = $this->backup->backup('module', $module);

        if ($result === true) {
            gplcart_file_delete_recursive($this->tempname);
            return true;
        }

        $this->error = $this->language->text('Failed to backup module @id', array('@id' => $this->module_id));
        return false;
    }

    /**
     * Extracts module files to the system directory
     * @return boolean
     */
    protected function extract()
    {
        $this->destination = GC_DIR_MODULE . "/{$this->module_id}";

        if (file_exists($this->destination)) {
            $this->tempname = gplcart_file_unique($this->destination . '~');
            if (!rename($this->destination, $this->tempname)) {
                $this->error = $this->language->text('Failed to rename @old to @new', array('@old' => $this->destination, '@new' => $this->tempname));
                return false;
            }
            $this->renamed = true;
        }

        if ($this->zip->extract(GC_DIR_MODULE)) {
            return true;
        }

        $this->error = $this->language->text('Failed to extract to @name', array('@name' => $this->destination));
        return false;
    }

    /**
     * Restore the original module files
     */
    protected function rollback()
    {
        if (!$this->isUpdate() || ($this->isUpdate() && $this->renamed)) {
            gplcart_file_delete_recursive($this->destination);
        }

        if (isset($this->tempname)) {
            rename($this->tempname, $this->destination);
        }
    }

    /**
     * Validates a module data
     * @return boolean
     */
    protected function validate()
    {
        $this->data = $this->module->getInfo($this->module_id);

        if (empty($this->data)) {
            $this->error = $this->language->text('Failed to read module @id', array('@id' => $this->module_id));
            return false;
        }

        $result_core = $this->module_model->checkCore($this->data);

        if ($result_core !== true) {
            $this->error = $result_core;
            return false;
        }

        $result_php = $this->module_model->checkPhpVersion($this->data);

        if ($result_php !== true) {
            $this->error = $result_php;
            return false;
        }

        return true;
    }

    /**
     * Returns an array of files from a ZIP file
     * @param string $file
     * @return array
     */
    public function getFilesFromZip($file)
    {
        try {
            $files = $this->zip->set($file)->getList();
        } catch (\Exception $e) {
            return array();
        }

        return count($files) < 2 ? array() : $files;
    }

    /**
     * Set a module id
     * @param string $file
     * @return boolean
     */
    protected function setModuleId($file)
    {
        $module_id = $this->getModuleIdFromZip($file);
        $result = $this->module_model->checkModuleId($module_id);

        if ($result !== true) {
            $this->error = $result;
            return false;
        }

        // Do not deal with enabled modules as it may cause fatal results
        // Check if the module ID actually has enabled status in the database
        // Alternative system methods are based on the scanned module folders so may return incorrect results
        if ($this->isEnabledModule($module_id)) {
            $this->error = $this->language->text('Module @id is enabled and cannot be updated', array('@id' => $module_id));
            return false;
        }

        $this->module_id = $module_id;
        return true;
    }

    /**
     * Check if a module ID has enabled status in the database
     * @param string $module_id
     * @return bool
     */
    protected function isEnabledModule($module_id)
    {
        $sql = 'SELECT module_id FROM module WHERE module_id=? AND status > 0';
        $result = $this->db->fetchColumn($sql, array($module_id));
        return !empty($result);
    }

    /**
     * Returns a module id from a zip file or false on error
     * @param string $file
     * @return boolean|string
     */
    public function getModuleIdFromZip($file)
    {
        $list = $this->getFilesFromZip($file);

        if (empty($list)) {
            return false;
        }

        $folder = reset($list);

        if (strrchr($folder, '/') !== '/') {
            return false;
        }

        $nested = 0;
        foreach ($list as $item) {
            if (strpos($item, $folder) === 0) {
                $nested++;
            }
        }

        if (count($list) != $nested) {
            return false;
        }

        return rtrim($folder, '/');
    }

    /**
     * Whether the module files have been updated
     * @return bool
     */
    public function isUpdate()
    {
        return isset($this->tempname);
    }

}
