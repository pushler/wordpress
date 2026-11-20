=== Pushler – Web Push for Klaviyo ===
Contributors: pushler
Tags: web push, push notifications, klaviyo, woocommerce, abandoned cart
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Install Pushler web push on your WordPress site in a minute. No files to upload.

== Description ==

[Pushler](https://pushler.com) sends web push notifications from the Klaviyo flows and segments you already use: cart and browse recovery, campaigns and more. This plugin installs Pushler on a WordPress site.

You need a Pushler account. There is a free plan.

What the plugin does:

* Adds the Pushler script to every page of your site.
* Serves the service worker from your own domain, at `/pushler-sw.js`. Browsers require this for push, and without the plugin you would upload the file by hand.
* Shows on one settings page whether the script and the service worker are working.

Your opt-in prompt, notifications and reports are managed in Pushler. Cart, browse and order events keep coming from Klaviyo, as they do today: your Klaviyo flows trigger the pushes.

It works with any WordPress site, including WooCommerce stores.

== Installation ==

1. Install and activate the plugin.
2. In Pushler, open Settings → Install and copy the script (or only the public key, which starts with `pk_`).
3. In WordPress, open Settings → Pushler, paste it into "Public key" and save.
4. Back in Pushler, on Settings → Install, keep the service worker path as `/pushler-sw.js` and select "Check now".

== Frequently Asked Questions ==

= Do I need a Pushler account? =

Yes. Create one at [pushler.com](https://pushler.com). The free plan sends 500 pushes a month.

= Do I need to upload a service worker file? =

No. WordPress answers `/pushler-sw.js` itself. If the Status section on the settings page says the address is not reaching WordPress, your web server is answering `.js` addresses on its own. Ask your host to pass that address to WordPress, or upload the file from Pushler's Install page to your site's root folder.

= Does it work if WordPress is installed in a folder? =

Pushler needs the service worker at the root of your domain. A site at `example.com/blog` is not supported yet.

= Does it slow my site down? =

The script loads after the page, with `async`, from Pushler's servers behind a content delivery network. Visitors who have not subscribed to push send nothing else.

= I use a caching or optimisation plugin. Anything to do? =

Usually not. If push stops working after you turn on "delay JavaScript" or "combine JavaScript", exclude `sdk/v1.js` and `pushler` from that feature.

== External services ==

This plugin connects your site to Pushler, a web push service. It cannot work without it.

* **What is loaded:** the Pushler script (`https://app.pushler.com/sdk/v1.js`) on every page of your site once you have saved a public key, and the service worker script (`https://app.pushler.com/sdk/sw.js`) in the browsers of visitors who subscribe.
* **What is sent, and when:**
  * On every page view, the script asks Pushler for your prompt settings. This request carries your public key and, as with any web request, the visitor's IP address and browser details.
  * When a visitor allows notifications: their browser's push subscription, browser, operating system, device type, language and time zone, and their Klaviyo identifier or email address if Klaviyo has set one in the browser.
  * For visitors who have subscribed: that they visited your site, at most once every 30 minutes.
* Visitors who have not subscribed to push send only the first request.

Pushler is provided by Pushler LLC: [terms of service](https://pushler.com/terms), [privacy policy](https://pushler.com/privacy).

== Changelog ==

= 1.0.0 =
* First release.
