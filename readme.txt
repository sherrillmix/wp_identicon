=== Plugin Name ===
Contributors: scottsm
Donate link: http://scott.sherrillmix.com/blog/
Tags: comments, avatar, identicon, geometric, shape , gravatar, icon
Requires at least: 1.5
Tested up to: 2.5
Stable tag: 1.02

Creates a unique, persistent geometric icons avatar for each commenter based on email address. 

== Description ==

This plugin provides a small randomly assembled icon made from geometric shapes (e.g. snowflakes or quilts) for each user based on their email address. Think gravatar only without requiring any external site and with consistently themed geometrical icons. Now with icons consistent across servers. Based on idea and images by [Don Park](http://www.docuverse.com/blog/donpark/2007/01/18/visual-security-9-block-ip-identification). See the plugin website if you need any help or for an example of the plugin in action.

== Installation ==

1. Unzip `wp_identicon.zip`. 
1. Upload `wp_identicon.php` and the `identicon` folder to `wp-content/plugins`. 
1. Make sure the `identicon` folder is [writable](http://codex.wordpress.org/Changing_File_Permissions). 
1. Activate the plugin in the Plugins Admin page.
1. Identicons should now appear beside commenters' names. Enjoy. (Advanced users can edit their theme file if they want further control).
1. You can add CSS for `img.identicon` in your theme's style.css to adjust the appearance of the images or adjust the size in the Identicon control panel (your old identicons won't be deleted until you clear the cache). You can also turn on Gravatar support or clear the Identicon image cache in the Control Panel.

== Frequently Asked Questions ==

= Will my identicon be the same on different blogs? =

Yes, if they're using the standard version (and I didn't mess up anything).

= Can it generate Identicons only for people without gravatars? =

Yes. Just turn on the Gravatar option in the Identicons options page.

= Can I add Identicons to the Recent Comments Widget in my sidebar? =

Yes, this plugin provides a replacement widget to Recent Comments (since the default widget doesn't provide the commenter's email).

= Does it work with WP 2.5+ themes with builtin avatars? =

Yes, just select the WP 2.5+ Theme option to the Automatically Add Identicons setting in the Identicon control panel.

= Why should I use this plugin when Gravatars now has Identicons? = 

The gravatar identicons are pretty good but if you want more control (custom colors, transparent backgrounds, different numbers of blocks) or just want one set of avatars for everyone on your site, then this plugin's for you. Otherwise, sure go with gravatars (just set <code>&default=identicon</code> in the gravatar url).

= Could you implement XXXX feature? =

Probably (if I have time/know how). It's definitely worth a shot since I'm always looking for feedback on how to improve my plugins. 


== Screenshots ==

1. An example of WP_Identicon in action.

2. The parts identicons are generated from.

