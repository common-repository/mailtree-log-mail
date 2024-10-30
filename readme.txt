=== Mailtree Log Mail  ===
Contributors: oacstudio
Requires at least: 4.0
Tested up to: 6.5
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: log mail, mail log, log, mail

A solid mail logger with additional REST API support to backup your messages to an external WordPress automatically.

== Description ==

This plugin logs all mails that use the wp_mail() function (should be almost all). Mail can be viewed, downloaded as CSV and resend.

The plugin has REST API support. Log entries can also be exported automatically to another (archive) WordPress site that runs Mailtree. Mailtree uses the REST API for exporting entries and also has an automatic retry function to account for connection errors.

## Feature list

### Logs:

* Logs all emails using the wp_mail() function (should be almost all!).
* Download single entry as CSV.
* Bulk download entries as CSV.
* Download all entries as CSV.
* Delete single entry.
* Bulk delete entries.
* View all, only failed or only successful sent messages.
* Logs additional info such as trigger, exact time, content type, reply to address and full HTML.
* Search log entries.

### Settings:

* Set capability to view logs.
* Set capability to view settings.

### Auto export / REST API:

* Export all entries to an external site automatically using Application Passwords and the REST API. Great for keeping a mail archive.
* Failed auto exports are retried automatically.
* Provides two REST API endpoints to save log entries (sent successful / failed).

### Misc:

* Translation-Ready

### Roadmap:

* Attachements are not logged or saved. However the message detail will show a notification that an attachement was sent.


== Installation ==

1. Upload `mailtree.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the new "Mailtree" main menu item to view entries and configure settings.

== Frequently Asked Questions ==

= How do I create an Application Password for auto export? =

Visit your user profile in the backend via `/wp-admin/profile.php`.
Scroll down "Application Passwords" and enter any name i.e. "Mailtree". It does not matter what name you pick.
Click "Add New Application Password".
The new Application Password gets displayed. Save it somewhere. It will not get displayed again.

Please note: The user that creates the Application Password must have the capability required by the "User capability needed to see logs (and access REST API endpoints)" setting.

= Are attachements logged? =

Not yet.
Attachements are not saved and also not auto exported to an external site.

== Screenshots ==

1. Log overview
2. Log entry
3. Settings

== Changelog ==

= 1.0.1 =
* Fix: Escape subject output in admin table.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =

Added an escape function. Update recommended asap.

= 1.0.0 =

None yet. This is the beginning :).
