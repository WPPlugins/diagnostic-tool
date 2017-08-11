=== Diagnostic Tool ===
Contributors: devsupport@imreltd.co.uk, rbevan@restdeveloper.com, marcin@imre.co.uk 
Tags: diagnostic, smtp, email, mail, wp_mail, mailer, phpmailer, network, dns, resolvers, altered files
Requires at least: 3.3
Tested up to: 3.9.1
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides visibility of Email setup & hooks used by other plugins, Outgoing connections exposing possible firewall problems, DNS setup and test facility, Cron overview & File System Diagnostics Tool to find changed files. This plugin does not affect the normal running of your site.

== Description ==

= This plugin allows you to test... =

*	Email Setup (including hooks used by other plugins)
*	Outgoing Connections used anywhere by wordpress (and logs all calls if enabled)
*	DNS Server (and displays current servers)
*	Also, spots File Changes in your wordpress install (possibly compromised websites)

This plugin allows visibility of badly setup/very restrictive hosting, compromised hosting environments or anything that might affect wordpress functionality. 

Each logging function can be disabled in the Settings section of the plugin.

If you believe you have having issues with WordPress that are intermittent or confusing, this plugin might help point out setup/hosting issues.

= The only overhead for running this plugin is... =

*	A cron job that scans the file directory for changes. This can be disabled.
*	When an Outbound Connection (http or https) call is made, a log is taken of this call and the result stored. This can be disabled.

We have written this plugin to be light weight and not affect the normal user experience of using your wordpress install, but to help where things no longer make sense. We have taken the most common problems we have with WordPress and created simple tests for each problem.

= Support issues =

Please use the <a href="http://wordpress.org/support/plugin/diagnostic-tool">plugin support section</a>.

== Installation ==

1. Upload the plugin to the `/wp-contents/plugins/` folder.
2. Activate the plugin from the 'Plugins' menu in WordPress.
3. Diagnostic Tool menu option will become available in the main menu. No configuration is needed.
4. Optionally disable any logging functionality you are not interested in under the Settings sub menu option.

== Frequently Asked Questions ==

= Will the plugin work with versions prior to 3.3? =

I have not tested the plugin with a WordPress version lower than 3.3 so it might or it might not work. You can give it a try.

= What is the overhead for running this plugin? =

We have written this plugin to be light weight and not affect the normal user experience of using your wordpress install, but to help where things no longer make sense. We have taken the most common problems we have with WordPress and created simple tests for each problem.

= How do I check the plugin has been removed correctly =

Not every relationship works. Any feedback would be appreciated. The plugin stores data only in TABLE_PREFIX_options table.

`select option_name, SUBSTR(option_value, 1, 100) FROM wp_options WHERE option_name LIKE '%diagnostic_tool%';`

`select option_name, SUBSTR(option_value, 1, 100) FROM wp_options WHERE option_name LIKE 'cron' AND option_value LIKE '%diagnostic_tool%';`

<em>Note wp_options is likely to be different on your install.</em>

Both options should return 0 rows.

== Screenshots ==

1. Screenshot of the Overview page, showing the cron job interval and the next expected run time.
2. Screenshot of Altered Files page, showing ALTERED, ADDED and DELETED files in the WordPress base dir.
3. Screenshot of Email Testing, showing a successful test to demo@mywwwsupport.com. Also showing other wordpress plugins (never this plugin) that uses wordpress hooks to alter email functionality. Also scans the plugins directory for direct calls to mail() or wp_mail(). Full description availble on this page as to why this is important.
4. Screenshot of Outbound Connections, showing a successful test to http://www.google.com. Also showing a log of any other outbound http or https connections made via wordpress. Full description of other tranports methods that other plugins (never this plugin) may use and can cause issues.
5. Screenshot of DNS Resolver, showing your DNS Resolvers and Search Domains. Also showing a successful test DNS lookup to www.google.com.
6. Screenshot of Settings, showing the ability to disable the "Altered Files" check or "Outgoing Connections" logging.
