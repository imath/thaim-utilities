<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Thaim Utilities Activity Widget Class
 *
 * @package Thaim Utilities
 * @subpackage Activity
 * @since   1.0.0
 */
class Thaim_Utilities_Widget extends WP_Widget {

	/**
	 * The constructor
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 */
	function __construct() {
		$widget_ops = array( 
			'classname' => 'thaim-utilities-widget', 
			'description' => __( 'Latest activities', 'thaim-utilities' ) 
		);
		parent::__construct( false, _x( '(Thaim Utilities) Widget', 'widget name', 'thaim-utilities' ), $widget_ops );
	}
	
	/**
	 * Registers the widget
	 *
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 * 
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'Thaim_Utilities_Widget' );
	}

	/**
	 * Displays the content of the widget
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 *
	 * @param array $args 
	 * @param array $instance
	 * @return string html the content of the shortcode
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Activities', 'thaim-utilities' ) : $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;

		$activity_args = array( 'max' => 5 );

		if ( ! empty( $instance['number'] ) ) {
			$activity_args['max'] = absint( $instance['number'] );
		}

		if ( ! empty( $instance['activity_type'] ) ) {
			$activity_args['action'] = esc_attr( $instance['activity_type'] );
		}

		add_filter( 'thaim_utilities_in_widget', '__return_true' );

		if ( bp_has_activities( $activity_args ) ) : ?>

			<ul class="widget-thaim-activities">
			
			<?php while ( bp_activities() ) : bp_the_activity(); ?>

				<li id="activity-<?php bp_activity_id(); ?>">

					<?php if ( bp_activity_has_content() ) : ?>

						<div class="activity-inner">

							<?php bp_activity_content_body(); ?>

							<?php thaim_utilities_activity_view_activity(); ?>

						</div>

					<?php endif; ?>

				</li>

			<?php endwhile; ?>

			</ul>
			
		<?php endif;
		
		remove_filter( 'thaim_utilities_in_widget', '__return_true' );
		
		echo $after_widget;
	}

	/**
	 * Updates the title of the widget
	 * 
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 *
	 * @param array $new_instance 
	 * @param array $old_instance 
	 * @return array the instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['activity_type'] = strip_tags( $new_instance['activity_type'] );
		$instance['number'] = absint( $new_instance['number'] );

		return $instance;
	}

	/**
	 * Displays the form in the admin of Widgets
	 *
	 * @package Thaim Utilities
	 * @subpackage Activity
	 * @since   1.0.0
	 * 
	 * @param array $instance
	 * @return string html the form
	 */
	function form( $instance ) {
		//Defaults
		$instance      = wp_parse_args( (array) $instance, array( 'title' => '', 'activity_type' => '', 'number' => 5 ) );
		$title         = esc_attr( $instance['title'] );
		$activity_type = esc_attr( $instance['activity_type'] );		
		$number        = absint( $instance['number'] );
	?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'thaim-utilities' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id( 'activity_type' ); ?>"><?php esc_html_e( 'Activity types', 'thaim-utilities' )?></label>
			<select id="<?php echo $this->get_field_id( 'activity_type' ); ?>" name="<?php echo $this->get_field_name( 'activity_type' ); ?>" class="widefat">
				<option value="0" <?php selected( 0, $activity_type );?>>
					<?php esc_html_e( 'All types', 'thaim-utilities' );?>
				</option>
				<option value="twitter_tweet" <?php selected( 'twitter_tweet', $activity_type );?>>
					<?php esc_html_e( 'Tweets', 'thaim-utilities' ); ?>
				</option>
				<option value="wporg_support" <?php selected( 'wporg_support', $activity_type );?>>
					<?php esc_html_e( 'Plugins support', 'thaim-utilities' ); ?>
				</option>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Amount:', 'thaim-utilities' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" /></p>
		<?php
	}
}

add_action( 'bp_widgets_init', array( 'Thaim_Utilities_Widget', 'register_widget' ), 10 );
