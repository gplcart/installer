<?php
/**
 * @package Installer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <ul class="nav nav-tabs">
    <?php if ($this->access('module_installer_upload')) { ?>
    <li><a href="<?php echo $this->url('admin/module/install'); ?>"><?php echo $this->text('Upload'); ?></a></li>
    <?php } ?>
    <li class="active disabled"><a class="disabled"><?php echo $this->text('Download'); ?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active">
      <p><?php echo $this->text('Warning! You are responsible for the content that you provide! <span class="text-danger">Do not use untrusted sources</span> as they might distribute a dangerous code!'); ?></p>
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
</form>
<?php echo $_job; ?>

