<?php

/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\installer;

/**
 * Main class for Installer module
 */
class Main
{

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!class_exists('ZipArchive')) {
            $result = gplcart_text('Class ZipArchive does not exist');
        }
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/install'] = array(
            'menu' => array(
                'admin' => 'Install' // @text
            ),
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
        $permissions['module_installer_upload'] = 'Installer: upload modules'; // @text
        $permissions['module_installer_download'] = 'Installer: download modules'; // @text
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
    public function hookCronRunAfter()
    {
        gplcart_file_delete_recursive(gplcart_file_private_module('installer'));
    }

}
