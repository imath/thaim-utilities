<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template !
 * 
 * @package Thaim Utilities
 * @subpackage Template
 * @since   1.0.0
 */

class Thaim_Utilities_WP_Org_Api_Template {
	var $current_plugin = -1;
	var $plugin_count;
	var $plugins;
	var $plugin;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_plugin_count;

	function __construct( $args = '', $action = 'query_plugins', $page_arg = 'spage' ) {
		
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		
		$defaults = array( 'page' => 1, 
					       'per_page' => 20, 
					       'author' => 'imath', 
					       'fields' => array( 'description' => true, 
										  'sections' => true, 
										  'tested' => true,
										  'requires' => true, 
										  'rating' => true, 
										  'downloaded' => true, 
										  'downloadlink' => true, 
										  'last_updated' => true, 
										  'homepage' => true, 
										  'tags' => true ) 
						);	
						
		$r = wp_parse_args( $args, $defaults );
		
		$this->plugins = plugins_api( $action, $r );
		
		$this->plugins = $this->plugins->plugins;
		
		$this->plugin_count = $this->total_plugin_count = count( $this->plugins );
		
		if ( (int) $this->total_plugin_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( $page_arg, '%#%' ),
				'format'    => '',
				'total'     => ceil( (int) $this->total_plugin_count / (int) $this->pag_num ),
				'current'   => (int) $this->pag_page,
				'prev_text' => _x( '&larr;', 'Plugins pagination previous text', 'thaim-utilities' ),
				'next_text' => _x( '&rarr;', 'Plugins pagination next text', 'thaim-utilities' ),
				'mid_size'   => 1
			) );
		}
		
	}

	function has_plugins() {
		if ( $this->plugin_count )
			return true;

		return false;
	}

	function next_plugin() {
		$this->current_plugin++;
		$this->plugin = $this->plugins[$this->current_plugin];

		return $this->plugin;
	}

	function rewind_plugins() {
		$this->current_plugin = -1;
		if ( $this->plugin_count > 0 ) {
			$this->plugin = $this->plugins[0];
		}
	}

	function plugins() {
		if ( $this->current_plugin + 1 < $this->plugin_count ) {
			return true;
		} else if ( $this->current_plugin + 1 == $this->plugin_count ) {
			$this->rewind_plugins();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_plugin() {

		$this->in_the_loop = true;
		$this->plugin      = $this->next_plugin();

		// loop has just started
		if ( 0 == $this->current_plugin )
			do_action( 'plugin_loop_start' );
	}
}

function thaim_utitilities_has_plugins( $args = '' ) {
	global $plugins_template;
	
	$author       = bp_get_option( 'thaim_link_wordpress_org', 'imath' );
	$page         = 1;
	$per_page     = bp_get_option( 'thaim_perpage_wordpress_org', 20 );
	
	$defaults = array( 'page' => $page, 
					   'per_page' => $per_page, 
					   'author' => $author, 
					   'fields' => array( 'description' => false, 
					                      'sections' => false, 
					                      'tested' => false ,
					                      'requires' => true, 
					                      'rating' => true, 
					                      'downloaded' => true, 
					                      'downloadlink' => true, 
					                      'last_updated' => true, 
					                      'homepage' => false, 
					                      'tags' => true ) 
				     );
	
	$r = wp_parse_args( $args, $defaults );
	
	$plugins_template = new Thaim_Utilities_WP_Org_Api_Template( $r );
	
	return apply_filters( 'thaim_utitilities_has_plugins', $plugins_template->has_plugins(), $plugins_template );
}

function thaim_utitilities_the_plugin() {
	global $plugins_template;
	return $plugins_template->the_plugin();
}

function thaim_utitilities_plugins() {
	global $plugins_template;
	return $plugins_template->plugins();
}

function thaim_utitilities_plugin_name() {
	echo thaim_utitilities_get_plugin_name();
}

	function thaim_utitilities_get_plugin_name() {
		global $plugins_template;
		
		return apply_filters('thaim_utitilities_get_plugin_name', esc_html( $plugins_template->plugin->name ) );
	}

function thaim_utitilities_plugin_slug() {
	echo thaim_utitilities_get_plugin_slug();
}

	function thaim_utitilities_get_plugin_slug() {
		global $plugins_template;

		return apply_filters('thaim_utitilities_get_plugin_slug', esc_html( $plugins_template->plugin->slug ) );
	}
	
function thaim_utitilities_plugin_version() {
	echo thaim_utitilities_get_plugin_version();
}

	function thaim_utitilities_get_plugin_version() {
		global $plugins_template;

		return apply_filters('thaim_utitilities_get_plugin_version', $plugins_template->plugin->version );
	}	
	
function thaim_utitilities_plugin_requires() {
	echo thaim_utitilities_get_plugin_requires();
}

	function thaim_utitilities_get_plugin_requires() {
		global $plugins_template;

		return apply_filters('thaim_utitilities_get_plugin_requires', $plugins_template->plugin->requires );
	}

function thaim_utitilities_plugin_last_active() {
	global $plugins_template;

	if ( empty( $plugins_template->plugin->last_updated ) ) {
		return __( 'not yet active', 'thaim-utilities' );
	} else {
		return apply_filters( 'thaim_utitilities_plugin_last_active', bp_core_time_since( $plugins_template->plugin->last_updated . ' 00:00:00' ) );
	}
}
	
function thaim_utitilities_plugin_rating() {
	echo thaim_utitilities_get_plugin_rating();
}

	function thaim_utitilities_get_plugin_rating() {
		global $plugins_template;
		
		$ratings = $plugins_template->plugin->rating;
		$num_ratings = $plugins_template->plugin->num_ratings;
		
		$ratings = ( $ratings / 100 ) * 5;
		$rating = $ratings;
		
		$output = '<div class="thaim-rating-container">' . sprintf( __( 'Average of %s stars out of %s votes', 'thaim-utilities'), esc_html( $ratings ), esc_html( $num_ratings ) );
		$output .= '<ul class="thaim-rating">';
		
		for ( $i = 1 ; $i <= 5; $i++ ){
			
			if( $rating >= 1 )
				$icon = '&#xe08f;';
			elseif( $rating > 0 )
				$icon = '&#xe090;';
			else
				$icon = '&#xe08a;';
			
			$output .= '<li><span aria-hidden="true" data-icon="'.$icon.'"></span></li>';
			
			$rating -= 1; 
			
		}
		
		$output .= '</ul></div>';

		return apply_filters('thaim_utitilities_get_plugin_rating', $output );
	}
	
function thaim_utitilities_plugin_type() {
	global $plugins_template;
	
	$tags = $plugins_template->plugin->tags;

	if ( empty( $tags['buddypress'] ) && strpos( strtolower( $plugins_template->plugin->short_description ), 'buddypress' ) !== false ) {
		$tags['buddypress'] = 'buddypress';
	}
	
	if ( in_array( 'buddypress', array_map( 'strtolower', $tags ) ) )
		$output = '<div aria-hidden="true" data-icon="&#xe000;" class="plugin-type buddypress"></div>';
	else
		$output = '<div aria-hidden="true" data-icon="&#xe104;" class="plugin-type wordpress"></div>';

	echo apply_filters('thaim_utitilities_plugin_type', $output );
}

function thaim_utitilities_plugin_description() {
	echo thaim_utitilities_get_plugin_description();
}

	function thaim_utitilities_get_plugin_description() {
		global $plugins_template;
		
		$output = '<p class="thaim-short-desc">'. esc_html( $plugins_template->plugin->short_description ) .'</p>';
		
		$output .= thaim_utitilities_get_plugin_rating();
		
		return apply_filters('thaim_utitilities_get_plugin_description', $output);
	}

function thaim_utilities_display_version() {
	echo thaim_utilities_get_display_version();
}

	function thaim_utilities_get_display_version() {
		$output = '<p class="latest_version">' . sprintf( __( 'Latest version: %s', 'thaim-utilities' ), '<span>' . esc_html( thaim_utitilities_get_plugin_version() ) .'</span>' ) .'</p>';

		return apply_filters( 'thaim_utilities_get_display_version', $output );
	}

function thaim_utilities_display_required() {
	echo thaim_utilities_get_display_required();
}
	function thaim_utilities_get_display_required() {
		$output = '<p class="latest_version">' . sprintf( __( 'Requires: %s', 'thaim-utilities' ), '<span>' . esc_html( thaim_utitilities_get_plugin_requires() ) . '</span>' ) .'</p>';

		return apply_filters( 'thaim_utilities_get_display_required', $output );
	}

function thaim_utilities_downloaded() {
	echo thaim_utilities_get_downloaded();
}
	function thaim_utilities_get_downloaded() {
		global $plugins_template;

		$dowloaded = '<p class="downloaded">' . sprintf( __( 'Downloaded %s times', 'thaim-utilities' ), '<span>' . absint( $plugins_template->plugin->downloaded ) . '</span>' ) . '</p>';

		return apply_filters( 'thaim_utilities_get_downloaded', $dowloaded );
	}

function thaim_utitilities_plugin_download_link() {
	echo thaim_utitilities_get_plugin_download_link();
}

	function thaim_utitilities_get_plugin_download_link() {
		global $plugins_template;
		
		$download_link = sprintf( __('<a href="%s" title="Download plugin" class="button">Download</a>', 'thaim-utilities'), esc_url( $plugins_template->plugin->download_link ) );
		
		return apply_filters('thaim_utitilities_get_plugin_download_link', $download_link );
	}
	
function thaim_utitilities_plugin_info_link() {
	echo thaim_utitilities_get_plugin_info_link();
}

	function thaim_utitilities_get_plugin_info_link() {

		$info_link = trailingslashit( 'http://wordpress.org/plugins/' . thaim_utitilities_get_plugin_slug() );

		return apply_filters('thaim_utitilities_get_plugin_info_link', $info_link );
	}

function thaim_utitilities_plugin_tag_link() {
	echo thaim_utitilities_get_plugin_tag_link();
}

	function thaim_utitilities_get_plugin_tag_link() {

		$tag_link = sprintf( __('<a href="%s" title="View Posts about the plugin" class="button">Posts</a>', 'thaim-utilities'), esc_url( site_url('tag') .'/'. thaim_utitilities_get_plugin_slug() ) );

		return apply_filters('thaim_utitilities_get_plugin_info_link', $tag_link, thaim_utitilities_get_plugin_slug() );
	}

class Thaim_Utilities_Github_Api_Template {
	var $current_git = -1;
	var $git_count;
	var $gits;
	var $git;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_git_count;

	function __construct() {
		
		// Defaults to me !
		$this->git_user = bp_get_option( 'thaim_list_github_repos', 'imath' );

		if ( empty( $this->git_user ) ) {
			$this->gits = array();
			$this->git_count = $this->total_git_count = 0;
		} else {
			$gits = array();
			
			$get_repos = new Thaim_Utilities_Github_API( $this->git_user );

			if ( ! empty( $get_repos->repos ) )
				$gits = $get_repos->repos;
			
			$this->gits = $gits;
			
			$this->git_count = $this->total_git_count = count( $this->gits );
		}
	}

	function has_gits() {
		if ( $this->git_count )
			return true;

		return false;
	}

	function next_git() {
		$this->current_git++;
		$this->git = $this->gits[$this->current_git];

		return $this->git;
	}

	function rewind_gits() {
		$this->current_git = -1;
		if ( $this->git_count > 0 ) {
			$this->git = $this->gits[0];
		}
	}

	function gits() {
		if ( $this->current_git + 1 < $this->git_count ) {
			return true;
		} else if ( $this->current_git + 1 == $this->git_count ) {
			$this->rewind_gits();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_git() {

		$this->in_the_loop = true;
		$this->git         = $this->next_git();

		// loop has just started
		if ( 0 == $this->current_git )
			do_action( 'plugin_loop_start' );
	}
}

function thaim_utitilities_has_gits() {
	global $gits_template;
	
	$gits_template = new Thaim_Utilities_Github_Api_Template();
	
	return apply_filters( 'thaim_utitilities_has_gits', $gits_template->has_gits(), $gits_template );
}

function thaim_utitilities_the_git() {
	global $gits_template;
	return $gits_template->the_git();
}

function thaim_utitilities_gits() {
	global $gits_template;
	return $gits_template->gits();
}

function thaim_utitilities_git_link() {
	echo thaim_utitilities_get_git_link();
}

	function thaim_utitilities_get_git_link() {
		global $gits_template;

		return apply_filters( 'thaim_utitilities_get_git_link', esc_url( $gits_template->git['url'] ) );
	}

function thaim_utitilities_git_icon() {
	echo thaim_utitilities_get_git_icon();
}

	function thaim_utitilities_get_git_icon() {
		global $gits_template;

		return '<div aria-hidden="true" data-icon="&#xe0fd;" class="plugin-type github"></div>'; 
	}

function thaim_utitilities_git_name() {
	echo thaim_utitilities_get_git_name();
}

	function thaim_utitilities_get_git_name() {
		global $gits_template;

		return apply_filters( 'thaim_utitilities_get_git_name', esc_html( $gits_template->git['name'] ) );
	}


function thaim_utitilities_get_git_last_active() {
	global $gits_template;

	if ( empty( $gits_template->git['last_update'] ) ) {
		return __( 'not yet active', 'thaim-utilities' );
	} else {
		$last_update = trim( str_replace( array( 'T', 'Z'), ' ', $gits_template->git['last_update'] ) );
		return apply_filters( 'thaim_utitilities_get_git_last_active', bp_core_time_since( $last_update ) );
	}
}

function thaim_utitilities_git_description() {
	echo thaim_utitilities_get_git_description();
}

	function thaim_utitilities_get_git_description() {
		global $gits_template;

		$output = '<p class="thaim-short-desc">' . esc_html( $gits_template->git['description'] ) . '</p>';

		return apply_filters( 'thaim_utitilities_get_git_description', $output );
	}

function thaim_utitilities_git_download_link() {
	echo thaim_utitilities_get_git_download_link();
}

	function thaim_utitilities_get_git_download_link() {
		global $gits_template;

		$download_link = sprintf( __( '<a href="%s" title="Download Git" class="button">Download</a>', 'thaim-utilities'), esc_url( $gits_template->git['download'] ) );
		return apply_filters( 'thaim_utitilities_get_git_download_link', $download_link );
	}

function thaim_utilities_display_stargazers() {
	echo thaim_utilities_get_display_stargazers();
}

	function thaim_utilities_get_display_stargazers() {
		global $gits_template;

		$stars = absint( $gits_template->git['stargazers'] );

		if( $stars >= 1 )
			$icon = '&#xe08f;';
		else
			$icon = '&#xe08a;';
			
		$output = '<p class="downloaded"><span aria-hidden="true" data-icon="' .$icon. '" class="git-icons"></span> <span>' . $stars . '</span></p>';

		return apply_filters( 'thaim_utilities_get_display_stargazers', $output );
	}

function thaim_utilities_display_watchers() {
	echo thaim_utilities_get_display_watchers();
}

	function thaim_utilities_get_display_watchers() {
		global $gits_template;

		$watchers = absint( $gits_template->git['watchers'] );
		
		if( $watchers >= 1 )
			$icon = '&#xe077;';
		else
			$icon = '&#xe07a;';
			
		$output = '<p class="downloaded"><span aria-hidden="true" data-icon="' .$icon. '" class="git-icons"></span> <span>' . $watchers . '</span></p>';

		return apply_filters( 'thaim_utilities_get_display_watchers', $output );
	}

function thaim_utilities_activity_view_activity() {
	echo thaim_utilities_get_activity_view_activity();
}

	function thaim_utilities_get_activity_view_activity() {
		if ( ! bp_is_active( 'activity' ) )
			return false;

		global $activities_template;

		$time_since = apply_filters_ref_array( 'bp_activity_time_since', array( '<span class="time-since">' . bp_core_time_since( $activities_template->activity->date_recorded ) . '</span>', &$activities_template->activity ) );

		$link = sprintf( '<a href="%1$s" class="permactivity view activity-time-since" title="%2$s">%3$s</a>', bp_activity_get_permalink( $activities_template->activity->id, $activities_template->activity ), esc_attr__( 'View Activity', 'thaim-utilities' ), $time_since );
		
		return apply_filters( 'thaim_activity_view_activity', $link );
	}
