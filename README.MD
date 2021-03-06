# Super Progressive Web Apps

#### This is a fork of the original plugin with many enhancements.
This plugin has diverged from the original since version [2.0.0](https://github.com/SuperPWA/Super-Progressive-Web-Apps/releases/tag/2.0) and will no longer be merging in updates from upstream.

Occasionally any useful bug fixes or enhancements from upstream will be cherry-picked. Currently, up to date with version
2.1.8


### Installation

```bash
composer require lipemat/super-progressive-web-apps
```
### Usage

``` php
require __DIR__ . '/vendor/autoload.php'
```

### Enhancements (non exhaustive)
3. Add to home screen optional support.
4. Support Google Webfonts and Gravatars. Also now filter to whitelist additional cache domains
5. Skip waiting on new service-working install.
6. Prevent doubling up of requests on initial caching.
7. Setting to enable/disable the service worker.
9. Automatically disable a fetch when `SCRIPT_DEBUG = true` to prevent caching when developing.
10. Setting to add "Must Cache" urls which will be added to the cache even no matter where they live. Useful when using a script from `/wp-admin` which would normally be excluded.
11. Browser cache may be flushed programmatically via the `superpwa_resource_version` filter or by using the version handling found in [wordpress-libs](https://github.com/lipemat/wordpress-libs/blob/a7830a62094fdb92901e7c8c69e6524b3bf2149a/src/Theme/Styles.php#L29)
