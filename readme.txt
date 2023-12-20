=== VRTs - Visual Regression Tests ===
Contributors: bleechberlin
Tags: vrts, visual regression, visual, regression, tests
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.7.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Test your website for unwanted visual changes. Run automatic tests and spot differences.

== Description ==

VRTs - Visual Regression Tests is a plugin to test your website for unwanted visual changes. The plugin runs automatic daily tests for chosen pages and posts. The tool creates daily comparison screenshots and compares them with a reference snapshot. If there is a difference between the screenshots, you'll be automatically notified via email. Use three comparison modes to spot the differences easily.

**Please note:** The website must be publicly accessible in order to set up and run the tests. Password protection or any kind of firewall might prevent the plugin from working correctly.

= YOUR STRATEGY TO SPOT UNWANTED CHANGES =

How do you usually check your website after updates and code changes? Do you go manually through all pages or do you just hope everything will run ok? Visual regression testing provides you a method that helps you find errors and visual abnormalities. The VRTs plugin gives you an environment to automate your process.

1. **Activate daily tests for any page or post:** Upon activation, a reference screenshot is taken. This will be renewed when a post is saved or updated.
2. **Receive instant alerts:** If a change is detected between the snapshot and the comparison screenshot, the plugin will create an alert and notify you via email. The daily test will be paused, until the alert is resolved.
3. **Compare two screenshots:** Detect differences between the two snapshots with the difference, split screen or side-by-side view.
4. **Exclude elements:** Prevent false positives by excluding dynamic elements, ads and animations from snapshots.
5. **Resolve tests:** After fixing an issue, mark the alert as resolved. A new snapshot will be generated and the test will start running again.


= USE CASES =

In which cases can visual regression testing help you spot issues?

* Plugin and core software updates
* Manual code changes
* External software and API issues
* Server issues
* Malware and other malicious code
* Missing quality assurance


= FEATURES =

* **Instant first test:** After plugin activation, the homepage is immediately monitored and alerts are sent to the stored admin email.
* **Daily Tests:** The plugin automatically scans selected posts and pages daily. It validates the visual content by comparing two screenshots.
* **3 comparison modi:** Choose between three ways to compare snapshots - Difference, Split, Side-by-Side view.
* **Instant alerts:** As soon as a change is detected between the snapshot and the comparison screenshot, you will be notified via email.
* **GDPR compliant:** Snapshots are stored on European servers. We do not collect any personal data.
* **Cross-team:** Team members with admin rights can create and view tests and work together on the immediate solution.


= FREE FOREVER =
* test up to **3 pages**
* on **one** domain
* **automatic daily** tests


= GO PRO =

Do you like VRTs and want to run more tests?
Unlock more features with our paid plans:

* test up to **500 pages** in total
* on **multiple** domains
* with **automatic daily** test
* and **unlimited manual tests**
* run tests programmatically with **do_action( 'vrts_run_tests' )**
* add **multiple alert** recipients
* get **e-mail** support
* and access to **new features**

