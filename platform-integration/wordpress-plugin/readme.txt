=== SuperSeeded Upload ===
Contributors: superseeded
Tags: upload, file upload, csv, excel, data enrichment
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embeddable file upload widget for SuperSeeded data enrichment platform.

== Description ==

SuperSeeded Upload adds a beautiful, reliable file upload widget to your WordPress site. The widget supports resumable uploads using the TUS protocol, making it perfect for uploading large CSV and Excel files for data enrichment.

**Features:**

* Drop-in upload widget with drag & drop support
* Resumable uploads for reliable large file transfers
* Gutenberg block and shortcode support
* Light and dark themes
* Customizable via CSS variables
* Secure token-based authentication

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/superseeded-upload/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > SuperSeeded Upload to configure your API credentials
4. Add the widget to any page using the shortcode or Gutenberg block

== Configuration ==

1. Navigate to Settings > SuperSeeded Upload
2. Enter your Platform API Key (from SuperSeeded dashboard)
3. Enter your default Merchant ID
4. Configure theme and file restrictions as needed

== Usage ==

**Shortcode:**

`[superseeded_upload]`

With options:

`[superseeded_upload merchant_id="acme-corp" theme="dark"]`

**Gutenberg Block:**

Search for "SuperSeeded Upload" in the block inserter.

== Shortcode Attributes ==

* `merchant_id` - Override the default merchant ID
* `theme` - Widget theme: 'light' or 'dark'
* `class` - Additional CSS classes

== JavaScript Events ==

The widget dispatches custom events that you can listen to:

* `superseeded:file-added` - When a file is selected
* `superseeded:progress` - Upload progress updates
* `superseeded:complete` - Upload completed successfully
* `superseeded:error` - Upload failed

Example:

`document.querySelector('.superseeded-upload-container').addEventListener('superseeded:complete', function(e) {
    console.log('Upload complete:', e.detail);
});`

== Frequently Asked Questions ==

= Where do I get my API key? =

Log in to your SuperSeeded dashboard and navigate to the API Keys section.

= What file types are supported? =

By default, the widget accepts CSV, XLSX, and XLS files. You can customize this in the plugin settings.

= Is my API key secure? =

Yes. Your API key is stored securely on the server and is never exposed to the frontend. The plugin uses a secure token proxy to authenticate uploads.

== Changelog ==

= 1.0.0 =
* Initial release
* Shortcode support
* Gutenberg block support
* Admin settings page
* Secure token proxy

== Upgrade Notice ==

= 1.0.0 =
Initial release.
