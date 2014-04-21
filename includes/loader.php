<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Implementation of BP_Component
 * 
 * @package Thaim Utilities
 * @subpackage Component
 * @since   1.0.0
 */
class Thaim_Utilities_Component extends BP_Component {

	/**
	 * Constructor method
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function __construct() {
		$bp = buddypress();

		parent::start(
			'thaimutilities',
			__( 'Repositories', 'thaim-utilities' ),
			thaim_utilities()->includes_dir
		);

		$this->includes();

		$bp->active_components[$this->id] = '1';
	}

	/**
	 * Include needed files
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'template.php',
			'functions.php',
			'loops.php',
		);

		if ( bp_is_active( 'activity' ) ) {
			$includes = array_merge( $includes, array( 
				'activity.php',
				'widget.php'
			) );
		}

		if ( is_admin() || is_network_admin() ) {
			$includes[] = 'admin.php';
		}

		parent::includes( $includes );
	}		

	/**
	 * Set up component's globals
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Set up the $globals array to be passed along to parent::setup_globals()
		$args = array( 'slug' => 'repositories' );

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $args );
	}

	/**
	 * Set up your component's navigation.
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		// the main navigation
		$main_nav = array(
			'name' 		          => __( 'Repositories', 'thaim-utilities' ),
			'slug' 		          => 'repositories',
			'position' 	          => 80,
			'screen_function'     => array( $this, 'wordpress' ),
			'default_subnav_slug' => 'wordpress'
		);

		// Stop if there is no user displayed or logged in
		if ( ! is_user_logged_in() && ! bp_displayed_user_id() )
			return;

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$component_link = trailingslashit( $user_domain . 'repositories' );

		// Add WordPress subnav item
		$sub_nav[] = array(
			'name'            =>  __( 'WordPress', 'thaim-utilities' ),
			'slug'            => 'wordpress',
			'parent_url'      => $component_link,
			'parent_slug'     => 'repositories',
			'screen_function' => array( $this, 'wordpress' ),
			'position'        => 10
		);

		// Add Github subnav item
		$sub_nav[] = array(
			'name'            =>  __( 'Github', 'thaim-utilities' ),
			'slug'            => 'github',
			'parent_url'      => $component_link,
			'parent_slug'     => 'repositories',
			'screen_function' => array( $this, 'github' ),
			'position'        => 20
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain   = bp_loggedin_user_domain();
			$component_link = trailingslashit( $user_domain . 'repositories' );

			// Add the main sub menu
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Repositories', 'thaim-utilities' ),
				'href'   => trailingslashit( $component_link )
			);

			// WordPress
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-wordpress',
				'title'  => __( 'WordPress', 'thaim-utilities' ),
				'href'   => trailingslashit( $component_link . 'wordpress' )
			);

			// Github
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-github',
				'title'  => __( 'Github', 'thaim-utilities' ),
				'href'   => trailingslashit( $component_link . 'github' )
			);

		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Ask BuddyPress for the WordPress plugins template
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function wordpress() {
		$this->screen = 'wordpress';

		add_action( 'bp_template_content', array( $this, 'screen_content_wordpress' ) );

		bp_core_load_template( 'members/single/plugins' );
	}

	/**
	 * Ask BuddyPress for the Github repos template
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function github() {
		$this->screen = 'github';

		add_action( 'bp_template_content', array( $this, 'screen_content_github' ) );

		bp_core_load_template( 'members/single/plugins' );
	}

	/**
	 * Display WordPress plugins screen
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */ 
	public function screen_content_wordpress() {
		if ( thaim_utilities_wp_org_link() ) {
			thaim_utilities_wordpress_loop();
		} else {
			?>
			<div id="message" class="info"><p><?php esc_html_e( 'Please define your WordPress.org settings', 'thaim-utilities' ); ?></p></div>
			<?php
		}
		
	}

	/**
	 * Display Github repos screens
	 * 
	 * @package Thaim Utilities
	 * @subpackage Component
	 * @since   1.0.0
	 */
	public function screen_content_github() {
		if ( thaim_utilities_github_has_repos() ) {
			thaim_utilities_github_loop();
		} else {
			?>
			<div id="message" class="info"><p><?php esc_html_e( 'Please define your Github.com settings', 'thaim-utilities' ); ?></p></div>
			<?php
		}
	}
}

/**
 * Loads the component into the $bp global
 * 
 * @package Thaim Utilities
 * @subpackage Component
 * @since   1.0.0
 */
function thaim_utilities_component() {
	buddypress()->thaimutilities = new Thaim_Utilities_Component;
}
