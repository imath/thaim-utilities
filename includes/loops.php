<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function thaim_utilities_wordpress_loop() {
	/**
	 * WordPress screen loop
	 */
	if ( thaim_utitilities_has_plugins() ) : ?>

		<ul id="plugins-list" class="item-list" role="main">

		<?php while ( thaim_utitilities_plugins() ) : thaim_utitilities_the_plugin(); ?>

			<li>
				<div class="item-avatar">
					<a href="<?php thaim_utitilities_plugin_info_link(); ?>"><?php thaim_utitilities_plugin_type(); ?></a>
				</div>

				<div class="item">
					<div class="item-title"><a href="<?php thaim_utitilities_plugin_info_link(); ?>"><?php thaim_utitilities_plugin_name(); ?></a></div>
					<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'thaim-utilities' ), thaim_utitilities_plugin_last_active() ); ?></span></div>
					<div class="item-desc"><?php thaim_utitilities_plugin_description(); ?></div>

				</div>

				<div class="action">

					<?php thaim_utitilities_plugin_tag_link() ; ?>

					<?php thaim_utitilities_plugin_download_link() ; ?>

					<div class="meta">

						<?php thaim_utilities_downloaded(); ?>
						<?php thaim_utilities_display_version(); ?>
						<?php thaim_utilities_display_required(); ?>

					</div>

				</div>

				<div class="clear"></div>
			</li>

		<?php endwhile; ?>

		</ul>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'Oops, seems WordPress.org is not reachable...', 'thaim-utilities' ); ?></p>
		</div>

	<?php endif;
}

function thaim_utilities_github_loop() {
	/**
	 * Github screen loop
	 */
	if ( thaim_utitilities_has_gits() ) : ?>

		<ul id="plugins-list" class="item-list" role="main">

		<?php while ( thaim_utitilities_gits() ) : thaim_utitilities_the_git(); ?>

			<li>
				<div class="item-avatar">
					<a href="<?php thaim_utitilities_git_link(); ?>"><?php thaim_utitilities_git_icon(); ?></a>
				</div>

				<div class="item">
					<div class="item-title"><a href="<?php thaim_utitilities_git_link(); ?>"><?php thaim_utitilities_git_name(); ?></a></div>
					<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'thaim-utilities' ), thaim_utitilities_get_git_last_active() ); ?></span></div>
					<div class="item-desc"><?php thaim_utitilities_git_description(); ?></div>

				</div>

				<div class="action">

					<?php thaim_utitilities_git_download_link() ; ?>

					<div class="meta">

						<?php thaim_utilities_display_stargazers(); ?>
						<?php thaim_utilities_display_watchers(); ?>

					</div>

				</div>

				<div class="clear"></div>
			</li>

		<?php endwhile; ?>

		</ul>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'Oops, seems Github repos are not reachable...', 'thaim-utilities' ); ?></p>
		</div>

	<?php endif;
}
