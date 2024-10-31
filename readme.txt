=== RSS llama ===
Contributors: oooorgle
Donate link: http://oooorgle.com/plugins/wp/rss-llama/
Tags: rss, feed, reader, subscribe
Requires at least: 4.2.2
Tested up to: 6.6.2
Stable tag: 2.0.0
Requires PHP: 7.0
License: CopyHeart
License URI: https://oooorgle.com/copyheart

Categorize your rss feeds into a useful page.

== Description ==
= Display organized summaries of your (Links Manager) RSS feeds. =

== Installation ==
= Install via Dashboard =
* Go to *Plugins* -> *Add New* -> *Upload*.
* Search for *rss-llama*.
* Click *Install Now*.
* Activate the plugin through the *Plugins* menu in the Dashboard.

= Install via .zip file =
* After downloading, go to *Plugins* -> *Add New* -> *Upload Plugin*.
* Click *Browse* and select the rss-llama.zip file.
* Click *Install Now*.
* Activate the plugin through the *Plugins* menu in the Dashboard.

= Install via FTP =
* After downloading and extracting the rss-llama.zip file. Upload the *rss-llama* folder to the */wp-content/plugins/* directory.
* Activate the plugin through the *Plugins* menu in the Dashboard.

= Usage and Shortcodes =
* To include this plugin in a template file: <code>do_shortcode( '[rss-llama]' );</code>
or, to include this plugin in a block, page, or post: <code>[rss-llama]</code>
* Populate an "RSS Address" field in one or more of your WordPress Links to include them in the list.
* By default all rss feeds are included. To show just a single category, include the category name: <code>[rss-llama cat='Category Name']</code>

== Frequently Asked Questions ==
* "What is the Links Manager?"
 * As of WordPress 3.5. The Links Manager is hidden by default. Learn more: [About Links Manager](https://codex.wordpress.org/Links_Manager) 
* "But I have a lot of links - I don't want feeds from them all. Is this all or nothing?"
 * You can display all links or just links from a specific category.
* "Does this plugin use Cookies?"
 * Yes. Cookies are used to store filter terms and for sites that have been de-selected in the index.
* "Does this plugin use Javascript?"
 * Yes.
* "Multi-site?"
 * This plugin has not yet been tested in a multi-site environment.

= Troubleshooting =
**If you encounter any problems -- thing you can try:**

* Check and Save the options by visiting the options tab and clicking Save at the bottom.

* Disable **caching** plugins and clear your browser **cache**.

* Check that the **shortcode** you are using is accurate and formatted correctly.

* **Reset the options**: Verify "Reset When Deactivating" is enabled in the plugin options tab, then deactivate/activate the plugin.

* View the **console** and enable **WP_DEBUG** mode to check for notices, warnings or errors. If you find any regarding this plugin, open a support ticket.

* **Re-install**: Deactivate and delete the plugin (this does not delete quote data) and re-install from WordPress.

* If any of the plugin files have been edited or changed try a re-install.

* Deactivate all **other plugins** and verify the problem still exists.

* Test some **different themes** and verify the problem still exists.

= Security =
* Be aware that external linking of sites and images creates the possibility of a (BLH) attack. Broken link hijacking (BLH) is a type of web attack which exploits external links that are no longer valid. Mainly due to an expired domain. The link content can be replaced and redirected, used to deface, impersonate, or even launch cross-site (XSS) scripting attacks.

== Changelog ==
= Upgrade Notice =
* Some options have been added or changed... If you encounter difficulty, you may need to verify the option "Reset When Deactivating" is set and saved, then Disable/Enable the plugin. You can also check and Save the options by visiting each tab and clicking Save at the bottom.
* [Version History](https://oooorgle.com/downloads/rss-llama/dev/versions.htm)

== Screenshots ==
1. Admin page
2. Admin page
3. Single Feed
4. Summary Feed