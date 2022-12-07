=== VRTs - Visual Regression Tests ===
Contributors: bleechberlin
Tags: vrts, visual regression, visual, regression, tests
Requires at least: 5.0
Tested up to: 6.1
Stable tag: 1.1.0
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
4. **Resolve tests:** After fixing an issue, mark the alert as resolved. A new snapshot will be generated and the test will start running again.


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
* Test up to **3 pages**
* **Daily testing** interval
* **30 days** alert history


= GO PRO =

Do you like VRTs and want to run more tests? Unlock more features with VRTs Pro:

* Test up to **25 pages**
* **Daily testing** interval
* **90 days alert** history
* **Multiple alert** recipients
* **E-mail support**



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

= Does the plugin work with cookie consent banners? =

Yes, cookie banners are not an issue. Before taking a snapshot, the tool can automatically trigger the Accept button to hide the banner. This option can be configured with CSS selectors in the plugin settings.



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


== Changelog ==

= 1.1 =
* Fix an issue with the add new test modal in specific cases
* Fix notification email site url
* Add admin-ajax fallback functionality
* Add messages for error when connecting to external service
* Improve plugin deactivation & activation
* Improve test deletion
* General ui/ux adjustments and improvements

= 1.0 =
* Initial Release
