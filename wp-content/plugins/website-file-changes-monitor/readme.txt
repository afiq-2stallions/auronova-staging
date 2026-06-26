=== Melapress File Monitor ===
Contributors: Melapress
Plugin URI: https://melapress.com/wordpress-file-monitor/
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.html
Tags: security, file monitor, malware detection, file security, file changes
Requires at least: 5.0
Tested up to: 6.9.1
Stable tag: 2.3.0
Requires PHP: 8.0

Get email alerts for file and permission changes on your WordPress sites. No false positives!

== Description ==

### Get notified of file and permission changes on your WordPress sites and boost reliability & security 

Melapress File Monitor is a WordPress file integrity monitoring plugin that keeps track of file and permission changes on your WordPress websites. It enables you to promptly identify code changes, file and directory permission changes, leftover files, malicious code, and malware injections - and take action.

Install [Melapress File Monitor](https://melapress.com/wordpress-file-monitor/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=mfm) on your website to:
*	Detect malware, infected files or files altered by bad actors
*   Keep track of the last code changes on your website for easier troubleshooting
*	Identify changes in file and directory permissions
*	Identify leftover & backup files that can lead to sensitive business & technical data exposure
*	Spot malware injections early to avoid irreparable site damage
*	Conduct essential WordPress forensic analysis after a cyberattack.

The plugin allows you to monitor and log file and permission changes across your WordPress site. You can see changes directly in the WordPress dashboard for easy access. You can also configure the plugin to send you file and permission change alerts through email whenever it detects a change; keeping you informed wherever you are. 

It helps you easily spot leftover and backup files that could leave your website exposed, and detect malware and code changes, so you can remove the files and clean malware infections at the earliest possible.

### Plugin Features

Melapress File Monitor is a very easy to use plugin with zero admin work. 

#### No False Alarms - Just Genuine Alerts!

This plugin uses an exclusive smart technology that detects WordPress core updates, plugin & theme installs, uninstalls, and updates.

When you update the WordPress core, install a new plugin, update a theme, or delete a plugin it won't flood you with hundreds of alerts prompting a false alarm. You only get alerted of genuine file and permission changes that can have an effect on the functionality and security of your WordPress site!

#### Instant Email Notifications

After a scan, the plugin sends an email with the list of file and permission changes it identifies on your WordPress sites and multisite networks.

The email includes all the details you require to track WordPress file changes, such as:
* The filename and the path of the file
* A count of how many files were added, modified or deleted
* A highlight of the site admin changes that caused the file changes, such as the plugins or themes installs, uninstalls, and updates.

#### Scans ALL Your Files, Including Custom Code

Melapress File Monitor can scan any type of file and it is not limited to WordPress and PHP files. Apart from the WordPress core files, plugins and themes files, it will also scan any other custom code files that you might have on your WordPress site. 

It also compares the WordPress core files of your website to the list of files on the official WordPress repository, so it will also alert you if a WordPress core file has been tampered with, or changed. You can also choose to exclude specific files, directories, and extensions for complete control.

To learn more on both the file integrity monitoring technologies the plugin uses refer to [how the plugin detects file changes on WordPress](https://melapress.com/support/kb/website-file-changes-monitor-how-plugin-detects-file-changes/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=mfm)

#### WordPress Multisite Networks Support

The Melapress File Monitor plugin can also detect file changes on WordPress multisite networks. When installed on a network, the plugin configuration and alerts are only available to the super administrators, preventing possible disclosure of sensitive information that could jeopardize the security of the sites on the network.

#### Other Notable Plugin Features

* Optimized scanning technology that does not affect the performance of your site
* Fully configurable file scan frequency (hourly, daily, weekly, time of the day)
* Instant file integrity scans with just a click of a button
* Ability to exclude directories, files, and file types from the scan
* Configurable maximum file size to scan
* File changes data only available to administrators for better security

##Free Plugin Support
Support is available for free via:

* [forums](https://wordpress.org/support/plugin/admin-notices-manager/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=mfm)

* [email](https://melapress.com/support/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=anm)

## MAINTAINED & SUPPORTED BY MELAPRESS

Melapress builds high-quality niche WordPress security & management plugins, including WP Activity Log, Melapress Login Security, and others.

Visit the [Melapress website](https://melapress.com/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=anm) for more information about the company and the plugins it develops.

== Installation ==

=== Install the plugin from within WordPress ===

WordPress security is easy with Melapress File Monitor. Simply: 

1. Navigate to Plugins > Add New, from your WordPress dashboard
2. Search for Melapress File Monitor
3. Install & activate the plugin from your Plugins page

=== Install the plugin manually (via file upload) ===

1. Download the plugin from the WordPress plugins repository
2. Unzip the zip file and upload the folder to the `/wp-content/plugins/` directory
3. Activate the Melapress File Monitor plugin through the Plugins page in WordPress

### Translate the plugin in your own language
If you want to help us translate this plugin in your own language please [contact us](https://melapress.com/contact/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=mfm). We will credit all translators.


== Frequently Asked Questions ==

= Is Melapress File Monitor free? =
Yes, Melapress File Monitor is a 100% free plugin.

= Does the plugin send any data to Melapress? =
No, the plugin does not send any data to us whatsoever. 

= Can I use Melapress File Monitor as a malware detector or to detect security breaches? =
Melapress File Moniotor is not an IDS (Intrustion Detection System) and as such does not monitor for breaches. However, if a bad actor managed to gain access and alters a file, Melapress File Monitor will alert you of the change. To that extent, it can function as a type of malware detection system, without actually specifying the type of malware or analysing the signature. It would detect malware by alerting you of file changes, but not specifying whether it is malware or not.

= Does Melapress File Monitor track file changes across my entire WordPress website? =
Yes, Melapress File Monitor scans all of your WordPress files including WordPress core files, custom code, plugins, themes, and media. You can set exemptions if you want so that certain file extensions and directories are not included in the scan. You can also ask the plugin to cross-check WordPress core files with the official WordPress repository.

= Where can I see file changes? =
Melapress File Monitor automatically send an email at the end of the scan with its findings. You can also see deteced file changes in the WordPress dashboard.

= Does Melapress File Monitor keep a history of scans?
Yes, you can configure the plugin to keep a histroy of all scan results.

= Does the plugin receive updates? =
We update the plugin regularly to ensure the plugin continues to run in tip-top shape while adding new features from time to time.

= Does Melapress File Monitor include support? =
At Melapress we stand behind all of our plugins, which is why we include one-to-one email support with all of our plugins. We also offer a [Knowledge Base](https://melapress.com/support/kb/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=anm) with tutorials and quick answers to commonly asked questions.

= How do I uninstall Melapress File Monitor? =
To uninstall Melapress File Monitor, login to your WP admin dashboard, navigate to Plugins > Installed Plugins, locate Melapress File Monitor, click on Deactivate and then Uninstall.

= How can I report security bugs? =
You can report security bugs through the Patchstack Vulnerability Disclosure Program. Please use this [form](https://patchstack.com/database/vdp/website-file-changes-monitor). For more details please refer to our [Melapress plugins security program](https://melapress.com/plugins-security-program/).

== Screenshots ==

1. The plugin is very easy to install and configure - follow the install wizard through which you can configure the scan frequency and times, email notification settings, which file extensions to exclude from a scan and other scan details via an easy to follow setup wizard.
2. Once the wizard is completed the first time scan starts automatically.
3. In the plugin dashboard you can see the recent list of file changes events, which should give you a good overview of the detected file changes.
4. The plugin's smart technology does not just report file changes. It can detect if a new plugin has been added, updated, or removed, and it reports the details rather than just flagging a file change.
5. The plugin can also detect when new files are added to the core of your website, even if it is not a WordPress or php file.
6. You can use the plugin's search filter to search for specific files and find what you need for in just seconds, in case you have a good number of file changes events.
7. The plugin can also detect changes in the WordPress core. During the scan it compares the core on your website to that on the official repository, and if there are any differences, it will report them to you.
8. The plugin is fully configurable and can easily be fine tuned to meet your requirements. Every setting in the setup wizard, and many other settings, can be configured at any time from the plugin's Settings page.


== Changelog ==

= 2.3 (2026-02-26) =

 * **New features**
	 * Added support for wildcards in folder exclusions: you can now use `*`, `**`, and `?` wildcards when defining the directories you want to exclude.

 * **Plugin improvements**
	 * Excluded file extensions are now fully skipped during scans. Previously the plugin was still reading and hashing these files. 
	 * Ignored directories are now properly excluded from file change detection as well as from the scan itself.
	 * Refactored the file comparison logic for improved performance, accuracy, and maintainability.
	 * Optimized the way the plugin stores certain data so it no longer creates 10s of 1000s of temporary records during scans in the `wp_options` table. They're now limited, most websites will see less than 20 temporary records in `wp_options` and strictly only during scans.
	 * Temporary files created by the phpfastcache library are now stored in `wp-content/uploads/melapress-file-monitor/mfm-tmp/` and are properly removed after use.
	 * Improved the cron job's reliability to avoid scan interruptions and related scheduling issues.
	 * Applied a number of generic scan performance and speed improvements.
	 * Removed outdated Captcha 4WP references from the About page and updated branding assets.

 * **Bug fixes**
	 
	 * Fixed: PHP warning fileperms(): stat failed caused by phpfastcache writing temporary files to non-existent paths.
	 * Fixed: all database tables and cron jobs are now properly cleaned up on uninstall.
	 * Fixed: Cron job `mfm_monitor_file_changes` was not removed on plugin deactivation or uninstall due to an incorrect reference name.
	 * Fixed: Files above the configured size limit were still having permissions changes and other events reported.
	 * Fixed the PHP fatal error `wp_is_valid_utf8()` call to undefined function triggered in the setup wizard on completion.
	 * Fixed: PHP fatal error caused by `phpfastcache` returning a non-array value in `Driver::driverRead()`.
	 * Fixed: `TypeError` when saving settings with a null value passed to `strtoupper()`.
	 * Fixed: PHP fatal error `Call to undefined method Directory_Runner::delete_all()` in `class-db-handler.php`.
	 * Fixed: PHP notice for translation domain being loaded too early.
	 * Fixed: The scan status REST endpoint was publicly accessible without authentication.
	 * Fixed: Plugin was incorrectly identified as "File Manager" in [WP Activity Log](https://wordpress.org/plugins/wp-security-audit-log/) logs. Now it is correctly shown as "Melapress File Monitor".

Refer to the complete [plugin changelog](https://melapress.com/support/kb/website-file-changes-monitor-plugin-changelog/?utm_source=wp+repo&utm_medium=repo+link&utm_campaign=wordpress_org&utm_content=mfm) for more detailed information about what was new, improved and fixed in previous version updates of Melapress File Monitor.




