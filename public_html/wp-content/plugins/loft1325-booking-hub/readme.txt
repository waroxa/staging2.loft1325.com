=== Loft1325 Booking Hub ===
Contributors: loft1325
Tags: bookings, butterflymx, admin
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 0.2.0

== Description ==
Mobile-first admin booking hub for Loft1325. Provides a dashboard, booking workflow, availability, inventory, and ButterflyMX key management.

== Installation ==
1. Upload the `loft1325-booking-hub` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Visit Loft1325 Hub in the WordPress admin.
4. Default hub password is `loft2026`. Update it in Paramètres.
5. Configure ButterflyMX credentials and default access_point_ids/device_ids.
6. Create a public page and add shortcode `[loft1325_booking_hub]` to expose the hub on the website.

== Notes ==
* TODO: Confirm ButterflyMX authorization header format.
* Use Seed 22 lofts button to populate inventory quickly.
* Use "Sync ButterflyMX" on Réservations to import existing keychains.

== Changelog ==
= 0.2.0 =
* Add public hub shortcode and ButterflyMX keychain sync workflow.

= 0.1.0 =
* Initial release with admin hub, custom tables, and ButterflyMX integration.
