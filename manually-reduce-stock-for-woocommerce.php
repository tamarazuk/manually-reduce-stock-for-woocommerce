<?php
/**
 * Plugin Name:       Manually Reduce Stock for WooCommerce
 * Plugin URI:        https://tamarazuk.com
 * Description:       This plugin adds a "Reduce Stock" button to an order screen to allow store owners to manually reduce stock for any items that haven't had stock reduced already.
 * Version:           1.0.0
 * Author:            Tamara Zuk
 * Author URI:        https://tamarazuk.com
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       manually-reduce-stock-for-woocommerce
 * Domain Path:       /i18n/languages/
 *
 * WC requires at least: 4.0.0
 * WC tested up to: 4.5.2
 */

defined( 'ABSPATH' ) or exit;

// check to ensure WooCommerce is installed and at a supported version
if ( ! Manually_Reduce_Stock_For_WooCommerce::is_plugin_active( 'woocommerce.php' ) || version_compare( get_option( 'woocommerce_db_version' ), Manually_Reduce_Stock_For_WooCommerce::MIN_WOOCOMMERCE_VERSION, '<' ) ) {
	add_action( 'admin_notices', array( 'Manually_Reduce_Stock_For_WooCommerce', 'render_outdated_wc_version_notice' ) );
	return;
}

// make sure we're loaded WooCommerce WC
add_action( 'plugins_loaded', 'plugin_manually_reduce_stock_for_woocommerce' );

/**
 * Class \Manually_Reduce_Stock_For_WooCommerce
 * Sets up the main plugin class.
 *
 * @since 1.0.0
 */

class Manually_Reduce_Stock_For_WooCommerce {


	/** plugin version number */
	const VERSION = '1.0.0';

	/** required WooCommerce version number */
	const MIN_WOOCOMMERCE_VERSION = '4.0.0';

	/** @var Manually_Reduce_Stock_For_WooCommerce single instance of this plugin */
	protected static $instance;


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'add_reduce_stock_button' ) );

		add_action( 'wp_ajax_manually_reduce_stock_for_woocommerce', array( $this, 'ajax_reduce_order_stock' ) );
	}


	/** Plugin methods ******************************************************/


	/**
	 * Enqueues scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();

		if ( $screen && 'shop_order' === $screen->id ) {

			wp_enqueue_script( 'manually_reduce_stock_for_woocommerce', $this->get_plugin_url() . '/assets/js/admin/scripts.min.js', array( 'jquery' ), Manually_Reduce_Stock_For_WooCommerce::VERSION, true );
		}
	}



	/**
	 * Adds the "Reduce Stock" action button on the order screen.
	 *
	 * @since 1.0.0
	 */
	public function add_reduce_stock_button() {

		?>
			<button type="button" class="button reduce-stock"><?php esc_html_e( 'Reduce Stock', 'manually-reduce-stock-for-woocommerce' ); ?></button>
		<?php
	}


	/**
	 * Reduces the stock for an order.
	 *
	 * @since 1.0.0
	 */
	public function ajax_reduce_order_stock() {

		check_ajax_referer( 'order-item', 'security' );

		if ( current_user_can( 'manage_woocommerce' ) ) {

			$response = array();

			try {
				$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
				$order    = wc_get_order( $order_id );

				if ( $order && $order instanceof \WC_Order ) {

					// reduce order stock
					wc_reduce_stock_levels( $order_id );

					if ( defined( 'WC_PLUGIN_FILE' ) ) {

						ob_start();

						$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );

						include dirname( WC_PLUGIN_FILE ) . '/includes/admin/meta-boxes/views/html-order-notes.php';

						$notes_html = ob_get_clean();

						$response = array( 'notes_html' => $notes_html );
					}

					wp_send_json_success( $response );

				} else {
					throw new Exception( __( 'Invalid order', 'woocommerce' ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}

		wp_send_json_error( null, 500 );
		exit;
	}


	/** Helper methods ******************************************************/


	/**
	 * Main plugin instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.0.0
	 * @see manually_reduce_stock_for_woocommerce()
	 * @return \Manually_Reduce_Stock_For_WooCommerce
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'manually-reduce-stock-for-woocommerce' ), 'Manually Reduce Stock for WooCommerce' ), '2.4.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'manually-reduce-stock-for-woocommerce' ), 'Manually Reduce Stock for WooCommerce' ), '2.4.0' );
	}



	/**
	 * Loads translations.
	 *
	 * @since 1.0.0
	 */
	public function load_translation() {
		load_plugin_textdomain( 'manually-reduce-stock-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Determines whether a plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name plugin name, as the plugin-filename.php
	 * @return boolean true if the named plugin is installed and active
	 */
	public static function is_plugin_active( $plugin_name ) {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		$plugin_filenames = array();

		foreach ( $active_plugins as $plugin ) {

			if ( false !== strpos( $plugin, '/' ) ) {

				// normal plugin name (plugin-dir/plugin-filename.php)
				list( , $filename ) = explode( '/', $plugin );

			} else {

				// no directory, just plugin file
				$filename = $plugin;
			}

			$plugin_filenames[] = $filename;
		}

		return in_array( $plugin_name, $plugin_filenames );
	}


	/**
	 * Renders a notice when WooCommerce version is outdated.
	 *
	 * @since 1.0.0
	 */
	public static function render_outdated_wc_version_notice() {

		$message = sprintf(
			/* translators: Placeholders: %1$s <strong>, %2$s - </strong>, %3$s - version number, %4$s + %6$s - <a> tags, %5$s - </a> */
			esc_html__( '%1$sManually Reduce Stock for WooCommerce is inactive.%2$s This plugin requires WooCommerce %3$s or newer. Please %4$supdate WooCommerce%5$s or %6$srun the WooCommerce database upgrade%5$s.', 'manually-reduce-stock-for-woocommerce' ),
			'<strong>',
			'</strong>',
			self::MIN_WOOCOMMERCE_VERSION,
			'<a href="' . admin_url( 'plugins.php' ) . '">',
			'</a>',
			'<a href="' . admin_url( 'plugins.php?do_update_woocommerce=true' ) . '">'
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}


	/**
	 * Checks if WooCommerce is greater than a specific version.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $version version number
	 * @return bool true if > version
	 */
	public static function is_wc_gte( $version ) {
		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, $version, '>=' );
	}


	/**
	 * Checks if WooCommerce is less than than a specific version.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $version version number
	 * @return bool true if < version
	 */
	public static function is_wc_lt( $version ) {
		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, $version, '<' );
	}


	/**
	 * Gets the plugin URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin URL
	 */
	public function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}


}


/**
 * Returns the One True Instance of Manually_Reduce_Stock_For_WooCommerce.
 *
 * @since 1.0.0
 * @return \Manually_Reduce_Stock_For_WooCommerce
 */
function plugin_manually_reduce_stock_for_woocommerce() {
	return Manually_Reduce_Stock_For_WooCommerce::instance();
}
