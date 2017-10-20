<?php

/**
 * @package Installer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\installer\handlers;

use gplcart\core\models\File as FileModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\modules\installer\models\Install as InstallerInstallModel;

/**
 * Handler for Installer module
 */
class Download
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Installer model class instance
     * @var \gplcart\modules\installer\models\Install $install
     */
    protected $install;

    /**
     * An array of errors
     * @var array
     */
    protected $errors = array();

    /**
     * An array of the current job
     * @var array
     */
    protected $job = array();

    /**
     * The current processing URL
     * @var string
     */
    protected $data_url;

    /**
     * @param FileModel $file
     * @param LanguageModel $language
     * @param InstallerInstallModel $install
     */
    public function __construct(FileModel $file, LanguageModel $language,
            InstallerInstallModel $install)
    {
        $this->file = $file;
        $this->install = $install;
        $this->language = $language;
    }

    /**
     * Processes one job iteration
     * @param array $job
     */
    public function process(array &$job)
    {
        $this->job = &$job;
        $this->errors = array();

        if (empty($this->job['data']['sources'][$this->job['done']])) {
            $this->job['status'] = false;
            $this->job['done'] = $this->job['total'];
        } else {
            $this->install();
            $this->job['done'] ++;
            $this->job['errors'] += $this->countErrors();
        }
    }

    /**
     * Install a module
     * @return boolean
     */
    protected function install()
    {
        $file = $this->download();

        if (empty($file)) {
            return false;
        }

        $result = $this->install->fromZip($file);

        if ($result !== true) {
            $this->setError($result);
            return false;
        }

        if ($this->install->isUpdate()) {
            $this->job['updated'] ++;
        } else {
            $this->job['inserted'] ++;
        }

        return true;
    }

    /**
     * Download a module file from a remote source
     * @return boolean|string
     */
    protected function download()
    {
        $this->data_url = $this->job['data']['sources'][$this->job['done']];

        $filename = md5($this->data_url);
        $destination = gplcart_file_private_module('installer', "$filename.zip", true);
        $result = $this->file->download($this->data_url, 'zip', $destination);

        if ($result !== true) {
            $this->setError($result);
            return false;
        }

        return $this->file->getTransferred();
    }

    /**
     * Returns a total number of errors and logs them
     * @return integer
     */
    protected function countErrors()
    {
        $count = 0;
        foreach ($this->errors as $url => $errors) {
            $errors = array_filter($errors);
            $count += count($errors);
            $this->logErrors($url, $errors);
        }
        return $count;
    }

    /**
     * Logs all errors happened for the URL
     * @param integer $url
     * @param array $errors
     * @return boolean
     */
    protected function logErrors($url, array $errors)
    {
        $data = array($url, implode(PHP_EOL, $errors));
        return gplcart_file_csv($this->job['log']['errors'], $data);
    }

    /**
     * Sets a error
     * @param string|array $error
     */
    protected function setError($error)
    {
        settype($error, 'array');
        $existing = empty($this->errors[$this->data_url]) ? array() : $this->errors[$this->data_url];
        $this->errors[$this->data_url] = gplcart_array_merge($existing, $error);
    }

}
