<?php

/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\installer;

use gplcart\core\Container;

/**
 * Main class for Installer module
 */
class Installer
{

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!class_exists('ZipArchive')) {
            $result = $this->getTranslationModel()->text('Class ZipArchive does not exist');
        }
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/install'] = array(
            'menu' => array('admin' => /* @text */'Install'),
            'access' => 'module_installer_upload',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\installer\\controllers\\Upload', 'editUpload')
            )
        );

        $routes['admin/module/install/download'] = array(
            'access' => 'module_installer_download',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\installer\\controllers\\Download', 'editDownload')
            )
        );
    }

    /**
     * Implements hook "user.permissions"
     * @param array $permissions
     */
    public function hookUserPermissions(array &$permissions)
    {
        $permissions['module_installer_upload'] = /* @text */'Installer: upload modules';
        $permissions['module_installer_download'] = /* @text */'Installer: download modules';
    }

    /**
     * Implements hook "job.handlers"
     * @param array $handlers
     */
    public function hookJobHandlers(array &$handlers)
    {
        $handlers['installer_download_module'] = array(
            'handlers' => array(
                'process' => array('gplcart\\modules\\installer\\handlers\\Download', 'process')
            ),
        );
    }

    /**
     * Implements hook "hook.cron"
     */
    public function hookCron()
    {
        gplcart_file_delete_recursive(gplcart_file_private_module('installer'));
    }

    /**
     * Translation UI model class instance
     * @return \gplcart\core\models\Translation
     */
    protected function getTranslationModel()
    {
        return Container::get('gplcart\\core\\models\\Translation');
    }

}
