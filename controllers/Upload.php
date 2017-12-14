<?php

/**
 * @package Installer 
 * @author Iurii Makukh <gplcart.software@gmail.com> 
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com> 
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+ 
 */

namespace gplcart\modules\installer\controllers;

use gplcart\core\models\Module as ModuleModel,
    gplcart\core\models\FileTransfer as FileTransferModel;
use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\modules\installer\models\Install as InstallerInstallModel;

/**
 * Handles incoming requests and outputs data related to the Installer module
 */
class Upload extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Installer model class instance
     * @var \gplcart\modules\installer\models\Install $install
     */
    protected $install;

    /**
     * File transfer model class instance
     * @var \gplcart\core\models\FileTransfer $file_transfer
     */
    protected $file_transfer;

    /**
     * @param ModuleModel $module
     * @param FileTransferModel $file_transfer
     * @param InstallerInstallModel $install
     */
    public function __construct(ModuleModel $module, FileTransferModel $file_transfer,
            InstallerInstallModel $install)
    {
        parent::__construct();

        $this->module = $module;
        $this->install = $install;
        $this->file_transfer = $file_transfer;
    }

    /**
     * Route page callback to display the module upload page
     */
    public function editUpload()
    {
        $this->setTitleEditUpload();
        $this->setBreadcrumbEditUpload();

        $this->submitUpload();
        $this->outputEditUpload();
    }

    /**
     * Set title on the module upload page
     */
    protected function setTitleEditUpload()
    {
        $title = $this->text('Upload module');
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module upload page
     */
    protected function setBreadcrumbEditUpload()
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
     * Handles submitted data
     */
    protected function submitUpload()
    {
        if ($this->isPosted('install') && $this->validateUpload()) {
            $this->installModuleUpload();
        }
    }

    /**
     * Validate a submitted data
     */
    protected function validateUpload()
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            $this->setError('file', $this->text('Nothing to install'));
            return false;
        }

        $result = $this->file_transfer->upload($file, 'zip', gplcart_file_private_module('installer'));

        if ($result !== true) {
            $this->setError('file', $result);
            return false;
        }

        $this->setSubmitted('file', $this->file_transfer->getTransferred());
        return !$this->hasErrors();
    }

    /**
     * Install uploaded module
     */
    protected function installModuleUpload()
    {
        $this->controlAccess('file_upload');
        $this->controlAccess('module_installer_upload');

        $file = $this->getSubmitted('file');
        $result = $this->install->fromZip($file);

        if ($result !== true) {
            $this->redirect('', $result, 'warning');
        }

        if ($this->install->isUpdate()) {
            $message = $this->text('Module has been updated. Previous version has been saved to a <a href="@url">backup</a> file', array('@url' => $this->url('admin/report/backup')));
        } else {
            $message = $this->text('Module has been uploaded. You should <a href="@url">enable</a> it manually', array('@url' => $this->url('admin/module/list')));
        }

        $this->redirect('', $message, 'success');
    }

    /**
     * Render and output the module upload page
     */
    protected function outputEditUpload()
    {
        $this->output('installer|upload');
    }

}
