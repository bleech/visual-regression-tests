# VRTs - Visual Regression Tests

VRTs - Visual Regression Tests is a plugin to test your website for unwanted visual changes. The plugin runs automatic daily tests for chosen pages and posts. The tool creates daily comparison screenshots and compares them with a reference snapshot. If there is a difference between the screenshots, you'll be automatically notified via email. Use three comparison mode to spot the differences easily.

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
