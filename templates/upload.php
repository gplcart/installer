<?php
/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <ul class="nav nav-tabs">
        <li class="active disabled"><a class="disabled"><?php echo $this->text('Upload'); ?></a></li>
        <?php if ($this->access('installer_module_download')) { ?>
        <li><a href="<?php echo $this->url('admin/module/install/download'); ?>"><?php echo $this->text('Download'); ?></a></li>
        <?php } ?>
      </ul>
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active">
          <p><?php echo $this->text('<b>Attention!</b> You are fully responsible for content you provided! <b>Do not use untrusted sources</b> as they may distribute dangerous or invalid code that will damage all your site!'); ?></p>
          <div class="form-group<?php echo $this->error('file', ' has-error'); ?>">
            <div class="col-md-4">
              <input type="file" name="file" class="form-control">
              <div class="help-block">
                <?php echo $this->error('file'); ?>
                <div class="text-muted"><?php echo $this->text('Select a ZIP file'); ?></div>
              </div>
            </div>
          </div>
          <button class="btn btn-default" name="install" value="1"><?php echo $this->text('Upload'); ?></button>
        </div>
      </div>
    </div>
  </div>
</form>