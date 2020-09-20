=== Manually Reduce Stock for WooCommerce ===
Contributors: tamarazuk
Donate link: https://paypal.me/tamarazuk
Tags: reduce stock, woocommerce
Requires at least: 4.4
Tested up to: 5.5.1
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin adds a "Reduce Stock" button to an order screen to allow store owners to manually reduce stock for any items that haven't had stock reduced already.

== Description ==

By default, WooCommerce reduce the stock of an order's associated products when the order's status is updated. With this plugin you'll have manual control of reducing item stock straight from the order page in the WordPress admin.

Manually reducing the stock for an order is very useful for store owners who include free samples with orders, create manual orders in the admin, or change items for an order in general. The plugin uses WooCommerce functions to reduce stock which ensure that stock is reduced only for items that haven't had their stock reduced already.

== Installation ==

1. Be sure you're running WooCommerce 4.0.0+ and WordPress 4.4+ in your shop.
2. Upload the entire `manually-reduce-stock-for-woocommerce` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress.

== Screenshots ==

1. The new "Reduce Stock" button, which will immediately reduce the stock of all eligible items within the order and display an order note once the process is complete.

== Changelog ==

= 1.0.0 =
 * Initial Release
