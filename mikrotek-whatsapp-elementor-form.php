<?php
/**
 * Plugin Name: Mikrotek WhatsApp Action for Elementor Form
 * Plugin URI: https://mikrotek.co.id
 * Description: Menambahkan Action After Submit ke WhatsApp pada widget Form Elementor Pro.
 * Version: 1.0.1
 * Author: Mikrotek
 * Author URI: https://mikrotek.co.id
 * Text Domain: mikrotek-wa-elementor
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Elementor tested up to: 3.24.0
 * Elementor Pro tested up to: 3.24.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'MIKROTEK_WA_ELEM_VERSION', '1.0.1' );
define( 'MIKROTEK_WA_ELEM_PATH', plugin_dir_path( __FILE__ ) );
define( 'MIKROTEK_WA_ELEM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Class Plugin
 */
final class Mikrotek_WA_Elementor_Plugin {

	/**
	 * Instance singleton
	 *
	 * @var Mikrotek_WA_Elementor_Plugin
	 */
	private static $instance = null;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Init plugin
	 */
	public function init() {
		// Register Action After Submit hook (Elementor Pro 3.5+)
		add_action( 'elementor_pro/forms/actions/register', [ $this, 'register_form_action' ] );

		// Fallback for older Elementor Pro versions
		add_action( 'elementor_pro/init', [ $this, 'register_form_action_legacy' ] );

		// Check if Elementor Pro exists after all plugins loaded
		add_action( 'admin_init', [ $this, 'check_elementor_pro_active' ] );

		// Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
	}

	/**
	 * Check if Elementor Pro active, show notice if not
	 */
	public function check_elementor_pro_active() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! did_action( 'elementor_pro/init' ) && ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor_pro' ] );
		}
	}

	/**
	 * Admin notice if Elementor Pro not active
	 */
	public function admin_notice_missing_elementor_pro() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Elementor Pro */
			esc_html__( '"%1$s" membutuhkan plugin "%2$s" aktif agar dapat digunakan.', 'mikrotek-wa-elementor' ),
			'<strong>' . esc_html__( 'Mikrotek WhatsApp Action for Elementor Form', 'mikrotek-wa-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor Pro', 'mikrotek-wa-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
	}

	/**
	 * Register WhatsApp Form Action (Elementor Pro 3.5+)
	 *
	 * @param \ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar
	 */
	public function register_form_action( $form_actions_registrar ) {
		require_once MIKROTEK_WA_ELEM_PATH . 'includes/class-whatsapp-action.php';

		$action = new \Mikrotek_Elementor_WhatsApp_Action();
		$form_actions_registrar->register( $action );
	}

	/**
	 * Legacy register for Elementor Pro < 3.5
	 */
	public function register_form_action_legacy() {
		if ( ! class_exists( '\ElementorPro\Plugin' ) ) {
			return;
		}

		$forms_module = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' );
		if ( $forms_module && method_exists( $forms_module, 'add_form_action' ) ) {
			require_once MIKROTEK_WA_ELEM_PATH . 'includes/class-whatsapp-action.php';
			$action = new \Mikrotek_Elementor_WhatsApp_Action();
			$forms_module->add_form_action( $action->get_name(), $action );
		}
	}

	/**
	 * Frontend assets for WhatsApp redirection handling
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'mikrotek-wa-elementor-frontend',
			MIKROTEK_WA_ELEM_URL . 'assets/js/frontend.js',
			[ 'jquery' ],
			MIKROTEK_WA_ELEM_VERSION,
			true
		);
	}
}

// Bootstrap
Mikrotek_WA_Elementor_Plugin::get_instance();
