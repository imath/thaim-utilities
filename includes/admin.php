<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Administration !
 * 
 * This will be in a new section of BuddyPress settings
 * 
 * @package Thaim Utilities
 * @subpackage Component
 * @since   1.0.0
 */
class Thaim_Utilities_Admin {

	/**
	 * Constructor method
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Start !
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	public static function start() {
		if( ! is_admin() )
			return;

		$thaim_utilities = thaim_utilities();

		if( empty( $thaim_utilities->admin ) ) {
			$thaim_utilities->admin = new self;
		}

		return $thaim_utilities->admin;
	}

	/**
	 * Hooking BuddyPress settings to include ours
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	private function setup_hooks() {
		add_action( 'bp_register_admin_settings', array( $this, 'register_settings' ) );
	}

	/**
	 * The plugin's settings section
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	public function register_settings() {

		add_settings_section(
	    	'thaim_utilities_section',
	    	__( 'Thaim Utilities Settings',  'thaim-utilities' ),
	    	array( $this, 'settings_callback_section' ),
	    	'buddypress'
	    );

	    $settings = apply_filters( 'thaim_utilities_settings_fields', array( 
	    	array(
	    		'name'     => 'thaim_link_wordpress_org',
	    		'title'    => __( 'WordPress.org account', 'thaim-utilities' ),
	    		'display'  => array( $this, 'link_wp_org_callback' ),
	    		'sanitize' => 'sanitize_text_field',
	    	),
	    	array(
	    		'name'     => 'thaim_perpage_wordpress_org',
	    		'title'    => __( 'Number of WordPress plugins to display', 'thaim-utilities' ),
	    		'display'  => array( $this, 'perpage_wp_org_callback' ),
	    		'sanitize' => 'absint',
	    	),
	    	array(
	    		'name'     => 'thaim_list_github_repos',
	    		'title'    => __( 'List your github repos', 'thaim-utilities' ),
	    		'display'  => array( $this, 'list_github_repos_callback' ),
	    		'sanitize' => 'sanitize_text_field',
	    	),
	    ) );

		foreach( $settings as $setting ) {
			add_settings_field(
				$setting['name'],
				$setting['title'],
				$setting['display'],
				'buddypress',
				'thaim_utilities_section'
			);

			register_setting(
				'buddypress',
				$setting['name'],
				$setting['sanitize']
			);
		}
	}

	/**
	 * This is the display function for your section's description
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	function settings_callback_section() {
	    ?>
	    <p class="description"><?php _e( 'Define the Utilities to use.', 'thaim-utilities' );?></p>
	    <?php
	}
	 
	/**
	 * This is the display function for WordPress account
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	function link_wp_org_callback() {
		$wp_link_option_value = bp_get_option( 'thaim_link_wordpress_org', '' );
		?>
		<input id="thaim_link_wordpress_org" name="thaim_link_wordpress_org" type="text" value="<?php echo esc_attr( $wp_link_option_value ); ?>" />
		<label for="thaim_link_wordpress_org"><?php esc_html_e( 'WordPress.org username', 'thaim-utilities' ); ?></label>
		<?php
	}

	/**
	 * This is the display function for WordPress pagination
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	function perpage_wp_org_callback() {
		$wp_perpage_option_value = bp_get_option( 'thaim_perpage_wordpress_org', 20 );
		?>
		<input id="thaim_perpage_wordpress_org" name="thaim_perpage_wordpress_org" type="number" value="<?php echo esc_attr( $wp_perpage_option_value ); ?>" />
		<p class="description"><?php _e( '(up to 20)', 'thaim-utilities' ); ?></p>
		<?php
	}

	/**
	 * This is the display function for Github account
	 * 
	 * @package Thaim Utilities
	 * @subpackage Admin
	 * @since   1.0.0
	 */
	function list_github_repos_callback() {
		$github_option_value = bp_get_option( 'thaim_list_github_repos', '' );
		?>
		<input id="thaim_list_github_repos" name="thaim_list_github_repos" type="text" value="<?php echo esc_attr( $github_option_value ); ?>" />
		<label for="thaim_list_github_repos"><?php esc_html_e( 'Github.com username', 'thaim-utilities' ); ?></label>
		<?php
	}

}
add_action( 'bp_init', array( 'Thaim_Utilities_Admin', 'start' ) );
