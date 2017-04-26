<?php
/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <ul class="nav nav-tabs">
        <?php if ($this->access('installer_module_upload')) { ?>
        <li><a href="<?php echo $this->url('admin/module/install'); ?>"><?php echo $this->text('Upload'); ?></a></li>
        <?php } ?>
        <li class="active disabled"><a class="disabled"><?php echo $this->text('Download'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active">
          <p><?php echo $this->text('<b>Warning!</b> You are fully responsible for content you provided! <b>Do not use untrusted sources</b> as they may distribute dangerous or invalid code that will damage all your site!'); ?></p>
          <div class="form-group<?php echo $this->error('sources', ' has-error'); ?>">
            <div class="col-md-12">
              <textarea class="form-control" rows="10" name="download[sources]" placeholder="http://domain.com/module.zip"><?php echo isset($download['sources']) ? $this->e($download['sources']) : ''; ?></textarea>
              <div class="help-block">
                <?php echo $this->error('sources'); ?>
                <div class="text-muted"><?php echo $this->text('One or more URLs to module zip files, one per line'); ?></div>
              </div>
            </div>
          </div>
          <button class="btn btn-default" name="install" value="1"><?php echo $this->text('Download'); ?></button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php if(!empty($job)) { ?>
<?php echo $job; ?>
<?php } ?>

