<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Functions !
 * 
 * @package Thaim Utilities
 * @subpackage Functions
 * @since   1.0.0
 */


function thaim_utilities_wp_org_link() {
	$wp_option = bp_get_option( 'thaim_link_wordpress_org', '' );
	
	if ( ! empty( $wp_option ) )
		return true;
	else
		return false;
}

function thaim_utilities_github_has_repos() {
	$git_option = bp_get_option( 'thaim_list_github_repos' );
	
	if ( ! empty( $git_option ) )
		return true;
		
	else
		return false;
}

/**
* Thaim_Github_API do not use oauth in this beta version
* but we'll do better in the future ;)
*/
class Thaim_Utilities_Github_API {
	protected $github_user;
	public $repos;
	
	// i'm the author of this theme so by default you'll get my repos ;)
	function __construct( $user = 'imath' ) {
		$this->github_user = $user;
		$this->get_repos();
	}
	
	function get_repos() {
		/* we use curl !*/
		add_filter( 'use_fsockopen_transport', '__return_false' );
		add_filter( 'use_fopen_transport', '__return_false' );
		add_filter( 'use_streams_transport', '__return_false' );
		add_filter( 'use_http_extension_transport', '__return_false' );
		
		// list all repos
		$url = 'https://api.github.com/users/' . $this->github_user . '/repos';
		
		$args = array(
			'method' => 'GET',
			'sslverify' => false
		);

		$github = wp_remote_request( $url, $args );
		
		// exit on error..
		if( empty( $github ) || ! empty( $github->errors ) ) {
			$this->repos = array();
		}
		
		$github_datas = $github['body'];

		$gits = json_decode( $github_datas );
		$sort_by = array();
		
		if ( is_array( $gits ) ) {

			foreach( $gits as $git ) {
				$this->repos[] = array( 
					'name'        => $git->name,
					'description' => $git->description,
					'url'         => $git->html_url,
					'download'    => trailingslashit( $git->html_url ) . 'archive/master.zip',
					'last_update' => $git->updated_at,
					'stargazers'  => $git->stargazers_count,
					'watchers'    => $git->watchers_count
				);

				$sort_by[] = $git->updated_at;
			}

			if ( ! empty( $sort_by ) )
				array_multisort( $sort_by, SORT_DESC, $this->repos );

		// Github is not reachable rate limit exceeded
		} else {
			$this->repos = array();
		}
		
	}
}
