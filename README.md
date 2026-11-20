# Pushler for WordPress

The WordPress plugin for [Pushler](https://pushler.com), web push notifications for Klaviyo.

It adds the Pushler script to every page and serves the service worker from the site's own domain at `/pushler-sw.js`, so nothing has to be uploaded. That is all it does: cart, browse and order events reach Pushler through Klaviyo, not through this plugin.

`readme.txt` is the WordPress.org listing and the user documentation.

## Layout

| File | What it does |
| --- | --- |
| `pushler.php` | Plugin header, constants, loads the rest |
| `includes/functions.php` | App address, public key, worker file contents |
| `includes/class-pushler-service-worker.php` | Answers `/pushler-sw.js` |
| `includes/class-pushler-frontend.php` | The script tag, with the site's key |
| `includes/class-pushler-settings.php` | Settings → Pushler: the key and a status check |
| `uninstall.php` | Deletes the saved key |

## Development

Point the plugin at another Pushler address in `wp-config.php`:

```php
define( 'PUSHLER_APP_URL', 'https://staging.example.com' );
```

Or use the `pushler_app_url` filter.

## Releasing

1. Change the version in `pushler.php` (header and `PUSHLER_VERSION`) and `Stable tag` in `readme.txt`, and add a changelog entry.
2. Build the zip: `git archive --format=zip --prefix=pushler/ -o pushler.zip HEAD`. `.gitattributes` leaves the development files out.

## License

GPL-2.0-or-later. See `LICENSE`.
