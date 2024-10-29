=== VRTs - Visual Regression Tests ===
Contributors: bleechberlin
Tags: vrts, visual regression, visual, regression, tests
Requires at least: 5.0
Tested up to: 6.6.2
Stable tag: 1.9.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Keep your WordPress websites bug-free with automatic screenshots, daily comparisons, and instant tests after WordPress and plugin updates.

== Description ==

Find issues before others do – every time. With automatic screenshots, daily comparisons, and instant tests after WordPress and plugin updates. Select the pages of your choice for continuous monitoring. The plugin immediately notifies you of any visual changes it detects.

**Please note:** The website must be publicly accessible in order to set up and run the tests. Password protection or any kind of firewall might prevent the plugin from working correctly.

= Your strategy to spot unwanted changes =

How do you check your website after updates and code changes? Do you manually go through pages, or do you just cross your fingers, hoping everything will be okay? Visual regression testing provides a better way to spot issues and detect visual changes. The VRTs plugin automates testing for you with a click of a button.

1. **Activate Tests for any page or post:** Upon activation, a reference screenshot is taken. This will be renewed when a post is saved or updated.
2. **VRTs monitors your pages:** Every day, VRTs captures and compares screenshots of your selected pages. Upgrade to Pro to automate Tests for WordPress updates, integrate deployment pipelines via API, and run Manual Tests on demand.
2. **Receive instant alerts:** If a change is detected between the snapshot and the comparison screenshot, the plugin will notify you via email. 
3. **Review changes:** The difference view makes it easy to spot changes, while the comparison slider lets you inspect the details.
4. **Hide elements:** Prevent false positives by excluding dynamic elements, ads and animations from snapshots.


= Use cases =

In which cases can visual regression testing help you spot issues?

* Plugin and core software updates
* Manual code changes
* External software and API issues
* Server issues
* Malware and other malicious code
* Missing quality assurance


= Features =

* **No setup:** After plugin activation, the frontpage is immediately monitored and alerts are sent to the WordPress admin email.
* **Daily Tests:** The plugin monitors selected posts and pages and compares screenshots daily.
* **Hide Elements:** Hide dynamic or irrelevant elements to prevent false positives in your Tests.
* **Click Element:** Define an element that should be clicked before taking a screenshot. This is useful for closing cookie banners or modals.
* **Email Notifications:** As soon as a change is detected between the snapshot and the comparison screenshot, you will be notified via email.
* **Fullscreen Review:** Inspect changes up close using the difference view and comparison slider in fullscreen mode.
* **Read / Unread:** Keep track of Test results that require further attention by marking Alerts as unread.
* **Flag False Positives:** Identify acceptable changes or non-issues to minimize unnecessary notifications.
* **Update Automation (Pro):** Automatically trigger Tests on WordPress core , plugin, theme, or language updates and  catch any issues right away.
* **Manual Testing (Pro):** Run Tests manually on demand, either for all configured pages or for specific pages, to verify any changes or global edits immediately.
* **API integration (Pro):** Trigger Tests via PHP scripts or WP CLI for integration with other tools, deployment pipelines or custom automations.
* **Customizable Notifications (Pro):** Specify email addresses for each trigger, to set up notifications for various teams or stakeholders.


= Free forever =
* test up to **3 pages**
* on **one** domain
* **automatic daily** Tests


= Go Pro =

Do you like VRTs and want to run more Tests?
Unlock more features with our paid plans:

* test up to **500 pages** in total
* on **multiple** domains
* **Scheduled daily** Tests
* **Multiple** alert recipient groups by alert type
* **Manual Tests** (unlimited)
* **API access** (unlimited) [Read the docs.](https://vrts.app/docs/)
* **Automatic Tests** after WordPress updates, Plugin updates and Plugin installations

[See pricing plans](https://vrts.app/pricing/)


= Bug reports =

You found a bug? Please report it by creating an issue on the [support forum](https://wordpress.org/support/plugin/visual-regression-tests/).


= Read more =

Want to learn more about VRTs?
Visit our website: [vrts.app](https://vrts.app/)


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
* Configure a Test for this page. The screenshotter will follow the redirect.

= How do I prevent screenshots from getting blocked? =

If your firewall is blocking our screenshot service, whitelist our IP address to resolve this: 49.13.14.240.

For Cloudflare, follow these steps:

1. Log in to your Cloudflare account.
2. Navigate to **Security → WAF**.
3. Click on **Tools**.
4. In the **IP Access Rules box**, enter 49.13.14.240.
5. Select **Allow** from the action dropdown.
6. Add “VRTs” as the note.
7. Click **Add**.


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

1. Review changes using the difference view and comparison slider. 
2. Get an overview of all past Test Runs, their Triggers and detected changes.
3. Manage all your registered Test pages.
4. Activate Tests right within the editor.
5. Specify multiple alert recipients for each Trigger.
6. Run unlimited Manual Tests at any time (Pro Feature).


== Changelog ==

= 2.0.0 =
* Introduced Runs
* New Test Review experience
* Automatic Tests on WordPress and plugin updates
* Customizable notification recipients for each Trigger

= 1.9.1 =
* Fixed alerts pagination SQL query compatibility with MySQL 5.7 and lower.

= 1.9.0 =
* Test now run continuously and do not pause upon alerts.
* Added onboarding for tests and alerts.
* Fixed character encoding in alert emails.
* Improved test status display inside the Gutenberg editor and classic metaboxes.
* Removed pagination for tests.
* Minor wording and styling changes.

= 1.8.0 =
* Added option to mark alerts as false positive.
* Improved tests order by status.
* Minor wording and styling changes.

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
