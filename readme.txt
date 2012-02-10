=== Odds Widgets ===
Contributors: dionesku
Tags: odds, bookies, sport odds, sport widgets, widgets, betting, betting widgets, betting odds
Requires at least: 2.8.0
Tested up to: 3.3.1
Stable tag: 1.1.1

Odds Widgets plugin will allow you to create, configure and display live odds tables as widgets in your blog

== Description ==

Odds Widgets plugin is an interface to [Valuechecker.co.uk Odds Widgets](http://widgets.valuechecker.co.uk/) application which allows one to configure, build and place live odds widgets on any website through a small piece of JavaScript code. 
The process is very simple and in less than 2 minutes you can display an odds widget on your website. 
Odds Widgets plugin is an interface to the online API that allows you to configure, build and place odds widgets on your blog from within WP Widgets Admin

== Installation ==

1. Install Odds Widgets plugin either via the WordPress.org plugin directory or by uploading 'odds-widgets' folder to the '/wp-content/plugins/' directory on your server
2. Activate the plugin through the 'Plugins' menu in WordPress admin
3. That's it! You're ready to show live odds on your blog! Check FAQ section for more info!

== Frequently Asked Questions ==

= How do I setup an odds widget on my blog =

1. Go to Admin / Appearance / Widgets
2. Drag an "Odds Widget" widget to the sidebar where you want it to appear
3. In widget's config panel select sport, widget type and widget
4. Select a widget style that better matches you site's color scheme
5. Preview the widget
6. Save the widget
7. The widget should show in the selected sidebar on front on next refresh

= What versions of WordPress is this plugin compatible with? =

Odds Widgets plugin works with WP 2.8 and newer (it's been tested up to WP 3.3.1) 

= Are there any special requirments for this plugin to work? =

In order for the plugin to communicate with the **Odds Widgets API**, you need to have **cURL** extension enabled on the server

= Do the widgets loading slow down my pages? =

They shouldn't! The widgets load data asynchronous from our servers, so your page doesn't wait for them to load.

= How fast do the Odds Widgets load =

The first time you refresh a page with a widget, you might notice the widget takes one to few secconds to show. This is needed for our servers to build it based on your settings. However, our servers use advanced caching systems to speed up the load of your widgets, so on next page reloads, the widgets on your page should load almost instantly.

== Screenshots ==

1. Single Tournamnet Odds Widget
2. Horseracing Daily Races Odds Widget
3. Odds Widget admin form

== Changelog ==

= 1.1 =
* First release

= 1.1.1 = 
* Added a new type of widget: Matches List. You can now create and display widgets with lists of matches for the sports that feature this type of events
* Narrowed down the widget preview window to closer match average sidebar width, so the widgets preview look more like they actually do when showing in your blog sidebar