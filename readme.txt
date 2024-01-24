=== IssueM ===
Contributors: layotte, pericson, endocreative
Tags: issue management, issue manager, magazine management, magazine manager, news management, news manager, periodical manager, periodicial management, publishing, magazine publishing, issue publisher, wordpress magazine
Requires at least: 5.6
Tested up to: 6.4.2
Stable tag: 2.9.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create, Organize, and Publish Issues with WordPress

== Description ==

**"...IssueM is a real standout." &mdash; WPMU**

[IssueM](https://leakypaywall.com/issuem/) is one of the cornerstones of the leading WordPress platform for publishers.

IssueM turns WordPress into a powerful publishing platform for digital issues. Popular with alumni magazines, trade magazines, news sites, science journals, and other publishers of periodicals, IssueM brings the proven and familiar issue-based publishing model to the web. There are incredible benefits to the issue-based model of publishing:

1. Publishers love that IssueM's issue-based digital publishing slows the pace of posting to better suit their existing workflows;
1. And, readers love the long-format, less-frantic, well-organized reading experience.

IssueM is packed with features—and is gaining more with every release! Upon installation, IssueM allows publishers to:

* Create issues
* Create articles and assign articles to particular issues
* Publish an issue (and all included articles) at one time
* Display past issues on an attractive "Past Issues" archive page
* Display a current-issue article list anywhere on the site using a widget
* Allow readers to search for content across all issues
* Select featured articles to be displayed in a rotating article showcase
* Use WordPress's scheduling ability to schedule and automate the launch of an issue
* Present issues with an attractive ready-to-go (yet customizable) issue table of contents page

Some other benefits:
* IssueM works with any WordPress theme.
* IssueM is backed by the dedicated team of developers and publishers at zeen101.
* IssueM is an active project, with new updates and features released regularly.
* IssueM's developer, zeen101, is committed to providing stellar support.
* IssueM is free.

Issue-based publishing on the web is gaining steam. More and more traditional publishers are learning that using their print version's issue-based publication schedule on the web—as opposed to a running stream of content—is not only not a step backwards, but it is a welcome change for readers who are often overwhelmed by streams of content. As this publishing model grows in popularity, so does the IssueM ecosystem. Currently, zeen101 offers the following add-ons and companion plugins to turbocharge and complement IssueM.

* **[Leaky Paywall](https://leakypaywall.com/)**, when paired with IssueM, makes charging for issue subscriptions simple. Sell subscriptions to your content using any schedule (weekly, monthly, annually, etc.) and price you like. Set your "leak level" to define how many articles readers are allowed to view without a subscription for any time period you set. For example, you can allow unsubscribed readers to read 3 articles per month before a subscription is required. Leaky Paywall also allows you to set limits by content type as well. Leaky Paywall is a paid plugin.
* **[UniPress](https://leakypaywall.com/unipress-apps/)** brings your IssueM issues to iPhone and Android devices as your publication's very own app. Build a stronger relationship with your readers by providing them with a dedicated app that they can have in their pocket every day. All your issues, articles, posts, and pages flow into the UniPress app framework that zeen101 will brand as your magazine, set up, and submit to the Apple and Android app stores. It couldn't be easier. UniPress is available now. (The IssueM integration is currently under development and will be available soon.)
* **[The Issue-to-PDF add-on](https://leakypaywall.com/downloads/issue-to-pdf/)** allows publishers to convert their digital issues into PDFs with the click of a button.
* Want to convert your blog posts into issues? **[The Issue Migration Tool](https://leakypaywall.com/downloads/migration-tool/)** will migrate any post content you choose into article content, so that you can include it into your IssueM issues. Quickly create a special issue or start an issue based magazine or news site.
* If you need more power in your search box, the **[Advanced Issue Search add-on](https://leakypaywall.com/downloads/issuem-advanced-search/)** allows your readers to comb through your content in a way that isn't possible with the standard WordPress content search.

== Installation ==

1. Upload the entire `issuem` folder to your `/wp-content/plugins/` folder.
1. Go to the 'Plugins' page in the menu and activate the plugin.

== FAQ ==

**What are the minimum requirements for IssueM?**
You must have:
* WordPress 5.3 or later
* PHP 7 or higher

**How is IssueM Licensed?**
* IssueM is GPL.

== Screenshots ==
1. Berkeley Science Review - Current Issue
2. Berkeley Science Review - Older Issue
3. Berkeley Science Review - Issue Archive
4. Simmons Field Educator - Current Issue
5. Simmons Field Educator - Issue Archive
6. Einstein College of Medicine - Current Issue
7. Einstein College of Medicine - Current Issue with past issue menu (custom programming)
8. Einstein College of Medicine - Issue Archive
9. Law Practice Today - Current Issue
10. Law Practice Today - Issue Archive
11. IssueM - issue management
12. IssueM - add new article
13. IssueM - plugin settings
14. IssueM - help

== Changelog ==

= 2.9.0 =
* Update display of pdf line in archives shortcode
* Update links

= 2.8.9 =
* Remove external dependencies from plugin

= 2.8.8 =
* Code cleanup

= 2.8.7 =
* Fix pagination on IssueM archives
* Update WP admin menu icon

= 2.8.6 =
* Fix some styles
* Add attributes to do_issuem_archives_get_terms_args filter
* Update html on issuem archive issues

= 2.8.5 =
* Sanitize parameters

= 2.8.4 =
* Add ability for 3rd party plugins to add settings tabs
* Fix index error during post save

= 2.8.3 =
* Display latest issue in widget even if it has no articles attached
* Fix cover image setting on issue page

= 2.8.2 =
* Add admin columns on article for issue, categories, and tags
* Display categories in article list widget even if they are empty
* Fix javascript error that was breaking settings page

= 2.8.1 =
* Fix excerpt filtering when content has oembed
* Add issuem_article_post_type_args filter for adjusting article post type before registering
* Add article taxonomies to post type editor in block editor
* Add issuem_archives_issue_url filter

= 2.8.0 =
* Add fix for issue order
* Add fix for capability error
* Enable block editor support on article post type

= 2.7.4 =
* Add meta value to check for query for featured thumbnails and featured rotators

= 2.7.3 =
* Use media uploader for cover image on an issue
* Fix sorting by issue order in admin table
* Update how article meta is saved during a bulk edit so that options stay
* Add issuem_widget_after_issue_cover filter
* Update admin logo

= 2.7.2 =
* Add filter for statuses

= 2.7.1 =
* Fixing bug caused by using specialy characters in content/excerpts
* Adding arguments to new settings hooks (HT: pressupinc)

= 2.7.0 =
* Updating constructor for PHP7

= 2.6.1 =
* Two action hooks to allow multiple article formats
* Switching split() for explode() for PHP7 Compat
* Fixing typo in issue order check

= 2.6.0 =
* Fixing bug preventing uploads in Issue Taxonomy
* Fixing bug when saving Issue Taxonomy
* Add check to is article page conditional to fix a bug with redirect_canonical

= 2.5.0 =
* Add array keys for option values on issue statuses to fix problem with translations changing the values that were being checked elsewhere
* Add filter for issuem_active_issue_slug
* Add filter for issuem_draft_issue_tax_query ;
* Add filter to pre get posts draft tax query
* Fix header output error in admin

= 2.4.0 =
* Check if currently set post type is a string before adding article to the preferred array type

= 2.3.0 =
* Add aritcles to is_author query
* Remove extra slash in css and js link URLs
* Use explode instead of split to create array of article category slugs
* Use correct variable name issue instead of issuem_issue in the featured thumbs shortcode

= 2.2.1 =
* Modify how we add article to the post query in pre_get_posts

= 2.2.0 =
* Updating WP_Widget calls for deprecated use in WP4.3

= 2.1.0 =
* Escaping add/remove_query_arg calls properly

= 2.0.4 =
* Create add-ons page to showcase available IssueM add-ons from within the plugin
* Add helpful descriptions on the IssueM settings page
* Clean up HTML errors
* Update support links and information
* Add link to settings page from plugin listing page

= 2.0.3 =
* Update styling of article notice and add the ability to dismiss the notice

= 2.0.2 =
* Fixing bug preventing WP Categories from working in the Article List Widget

= 2.0.1 =

* Better verification for clever users who might know what your next draft issue is named, so they can't see it
* Make sure we verify the post exists before setting the content

= 2.0.0 =

* Prepping for IssueM Pro integration!
* Changing feed templates to order posts by menu_order over post_date
* Add default settings for rotator options
* Updating issuem.com links to go to zeen101.com
* Update help page layout and improve documentation
* Add RSS feed to zeen101 to IssueM settings page sidebar
* Update layout of settings page
* Add rate issuem widget to issuem settings page
* Clarify description for Article Format meta box
* Update list of wp contributors
* Add second param to get_issuem_author_name to not output name as a link
* Use the number input for font size inputs
* Update settings notification so it actually displays
* Add tabs to IssueM settings page
* Change issue_url output to only the term slug if use tax links is checked in settings
* Redux of feature image rotator options

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
