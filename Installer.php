<?php

/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\installer;

use gplcart\core\Module;

/**
 * Main class for Installer module
 */
class Installer extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/install'] = array(
            'menu' => array('admin' => 'Install'),
            'access' => 'installer_module_upload',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\installer\\controllers\\Upload', 'editUpload')
            )
        );

        $routes['admin/module/install/download'] = array(
            'access' => 'installer_module_download',
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
        $permissions['installer_module_upload'] = 'Installer: upload modules';
        $permissions['installer_module_download'] = 'Installer: download modules';
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
        $directory = GC_PRIVATE_MODULE_DIR . '/installer';
        if (is_dir($directory)) {
            gplcart_file_delete_recursive($directory);
        }
    }

}
