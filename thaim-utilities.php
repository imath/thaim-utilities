<?php
/**
 * Plugin Name: Thaim Utilities
 * Plugin URI: https://github.com/imath/thaim-utilities
 * Description: A BuddyPress component to back up tweets and WordPress support forums in activities & to sync WordPress and github repos
 * Version: 1.0.0
 * Requires at least: 3.9
 * Tested up to: 3.9
 * Text Domain: thaim-utilities
 * Domain Path: /languages/
 * License:     GPLv2 or later
 * Author: imath
 * Author URI: http://imathi.eu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ThaimUtilities' ) ) :
/**
 * Main Class.
 *
 * @package Thaim Utilities
 * @since   1.0.0
 */
class ThaimUtilities {
	/**
	 * Instance of this class.
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Required BuddyPress version for your plugin..
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 *
	 * @var      string
	 */
	public static $required_bp_version = '2.0';

	/**
	 * BuddyPress config.
	 * 
	 * @package Thaim Utilities
	 *
	 * @var      array
	 */
	public static $bp_config = array();

	/**
	 * Initialize the plugin
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function start() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	private function setup_globals() {

		$this->version       = '1.0.0';
		$this->domain        = 'thaim-utilities';

		/** Paths ***********************************************/

		$this->file          = __FILE__;
		$this->basename      = plugin_basename( $this->file );

		// Define a global that we can use to construct file paths throughout the component
		$this->plugin_dir    = plugin_dir_path( $this->file );

		// Define a global that we can use to construct file paths starting from the includes directory
		$this->includes_dir  = trailingslashit( $this->plugin_dir . 'includes' );

		// Define a global that we can use to construct file paths starting from the includes directory
		$this->lang_dir      = trailingslashit( $this->plugin_dir . 'languages' );


		$this->plugin_url    = plugin_dir_url( $this->file );
		$this->includes_url  = trailingslashit( $this->plugin_url . 'includes' );

		// Define a global that we can use to construct url to the javascript scripts needed by the component
		$this->plugin_js     = trailingslashit( $this->includes_url . 'js' );

		// Define a global that we can use to construct url to the css needed by the component
		$this->plugin_css    = trailingslashit( $this->includes_url . 'css' );
	}

	/**
	 * Include the component's loader.
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	private function includes() {
		if ( self::bail() )
			return;

		require( $this->includes_dir . 'loader.php' );
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	private function setup_hooks() {

		if ( ! self::bail() ) {
			// Load the component
			add_action( 'bp_loaded', 'thaim_utilities_component', 10 );

			// loads the languages..
			add_action( 'bp_init', array( $this, 'load_textdomain' ), 5 );

		} else {
			// Display a warning message in network admin or admin
			add_action( self::$bp_config['network_active'] ? 'network_admin_notices' : 'admin_notices', array( $this, 'warning' ) );
		}
		
	}

	/**
	 * Display a warning message to admin
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	public function warning() {
		$warnings = array();

		if( ! self::version_check() ) {
			$warnings[] = sprintf( __( 'Thaim Utilities requires at least version %s of BuddyPress.', 'thaim-utilities' ), self::$required_bp_version );
		}

		if ( ! empty( self::$bp_config ) ) {
			$config = self::$bp_config;
		} else {
			$config = self::config_check();
		}
		
		if ( ! bp_core_do_network_admin() && ! $config['blog_status'] ) {
			$warnings[] = __( 'Thaim Utilities requires to be activated on the blog where BuddyPress is activated.', 'thaim-utilities' );
		}

		if ( bp_core_do_network_admin() && ! $config['network_status'] ) {
			$warnings[] = __( 'Thaim Utilities and BuddyPress need to share the same network configuration.', 'thaim-utilities' );
		}

		if ( ! empty( $warnings ) ) :
		?>
		<div id="message" class="error">
			<?php foreach ( $warnings as $warning ) : ?>
				<p><?php echo esc_html( $warning ) ; ?></p>
			<?php endforeach ; ?>
		</div>
		<?php
		endif;
	}

	/** Utilities *****************************************************************************/

	/**
	 * Checks BuddyPress version
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	public static function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$required_bp_version, '>=' );
	}

	/**
	 * Checks if your plugin's config is similar to BuddyPress
	 * 
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	public static function config_check() {
		/**
		 * blog_status    : true if your plugin is activated on the same blog
		 * network_active : true when your plugin is activated on the network
		 * network_status : BuddyPress & your plugin share the same network status
		 */
		self::$bp_config = array(
			'blog_status'    => false, 
			'network_active' => false, 
			'network_status' => true 
		);

		if ( get_current_blog_id() == bp_get_root_blog_id() ) {
			self::$bp_config['blog_status'] = true;
		}
		
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

		// No Network plugins
		if ( empty( $network_plugins ) )
			return self::$bp_config;

		// Looking for BuddyPress and your plugin
		$check = array( buddypress()->basename, $this->basename );

		// Are they active on the network ?
		$network_active = array_diff( $check, array_keys( $network_plugins ) );
		
		// If result is 1, your plugin is network activated
		// and not BuddyPress or vice & versa. Config is not ok
		if ( count( $network_active ) == 1 )
			self::$bp_config['network_status'] = false;
		
		// We need to know if the plugin is network activated to choose the right
		// notice ( admin or network_admin ) to display the warning message.
		self::$bp_config['network_active'] = isset( $network_plugins[ $this->basename ] );

		return self::$bp_config;
	}

	/**
	 * Bail if BuddyPress config is different than this plugin
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 */
	public static function bail() {
		$retval = false;

		$config = self::config_check();

		if ( ! self::version_check() || ! $config['blog_status'] || ! $config['network_status'] )
			$retval = true;

		return $retval;
	}

	/**
	 * Loads the translation files
	 *
	 * @package Thaim Utilities
	 * @since   1.0.0
	 * 
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/thaim-utilities/' . $mofile;

		// Look in global /wp-content/languages/thaim-utilities folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/thaim-utilities/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}
}

endif;

/**
 * BuddyPress is loaded and initialized, let's start !
 * 
 * @package Thaim Utilities
 * @since   1.0.0
 */
function thaim_utilities() {
	return ThaimUtilities::start();
}
add_action( 'bp_include', 'thaim_utilities' );
