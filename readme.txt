=== IssueM ===
Contributors: layotte, peterericson
Tags: magazine manager, issue manager, news manager, news management, periodical manager, periodicial management, issue management, magazine management
Requires at least: 3.3
Tested up to: 4.0 
Stable tag: 1.2.12

The world first and easiest to use Issue Managing plugin for WordPress.

== Description ==

Many sites use WordPress for as a magazine or news manager. There are dozens of themes out there geared to making WordPress "look" like a issue based site. But [IssueM](http://issuem.com/) is the first plugin that truly takes the Issue based system to the next level in WordPress.

The IssueM plugin makes a distinction between WordPress' blogging platform and creates a new level of Articles which are associated with Issues. Issues can be drafted until they are ready for release, yet still visible on the site for Administrators, Editors, Authors, and Contributors. IssueM gives you hassle-free control over your issue based sites. IssueM also has a simple to use WordPress shortcode system, making it simple to integrate into your existing theme. Now you have the freedom to pick a theme that fits your site's needs without compromising on the flexibility of the functionality of your site.

You can follow this plugins development on [GitHub](https://github.com/zeen101/issuem)

Premium Add-ons Available:
Leaky Paywall w/ Stripe and PayPal Integration - Charge your customers to view your blog posts and/or IssueM articles. Find out more at http://LeakyPW.com
Post Migration Tool - Convert your old blog posts into Issue based Articles!
Advanced Search Tool - An advanced search system that lets your site visits search terms in specific articles in specific categories, tags, and/or issues!

Also, be sure to check out our [IssueM Magazine theme](https://issuem.com/downloads/issuem-magazine-theme/) to help get you started today on your new online magazine!

For support, demos and premium add-ons, please visit [IssueM.com](http://issuem.com/).

== Installation ==

1. Upload the entire `issuem` folder to your `/wp-content/plugins/` folder.
1. Go to the 'Plugins' page in the menu and activate the plugin.

== Frequently Asked Questions ==

= What are the minimum requirements for IssueM? =

You must have:

* WordPress 3.3 or later
* PHP 5

= How is IssueM Licensed? =

* IssueM is GPL

== Changelog ==
= 1.2.12 =
* Setting issuem_issue cookie properly for article views
* Added issuem_issue setting for taxinomical links
* Update support links to point to zeen101.com
* Added link to issue name in the IssueM Active Issue widget
* Added DATE to issuem_replacement_args to get access to post date
* Added function to get the article excerpt by id for use in the featured thumb shortcode when the excerpt need
* Update featured rotator so that caption is clickable
* Added media queries to featured thumbnail layout and clean up font styling from the article list
* Added message to IssueM Article Format section describing what it does
* Update layout and position of IssueM Article Options on edit article page

= 1.2.11 =
* Adding missing code to display PDF link in widget when External PDF Link is not empty and set to display PDF links

= 1.2.10 =
* Fixed readme
* Fixed messed up constant variable name

= 1.2.9 =
* Updating IssueM references to point to zeen101
* Update show_thumbnail_byline variable
* Add title field to IssueM Active Issue widget
* Change object name to article instead of post for the featured thumbnail category list arguements
* Add display thumbnail byline to issuem settings page
* Filter the_content plus... Related to commit 854d12b79c2b7701ad1712fff6f387246ee5a218
* Applies 'the_content' filter to article content
* Changing merged pull request (minor update)
* Update issuem-functions.php
* Customizing Flexslider to deal with conflicting themes/plugins that enqueu their own version
* Fixed activation hook function call
* Move inline css to admin css file'
* Replace file uploader with wp media uploader for default issue image
* Remove toggle arrows from settings boxes
* Add settings sidebar for additional content
* Update layout of settings page
* Fixing responsive issue with captions in flexslider

= 1.2.8 =
* Fixing featured thumbnail shortcode to show categories properly 
* Adding filter and CSS class for no articles found message on articles shortcode
* Add getting started video and text to issuem help page

= 1.2.7 =
* Adding Order Direction to IssueM Article Widget
* Adding limit/next/previous capability to archive list
* Fixing tested up to line in readme.txt
* Adding args attribute to modify get_terms for archive shortcode
* Add styling to center featured thumbs with percentages
* Change featured thumbnail shortcode layout markup and remove font family styling

= 1.2.6 =
* Fixed Article Category widget dropdown bug

= 1.2.5 =
* Fixed Issue Ordering Bug

= 1.2.4 =
* Updating POT file
* Added new filter for leaky paywall to restrict PDF downloads
* Fixed article category sorting bug
* Fixed article import bug with Issue's missing meta data
* Added fix for query reset on widgets
* Added fix for category ordering in shortcode
* Updating some CSS and tested up to version
* Added option for the [issuem_archives] shortcode to generate issue urls using get_term_link instead of based on the shortcode page with a backup to the previous links in the event of an error

= 1.2.3 =
* Added integration for upcoming Issue to PDF plugin
* General Code Cleanup

= 1.2.2 =
* Fixed bug in Extrenal Link setting for Issues
* Fixed bug in displaying WordPress author as First & Last name
* Fixed some PHP docblock

= 1.2.1 =
* Added function/hook to add featured image to article post type, if not supported by theme
* Rewrote flush rewrite rules activation hook for better performance
* Fixed version number in PHP comments
* Added option to set page for issue archives
* Added filter to set default page content for issue archive page (if no content exists)

= 1.2.0 =
* OpenSourceInitiative Release!

= 1.1.8.1 =
* Fixed typo in Editor permissions

= 1.1.8 =
* Fixed bug in advanced search shortcode
* Added pre_get_posts filter call to modify the WordPress Categories/Tags query if not using IssueM Categories/Tags
* Added shortcode to display current issue title
* Fixed IssueM Category and Tag names
* Fixed permissions
* Fixed typo in str_ireplace helper function

= 1.1.7 =
* Fixed bug in shortcodes when using the IssueM Category Order field. Must set use_category_order="true" in the issuem_articles shortcode.
* Fixed bug in shortcodes not displaying the Author name when "Use IssueM Author Name instead of WordPress Author" option is set.

= 1.1.6 =
* Fixed bug in shortcodes defining the specific issue slug.
* Fixed i18n text domain directory typo
* Fixed typo in advanced search form
* Added Swedish translation
* Fixed error in issuem.pot file

= 1.1.5 =
* Modifed CSS styling to deal with themes overwriting the Featured Image Rotator
* Added Spanish translation

= 1.1.4 =
* Modified archive shortcode to use external link setting depending on issue logic
* Added 'archive_issue_url_external_link' filter to set the external link for as the primary article URL too
* Added 'issuem_author_name' setting to IssueM user interface

= 1.1.3 =
* Fix permalink rewrite cache issue for new users
* Added i18n support for future translations
* Added 'issuem_author_name' filter for users who do not want to use the built-in WP-User authors
* Switched from jqFancyTransitions jQuery slider to FlexSlider for responsive functionality

= 1.1.2 =
* Fixed bug in buyer's news

= 1.1.1 =
* Added informational buyer's news section to articles page
* Added show_featured option to articles shortcode (off by default), to prevent articles in the rotator/thumbnails from also appearing in the article list

= 1.1.0 =
* Fixed bug getting new version information from IssueM API
* Added API warnings and errors

= 1.0.9 =
* Fixed bug with issue order causing last Issue ID to be first regardless of order
* Added 'article_category' argument to issuem_articles shortcode, now you can specify a single category

= 1.0.8 =
* Set default issue order to issue_order for widgets
* Enabled issue order sortable column in dashboard, set it as the default sort
* Fixed some missing l18n text
* Added orderby option to article list widget
* Added issuem_issue_url filter for users who want to change the default Issue URL
* Added 'external link' field to issue meta, for users who want to link to a PDF/URL not hosted on their own site
* Fixed bug caused by users who set the Issue Order for only some of their issues
* Fixed bug in issuem_replacements_args workflow

= 1.0.7 =
* Set default issue order by issue_order instead of issue date
* Added filter to set default page content for issue page (if no content exists)
* Hot linked cover image to issue page in Active Issue widget
* Added CSS to Article List Widget to highlight current article (if on one)
* Fixed Article List Widget so it displays empty issues

= 1.0.6 =
* Fixed bug IssueM Migration tool link showing up when it was not enabled
* Fixed bug in Active Issue Widget, not showing default IssueM issue image when not specific issue image was set

= 1.0.5 =
* Added IssueM Migration Functionality, to migrate Posts to Articles
* Fixed issuem_archives shortcode to display correctly
* Added CSS and JavaScript auto version matching with IssueM version

= 1.0.4 =
* Fixed PHP Warning bug for site with no issues
* Added ability to users with 'manage_issues' permission to see Draft issues
* Added ability to specify an issue in the issuem_articles, issuem_featured_rotator, and issuem_featured_thumbnails shortcodes

= 1.0.3 =
* Fixed Verify API button
* Fixed Archived Issue default format alignment
* Fixed IssueM Cookie settings

= 1.0.2 =
* Added RSS Feed filtering, so draft issues do not show up in RSS feed and only the active issue article shows up by default
* Added category and number of Article options to Article List widget
* Minor bug fixes

= 1.0.1 =
* Beta Release
* General Code Cleanup
* Added ability to choose open target for PDF links
* Added a number of helper functions
* Fixed template names

= 1.0.0 =
* Beta Release

== License ==

IssueM
Copyright (C) 2011 The Complete Website, LLC.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
