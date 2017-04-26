[![Build Status](https://scrutinizer-ci.com/g/gplcart/installer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/installer/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/installer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/installer/?branch=master)

Installer is a [GPL Cart](https://github.com/gplcart/gplcart) module that intended to upload/download ("install") zipped modules from various sources.

**Features**

- Upload or download several modules at once
- Basic validation (core compatibility, PHP version etc)
- Updating existing modules
- Auto-backup for updated modules


**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/installer`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Allow administrators to use Installer by giving them permissions `Installer: upload modules` or `Installer: download modules` at `admin/user/role`

**Usage**

- UI is located at `admin/module/install`