[See all pricing plans](https://bleech.de/en/products/visual-regression-tests/#pricing)


= BUG-REPORT =

You found a bug? Please report it by creating an issue on the [support forum](https://wordpress.org/support/plugin/visual-regression-tests/).


= READ MORE =

You want to learn more about VRTs?

Official product page:

[VRTs – Visual Regression Tests](https://bleech.de/en/products/visual-regression-tests/)

Resources:

[How does visual regression testing work?](https://bleech.de/en/blog/how-does-visual-regression-testing-work/)



== Frequently Asked Questions ==

= What is Visual Regression Testing? =

With visual regression testing, you can detect errors and unwanted changes on your website by comparing a previous state of the website with a more recent one. For example, errors in the frontend can be caused by plugin updates and changes. For small websites, you may be able to find them right away, but for complex websites, it becomes difficult. A tool can help by taking pictures of pages and posts to detect visual changes and inform you automatically.

= How does visual regression testing work? =

Tests can be done manually, pixel-by-pixel, DOM-based or AI-based. In all cases you compare an earlier website state with a newer website state. Our Visual Regression Tests Plugin takes periodical screenshots and compares them on a split screen.

= Why should I use a tool for visual regression tests? =

You can do visual testing either manually or with automated tools. Checking your website for errors manually is time-consuming and less accurate. Automated tests may find errors that are minimal and irrelevant. But the chance of finding critical errors is much higher with a tool. As soon as you discover an error, you'll receive a warning so that you can fix the problem as soon as possible.

= Is the testing done on my server? =

Screenshots and comparisons are performed on an external server and sent to your WordPress website. Only required meta data is stored in your database.

= Why are email notifications not working properly? =

If our external screenshot service can not access your WordPress installation directly, test status updates and sending emails will be handled by the WP-Cron system. In order to be notified by the plugin about new alerts, please make sure that your WordPress instance can send emails and that the WordPress cron system is set up correctly. The default configuration of the WordPress cron system does not work reliably if you cache your site heavily, do not have frequent visitors or do not use wp-admin regularly. In this case, you should [hook the WP-Cron into the system task scheduler](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/), or use an external cron job scheduling service.

= Does the plugin work with cookie consent banners? =

Yes, cookie banners are not an issue. Before taking a snapshot, the tool can automatically trigger the Accept button to hide the banner. This option can be configured with CSS selectors in the plugin settings.

= Can I test custom post type archives with VRTs? =

The VRTs plugin primarily supports WordPress pages and posts. Automated visual testing of pages with dynamically changing content can lead to false positives. However, you can test such pages by following these steps:

* Create a new blank page or post in WordPress.
* Set up a redirect from this page to your desired URL.
* Configure a test for this page. The screenshotter will follow the redirect.

== Installation ==

= INSTALL VRTS WITHIN WORDPRESS =
(recommended)

1. Open **Plugins > Add new**
2. Search for **Visual Regression Tests**
3. Click **install and activate** the plugin


= INSTALL VRTS MANUALLY THROUGH FTP =

1. Download the plugin on the WordPress plugin page
2. Upload the ‘visual-regression-tests’ folder to the /wp-content/plugins/ directory
3. Activate the VRTs plugin through the ‘Plugins’ menu in WordPress


= AFTER ACTIVATION =

* By default: The homepage is immediately monitored and alerts are sent to the stored admin email.
* Open the settings to configure who should receive alerts.
* Open a page or post to activate more tests.


= UPGRADE TO VRTS PRO =

1. Open **VRTs > Upgrade**
2. Click **Buy Pro**
3. **Enter license key** you have received after purchasing the plugin



== Screenshots ==

1. Start a test by toggling on Run Tests in the sidebar of your WordPress page or post.
2. Get an overview of all running and paused tests.
3. Get an overview of your alerts. Review and resolve them one by one.
4. The **difference view** merges the reference snapshot and the newly created screenshot. Differences between them are highlighted in red.
5. In the **split view** you can compare two snapshots with a vertical slider.
6. The **side by side view** displays two snapshots next to each other.
7. Run unlimited manual tests.


== Changelog ==

= 1.7.1 =
* Fixed issue with uninstalling the plugin.

= 1.7.0 =
* Fixed issue with formatted entities for page title inside email notifications.
* Added bulk action "Add to VRTs" for all public post types.
* Improved test status display flow.

= 1.6.0 =
* Fixed WordPress 6.4 deprecated notices for list tables.
* Added hook allowing developers to run tests by calling `do_action( 'vrts_run_tests' )`.
* Added test page title and url to email notifications.

= 1.5.2 =
* Changed internal option name.

= 1.5.1 =
* Fixed issues with empty rest data on post update

= 1.5.0 =
* Fixed test status display
* Fixed url display in alerts list table
* Added ability to trigger tests manually in specific plans

= 1.4.0 =
* Fixed text domains for translation usage.
* Added ability to hide elements on the page during screenshot with a css selector. Editable per test via quick edit or on the post's edit page.
* Improved outdated base screenshots are removed after fixing an alert or changing a posts content.

= 1.3.1 =
* Improved functionality to create alerts only if pixel difference is > 1
* Show the current plan on the update page

= 1.3.0 =
* Changed initial validation logic
* Made functionality work with protected websites
* Added cron job to fetch test results
* Allowed adding of tests for unpublished posts
* Improved code structure
* Improved communication with external service
* Improved block editor compatibility and functionality with rest api

= 1.2.4 =
* Fixed an issue that crashes the Gutenberg editor after upgrading to WordPress 6.2
* Improved metabox behaviour inside the Gutenberg editor

= 1.2.3 =
* Fixed tests and alerts search

= 1.2.2 =
* Fixed url verification for sites using the WPML plugin

= 1.2.1 =
* Fixed url validation on plugin update

= 1.2 =
* Fixed license validations and notifications
* Fixed alert pixel count
* Fixed alert view metabox pixel count
* Fixed status codes for admin ajax
* Added notification messages if site is moved
* Added notification inside metabox when service is not available
* Improved license handling when license is added / removed
* Improved data removal on plugin uninstall
* Improved notifications logic inside metabox
* Minor UI/UX adjustments and improvements

= 1.1 =
* Fixed an issue with the add new test modal in specific cases
* Fixed notification email site url
* Added admin-ajax fallback functionality
* Added messages for error when connecting to external service
* Improved plugin deactivation & activation
* Improved test deletion
* General UI/UX adjustments and improvements

= 1.0 =
* Initial Release
