<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Activities !
 * 
 * Let's backup tweets and support forums
 * 
 * @package Thaim Utilities
 * @subpackage Activity
 * @since   1.0.0
 */
class Thaim_Utilities_Activity {

	protected $user_id           = 0;
	protected $twitter_username  = '';
	protected $twitter_connexion = array();
	protected $latest_tweet      = 0;
	protected $wporg_username    = '';
	protected $wporg_support     = false;
	
	/**
	 * Constructor
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
	}

	/**
	 * Start !
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public static function start() {

		$thaim_utilities = thaim_utilities();

		if( empty( $thaim_utilities->activity ) ) {
			$thaim_utilities->activity = new self;
		}

		return $thaim_utilities->activity;
	}

	/**
	 * Set up usefull global variables
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function setup_globals() {
		// customize with your wordpress login.
		$this->user_id           = bp_core_get_userid( 'imath' );

		// Twitter settings
		$this->twitter_username  = bp_get_option( 'thaim_twitter_username', '' );
		$this->twitter_connexion = bp_get_option( 'thaim_twitter_connexion', array() );
		$this->latest_tweet      = bp_get_user_meta( $this->user_id, 'thaim_twitter_latest_tweet_id', true );

		// WordPress Support settings
		$this->wporg_support     = bp_get_option( 'thaim_support_wordpress_org', 0 );
		$this->wporg_username    = bp_get_option( 'thaim_link_wordpress_org', 0 );
		$this->latest_support    = bp_get_user_meta( $this->user_id, 'thaim_wporg_latest_support_id', true );
	}

	/**
	 * Actions & filters
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function setup_hooks() {
		// Adding activity settings
		add_filter( 'thaim_utilities_settings_fields', array( $this, 'register_settings' ), 10, 1 );

		// Defining cron tasks
		add_action( 'load-settings_page_bp-settings', array( $this, 'manage_cron' ) );

		// Hooking cron jobs
		add_action( 'thaim_twitter_cron_job',       array( $this, 'backup_twitter' ) );
		add_action( 'thaim_wporg_support_cron_job', array( $this, 'backup_wporg' )   );

		// Register Activity actions
		add_action( 'bp_register_activity_actions', array( $this, 'register_actions' ) );

		// Embed tweets on activity single items
		add_filter( 'bp_get_activity_content_body', array( $this, 'maybe_embed_tweet' ), 8, 2 );

		// Add activity filters
		add_action( 'bp_member_activity_filter_options', array( $this, 'maybe_add_activity_filters' ) );
	}

	/**
	 * Add specific activity settings to plugin's section
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function register_settings( $settings = array() ) {

	    $activity_settings = array( 
	    	array(
				'name'     => 'thaim_twitter_username',
				'title'    => __( 'Twitter Username', 'thaim-utilities' ),
				'display'  => array( $this, 'twitter_username_callback' ),
	    		'sanitize' => 'sanitize_text_field',
			),
			array(
				'name'     => 'thaim_twitter_connexion',
				'title'    => __( 'Twitter API credentials', 'thaim-utilities' ),
				'display'  => array( $this, 'twitter_connexion_callback' ),
				'sanitize' => array( $this, 'twitter_connexion_sanitize' ),
	    	),
			array(
				'name'     => 'thaim_support_wordpress_org',
				'title'    => __( 'Import WordPress.org Support replies', 'thaim-utilities' ),
				'display'  => array( $this, 'support_wp_org_callback' ),
				'sanitize' => 'absint',
	    	),
	    );

	    return array_merge( $settings, $activity_settings );
	}
	 
	/**
	 * Twitter account callback
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function twitter_username_callback() {
		?>
		<input id="thaim_twitter_username" name="thaim_twitter_username" type="text" value="<?php echo esc_attr( $this->twitter_username ); ?>" />
		<label for="thaim_twitter_username"><?php esc_html_e( 'Twitter username', 'thaim-utilities' ); ?></label>
		<?php
	}

	/**
	 * Twitter credentials callback
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function twitter_connexion_callback() {
		$credentials = implode( "\n", $this->twitter_connexion );
		$placeholder_array = array(
			__( 'API Key', 'thaim-utilities' ),
			__( 'API Secret', 'thaim-utilities' ),
			__( 'Access token', 'thaim-utilities' ),
			__( 'Access token secret', 'thaim-utilities' ),
		);
		$placehoder = implode( '&#x0a;&#x09;&#x09;&#x09;&#x0a;&#x09;&#x09;&#x09;', $placeholder_array );
		$description = implode( '<br/>', $placeholder_array );
		?>
		<textarea id="thaim_twitter_connexion" name="thaim_twitter_connexion" placeholder="<?php echo $placehoder;?>" cols="20" rows="4" style="resize:none"><?php echo $credentials;?></textarea>
		<p class="description">
			<?php esc_html_e( 'Fill these informations respecting the order with a line breack between each, empty to disable.', 'thaim-utilities' );?>
			<br/>
			<?php echo $description;?>
		</p>
		<?php
	}

	/**
	 * Should we backup WordPress support ?
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function support_wp_org_callback() {
		?>
		<input id="thaim_support_wordpress_org" name="thaim_support_wordpress_org" type="checkbox" value="1" <?php checked( $this->wporg_support ); ?> />
		<p class="description"><?php esc_html_e( 'The replies you made in your support forums will be saved as BuddyPress Activities', 'thaim-utilities' );?></p>
		<?php
	}

	/**
	 * Twitter credentials santize function
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function twitter_connexion_sanitize( $option = null ) {
		$twitter_connexion = array();

		if ( is_array( $option ) )
			return $twitter_connexion;

		$options = explode( "\n", $option );

		if ( empty( $options ) || ! is_array( $options ) || count( $options ) < 4 )
			return $twitter_connexion;

		$twitter_connexion = array(
			'api_key'             => sanitize_text_field( $options[0] ),
			'api_secret'          => sanitize_text_field( $options[1] ),
			'access_token'        => sanitize_text_field( $options[2] ),
			'access_token_secret' => sanitize_text_field( $options[3] ),
		);
		
		return $twitter_connexion;
	}

	/**
	 * Set the cron jobs
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function manage_cron() {
		if ( ! empty( $_REQUEST['updated'] ) ) {

			$cron_tasks = array(
				'twitter' => array( 
					'job'       => 'thaim_twitter_cron_job',
					'frequence' => 'hourly',
					'active'    => false,
				),
				'wporg'   => array(
					'job'       => 'thaim_wporg_support_cron_job',
					'frequence' => 'twicedaily',
					'active'    => false,
				),
			);

			if ( ! empty( $this->twitter_connexion ) )
				$cron_tasks['twitter']['active'] = true;

			if ( ! empty( $this->wporg_support ) )
				$cron_tasks['wporg']['active'] = true;

			foreach( $cron_tasks as $task ) {
				$timestamp = false;

				if ( ! empty( $task['active' ] ) ) {
					// Schedule jobs if needed
					if ( ! wp_next_scheduled( $task['job'] ) )
						wp_schedule_event( time(), $task['frequence'], $task['job'] );

				// Unschedule jobs if needed
				} else {
					$timestamp = wp_next_scheduled( $task['job'] );
					//unschedule custom action hook 
					if( ! empty( $timestamp ) )
						wp_unschedule_event( $timestamp, $task['job'] );
				}
			}
		}
	}

	/**
	 * My tweets are mine !
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function backup_twitter() {
		$this->get_tweets();
	}

	/**
	 * Twitter API
	 * 
	 * Many thanks to Abraham Williams (abraham@abrah.am) http://abrah.am
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function get_access_token() {
		require_once( 'twitteroauth/twitteroauth.php' );
	  	
	  	$connection = new TwitterOAuth(
	  		$this->twitter_connexion['api_key'], 
	  		$this->twitter_connexion['api_secret'], 
	  		$this->twitter_connexion['access_token'], 
	  		$this->twitter_connexion['access_token_secret']
	  	);

	    return $connection;
	}

	/**
	 * Format date/time
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function get_date( $timestamp = false ) {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring = get_option( 'timezone_string' );
		
		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
			$check_zone_info = false;
			if ( 0 == $current_offset )
				$tzstring = 'UTC+0';
			elseif ($current_offset < 0)
				$tzstring = 'UTC' . $current_offset;
			else
				$tzstring = 'UTC+' . $current_offset;
		}
		
		date_default_timezone_set( $tzstring );
		
		if( empty( $timestamp ) )
			$timestamp = time();
		
		return date_i18n( 'Y-m-d H:i:s', $timestamp );
	} 
	
	/**
	 * Allo Twitter give me my tweets !
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function get_tweets() {
		// Bail if what we need is not set!
		if ( empty( $this->twitter_connexion ) || empty( $this->twitter_username ) )
			return false;

		$latest_id = false;

		$params = array( 
			'screen_name' => $this->twitter_username,
			'exclude_replies' => true
		);

		if ( ! empty( $this->latest_tweet ) )
			$params['since_id'] = $this->latest_tweet;

		$connection = $this->get_access_token();
		$content = $connection->get( 'statuses/user_timeline' , $params );

		if ( empty( $content ) )
			return;

		$from_user_link = bp_core_get_userlink( $this->user_id );
		
		foreach( $content as $tweet ) {
			// init vars
			$retweeted = $action = $permalink = false;

			if ( ! empty( $tweet->retweeted_status ) ) {
				$retweeted = $tweet;
				$tweet = $tweet->retweeted_status;
				$retweeted_user = '<a href="https://twitter.com/'.$tweet->user->screen_name.'">' . $tweet->user->name .'</a>';
				$action  = sprintf( __( '%s retweeted %s', 'thaim-utilities' ), $from_user_link, $retweeted_user );
			} else {
				$action  = sprintf( __( '%s tweeted', 'thaim-utilities' ), $from_user_link );
			}

			$the_tweet = $tweet->text;
			$permalink = 'https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id_str;

			$created = ! empty( $retweeted ) ? strtotime( $retweeted->created_at ) : strtotime( $tweet->created_at );
			$tweet_id = ! empty( $retweeted ) ? $retweeted->id_str : $tweet->id_str;

			if ( empty( $latest_id ) || $latest_id < $tweet_id )
				$latest_id = $tweet_id;

			// hashtags
			if ( ! empty( $tweet->entities->hashtags ) ) {
				foreach( $tweet->entities->hashtags as $hashtag ) {
					$the_tweet = str_replace( 
						$hashtag->text,
						'<a href="https://twitter.com/search?q=%23'.$hashtag->text.'&src=hash">'.$hashtag->text.'</a>',
						$the_tweet
					);
				}
			}

			// mentions
			if ( ! empty( $tweet->entities->user_mentions ) ) {
				foreach( $tweet->entities->user_mentions as $mention ) {
					$the_tweet = str_replace( 
						$mention->screen_name, 
						'<a href="https://twitter.com/'.$mention->screen_name.'" title="'.$mention->name.'">'.$mention->screen_name.'</a>',
						$the_tweet
					);
				}
			}

			// urls
			if ( ! empty( $tweet->entities->urls ) ) {
				foreach( $tweet->entities->urls as $url ) {
					$the_tweet = str_replace( 
						$url->url, 
						'<a href="'.$url->url.'" title="'.$url->expanded_url.'">'.$url->display_url.'</a>',
						$the_tweet
					);
				}
			}

			// media
			if ( ! empty( $tweet->entities->media ) ) {
				foreach( $tweet->entities->media as $media ) {
					$the_tweet = str_replace( 
						$media->url, 
						'<a href="'.$media->url.'" title="'.$media->expanded_url.'">'.$media->display_url.'</a>',
						$the_tweet
					);
				}
			}
			
			$args = array(
				'action'            => $action,
				'type'              => 'twitter_tweet',
				'content'           => $the_tweet,
				'item_id'           => $tweet_id,
				'recorded_time'     => $this->get_date( $created ),
				'tweet_permalink'   => $permalink,
			);

			$this->publish_activity( $args );
		}

		/* this way, next time we will start from latest saved one !*/
		if ( ! empty( $latest_id ) )
			bp_update_user_meta( $this->user_id, 'thaim_twitter_latest_tweet_id', $latest_id );
	}

	/**
	 * Backup WordPress Support
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function backup_wporg() {
		$this->get_supports();
	}

	/**
	 * Hello WordPress support feed
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function get_supports() {
		// Bail if we don't have what we need
		if ( empty( $this->wporg_username ) || empty( $this->wporg_support ) )
			return false;

		$latest_id = false;
		$feed      = 'http://wordpress.org/support/rss/view/plugin-committer/' . $this->wporg_username;
		$rss       = fetch_feed( $feed );

		$site     = esc_html( $rss->get_title() );
		$maxitems = $rss->get_item_quantity();
		$items    = $rss->get_items( 0, $maxitems );

		$since_id = $this->latest_support;

		if ( empty( $since_id ) )
			$since_id = 0;

		$from_user_link = bp_core_get_userlink( $this->user_id );
		       
		for ( $i=0; $i < count( $items ); $i++ ) {
	        $item = $items[$i];
	        $id = intval( str_replace( '@http://wordpress.org/support/', '', $item->get_id() ) );

	        if ( empty( $latest_id ) || $latest_id < $id )
				$latest_id = $id;

			if ( empty( $since_id ) || intval( $since_id ) < $id ) {

				$title = esc_html( $item->get_title() );
	        	preg_match( '/^(.*) &quot;\[Plugin: (.*)\] (.*)&quot;/', $title, $matches );

	        	if ( $item->get_author()->name == $this->wporg_username && ! empty( $matches[2] ) && ! empty( $matches[3] ) ) {

	        		$plugin_name = esc_html( $matches[2] );
	        		$plugin_url = '<a href="'.site_url( '/tag/' . sanitize_title( $plugin_name ) .'/'.'" title="' . esc_attr__( 'View Posts about the plugin', 'thaim-utilities' ) . '">'. $plugin_name .'</a>' );
	        		$topic = esc_html( $matches[3] );
	        		$link = esc_url( $item->get_link() );
	        		$topic_link = '<a href="'. $link .'" title="' . esc_attr__( 'View on WordPress.org', 'thaim-utilities' ) . '">'. $topic .'</a>';
	        		$daterss = $item->get_date( 'Y-m-d H:i:s' );
	        		
	        		$action  = sprintf( __( '%s gave a support reply on %s about %s', 'thaim-utilities' ), $from_user_link, $plugin_url, $topic_link );
	        		$description = esc_html( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) );
	        		
	        		$args = array(
						'action'            => $action,
						'content'           => $description,
						'type'              => 'wporg_support',
						'user_id'           => $this->user_id,
						'item_id'           => $id,
						'recorded_time'     => $daterss
					);

					$this->publish_activity( $args );
	        	}
			}
	    }
		/* Next time we'll start from this id */
		if( ! empty( $latest_id ) )
			bp_update_user_meta( $this->user_id, 'thaim_wporg_latest_support_id', $latest_id );
	}

	/**
	 * Let's record an activity
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	private function publish_activity( $args = array() ) {

		$defaults = array(
			'action'            => '',
			'content'           => '',
			'component'         => buddypress()->thaimutilities->id,
			'type'              => '',
			'user_id'           => $this->user_id,
			'primary_link'      => '',
			'item_id'           => false,
			'secondary_item_id' => false,
			'recorded_time'     => bp_core_current_time(),
			'hide_sitewide'     => false,
			'tweet_permalink'   => false,
		);
		
		$params = bp_parse_args( $args, $defaults, 'thaim_publish_activity_args' );
		extract( $params, EXTR_SKIP );
		
		$activity_id = bp_activity_add( array(
			'user_id'           => $user_id,
			'action'            => apply_filters( 'thaim_utilities_new_update_action', $action ),
			'content'           => apply_filters( 'thaim_utilities_new_update_content', $content ),
			'primary_link'      => apply_filters( 'thaim_utilities_new_update_primary_link', $primary_link ),
			'component'         => $component,
			'type'              => $type,
			'item_id'           => $item_id,
			'secondary_item_id' => $secondary_item_id,
			'recorded_time'     => $recorded_time,
			'hide_sitewide'     => $hide_sitewide
		) );

		if( $activity_id && ! empty( $tweet_permalink ) )
			bp_activity_update_meta( $activity_id, 'tweet_permalink' , esc_url_raw( $tweet_permalink ) );

		if( $activity_id )
			return true;

	}

	/**
	 * Register activity actions
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function register_actions() {
		$bp = buddypress();

		if ( ! empty( $this->twitter_connexion ) ) {
			bp_activity_set_action(
				$bp->thaimutilities->id,
				'twitter_tweet',
				__( 'Tweets', 'thaim-utilities' )
			);
		}
		
		if ( ! empty( $this->wporg_support ) ) {
			bp_activity_set_action(
				$bp->thaimutilities->id,
				'wporg_support',
				__( 'Plugins support', 'thaim-utilities' )
			);
		}
	}

	/**
	 * Display a tweet using WP embed feature
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function maybe_embed_tweet( $content = '', $activity = false ) {
		$in_widget = apply_filters( 'thaim_utilities_in_widget', false );

		if ( ! empty( $in_widget ) || is_admin() )
			return $content;

		if ( 'twitter_tweet' == bp_get_activity_type() && bp_is_single_activity() )
			$content = bp_activity_get_meta( $activity->id, 'tweet_permalink' );

		return $content;
	}

	/**
	 * Add filters to activity dropbox filter
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	public function maybe_add_activity_filters() {
		$actions = buddypress()->activity->actions->thaimutilities;

		foreach ( $actions as $action ) {
			?>
			<option value="<?php echo esc_attr( $action['key'] ) ;?>"><?php echo esc_html( $action['value'] ) ;?></option>
			<?php
		}
	}
}
add_action( 'bp_loaded', array( 'Thaim_Utilities_Activity', 'start' ), 20 );
