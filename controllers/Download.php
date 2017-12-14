<?php

/**
 * @package Installer 
 * @author Iurii Makukh <gplcart.software@gmail.com> 
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com> 
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+ 
 */

namespace gplcart\modules\installer\controllers;

use gplcart\modules\installer\models\Install as InstallerModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Installer module
 */
class Download extends BackendController
{

    /**
     * Install model instance
     * @var \gplcart\modules\installer\models\Install $install
     */
    protected $install_model;

    /**
     * @param InstallerModuleModel $install
     */
    public function __construct(InstallerModuleModel $install)
    {
        parent::__construct();

        $this->install_model = $install;
    }

    /**
     * Route page callback to display the module download page
     */
    public function editDownload()
    {
        $this->downloadErrorsDownload();
        $this->setTitleEditDownload();
        $this->setBreadcrumbEditDownload();

        $this->submitDownload();

        $sources = $this->getData('download.sources');
        if (is_array($sources)) {
            $this->setData('download.sources', implode("\n", $sources));
        }

        $this->outputEditDownload();
    }

    /**
     * Set title on the module download page
     */
    protected function setTitleEditDownload()
    {
        $vars = array('%name' => $this->text('Installer'));
        $title = $this->text('Edit %name settings', $vars);
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module download page
     */
    protected function setBreadcrumbEditDownload()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Downloads an error log file
     */
    protected function downloadErrorsDownload()
    {
        $file = $this->install_model->getErrorLogFile();
        if ($this->isQuery('download_errors') && is_file($file)) {
            $this->download($file);
        }
    }

    /**
     * Handles submitted data
     */
    protected function submitDownload()
    {
        if ($this->isPosted('install') && $this->validateDownload()) {
            $this->controlAccess('module_installer_download');
            $this->install_model->fromUrl($this->getSubmitted('sources'));
        }
    }

    /**
     * Validate an array of submitted data
     */
    protected function validateDownload()
    {
        $this->setSubmitted('download');
        $this->setSubmittedArray('sources');

        $this->validateElement('sources', 'required');
        $this->validateUrlDownload();

        return !$this->hasErrors();
    }

    /**
     * Validates an array of submitted source URLs
     */
    protected function validateUrlDownload()
    {
        $invalid = array();
        foreach ($this->getSubmitted('sources') as $line => $url) {
            $line++;
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $invalid[] = $line;
            }
        }

        if (!empty($invalid)) {
            $vars = array('@num' => implode(',', $invalid));
            $error = $this->text('Error on line @num', $vars);
            $this->setError('sources', $error);
        }
    }

    /**
     * Render and output the download modules page
     */
    protected function outputEditDownload()
    {
        $this->output('installer|download');
    }

}
