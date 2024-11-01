=== TSA Wait Times ===
Contributors: jdyken
Tags: tsa, security, wait, times, checkpoint, precheck
Requires at least: 4.6
Tested up to: 6.5.5
Requires PHP: 5.6
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Retrieve estimated wait times for all TSA security checkpoints for U.S. airports.

== Description ==
Retrieve current estimated wait times for all security checkpoints at all U.S. airports. You can also retrieve current airport delays.

Choose whether to display current FAA ground delays or ground stops for any airport.

Choose whether to display TSA Precheck availability as well as current status of any Precheck lanes.

== Installation ==
Instructions for implementing the plugin can be found at https://www.tsawaittimes.com/wordpress

First things first - you'll need to download and activate the TSA Wait Times plugin. From the WordPress Administration screen, click on "Plugins" and look for the TSA Wait Times plugin. When you find it, click the Activate link.

In order for the plugin to talk to TSAWaitTimes.com, you'll need to create an API key - please note that access may require a paid subscription. Your key allows your plugin to tell us who you are and allows us to monitor the resources used so that we can continue to provide a responsive platform for everyone.

If you don't have an account at TSA Wait Times, you'll need to create one, but don't worry - it's quick and easy. Just visit https://www.tsawaittimes.com/myaccount. If you already have an account, you'll need to sign in to either get your key or to generate one.  Please Note: If you do not generate an API key, the plugin will return dummy data. Dummy data can be useful for making sure that the plugin works, but you'll want to get an API key to return live data.

Next, add your API Key to WordPress. Once you have your API key, go back to your WordPress Administration screen and click on the Settings option. From there, look for the "TSA Wait Times" link.

Enter your API key into the appropriate box and click "Save Changes". Congratulations - you've just installed the TSA Wait Times WordPress plugin! Now it's time to start plugging it into your website!

== Frequently Asked Questions ==

= How do you calculate the estimated wait times? =

We use a combination of data sources including the TSA, FAA, traveler contributions, and our own internal data including a custom spider. The government data is often incomplete so we use additional custom logic to help fill in some of the blanks. Combining historical along with recent data points helps form the basis of the estimates.

= Are the estimated wait times accurate? =

All wait times are estimates and may not be reflective of the actual experience that a traveler may have at any given moment. Wait times are a combination of historical, user-submitted data, and additional custom logic. The TSA itself states on their website "Wait times are based on crowdsourced data provided by passengers and we cannot ensure their accuracy. Prepare for your trip by checking the histogram, which is based on historical data and can estimate how busy your airport is likely to be on your selected day and time of travel." We use a combination of factors to help determine the estimated wait time.

= Is this website owned by the TSA? =

No. This website is not owned or affiliated with the TSA. We are an independent software company (TayTech LLC) located in the state of Wisconsin in the United States.

= How much does it cost? =

Please check the TSAWaitTimes.com website for current pricing.

= What is an API key and why do I need one? =

An API key is a unique string assigned to you that lets the WordPress plugin talk to TSAWaitTimes.com. It lets us know who is asking for information, what info they want, and how we should return it. Creating an API key can be done from the TSAWaitTimes.com website.  If you do not have an API key, the WordPress plugin will return cached, dummy data which can be useful for testing but not so useful in a production environment.

== Screenshots ==
1. Sample output for current wait times at LAX

== Changelog ==

= 1.5 =
* Updated for latest version of WP. Updated TSA Wait Times branding.

= 1.4 =
* Moved CSS to local and removed remote css

= 1.3 =
* Compatibility check with WP v 5.9.3 and removed airport name in the current experience section

= 1.2 =
* Compatibility check with WP v 5.7

= 1.1 =
* CSS update

= 1.0 =
* Initial version and commit

== Upgrade Notice ==

= 1.0 =
Initial version and commit
