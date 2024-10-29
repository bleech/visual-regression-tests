# VRTs - Visual Regression Tests

Find issues before others do – every time. With automatic screenshots, daily comparisons, and instant tests after WordPress and plugin updates. Select the pages of your choice for continuous monitoring. The plugin immediately notifies you of any visual changes it detects.

> [!IMPORTANT]
> This repository is for **development purposes**. You need to run the build process for it to work. See the section [Development](#development) for further instructions. To use the plugin on your website, see the section [Installation](#installation).

## Installation

To install the plugin on your website, choose one of the following approaches:

1. Install from your WordPress backend: **Plugins** -> **Add New Plugin** and search for "vrts".
2. Download from the WordPress Plugin Directory: https://wordpress.org/plugins/visual-regression-tests/.
3. Install via [composer](https://getcomposer.org/) with [wpackagist](https://wpackagist.org/): `wpackagist-plugin/visual-regression-tests`.

### Requirements
- PHP 7.4

## Development

1. Clone the repository to your ``wp-content/plugins/`` folder.
3. Run ``composer install`` to install composer dependencies.
4. Run ``npm install`` to install node dependencies.
5. Run ``npm run build`` to build.
6. Run ``npm run start`` to start development.

### Dev Requirements
- Node 16

## License
GPLv2
