<?php
/**
 * Practical Publishing Actions main plugin and class file.
 *
 * @package Practical_Publishing_Actions
 */

/**
 * Plugin Name: Practical Publishing Actions
 * Plugin URI:  https://github.com/lancehmd/practical-publishing-actions
 * Description: Add a new post or return to the posts list after creating or updating one.
 * Version:     1.0.0
 * Author:      Lance Hammond
 * Author URI:  https://github.com/lancehmd
 * Text Domain: practical-publishing-actions
 * Domain Path: /languages/
 * License:     MIT
 * Tags:        practical, publish, publishing, actions, add new, go back, save and add new, save and go back
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Practical Publishing Actions main class.
 */
final class Practical_Publishing_Actions {

	/**
	 * Initialize our plugin hooks.
	 */
	public function init() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_stylesheet' ) );
		add_action( 'post_submitbox_start', array( $this, 'render_publishing_actions' ), 99 );
		add_filter( 'redirect_post_location', array( $this, 'redirect_after_publish' ), 10, 2 );

	}

	/**
	 * Register our admin stylesheet.
	 */
	public function register_admin_stylesheet() {

		wp_register_style( 'practical-publishing-actions', plugin_dir_url( __FILE__ ) . 'practical-publishing-actions.css', array(), '1.0.0' );

	}

	/**
	 * Render our button with the other publish actions.
	 */
	public function render_publishing_actions() {

		global $pagenow;

		// Enqueue our stylesheet.
		wp_enqueue_style( 'practical-publishing-actions' );

		// Button title for publishing.
		$add_new_title = __( 'Publish and Add New', 'practical-publishing-actions' );
		$go_back_title = __( 'Publish and Go Back', 'practical-publishing-actions' );

		// If we are updating an existing post, appropriate the button title.
		if ( 'post.php' === $pagenow ) {
			$add_new_title = __( 'Update and Add New', 'practical-publishing-actions' );
			$go_back_title = __( 'Update and Go Back', 'practical-publishing-actions' );
		}

		?>

		<div class="ppa-actions">

			<?php wp_nonce_field( 'ppa-actions', '_ppanonce' ); ?>

			<button class="button button-small" name="ppa-add-new">
				<?php echo esc_html( $add_new_title ); ?>
			</button>

			<button class="button button-small" name="ppa-go-back">
				<?php echo esc_html( $go_back_title ); ?>
			</button>

		</div>

		<?php

	}

	/**
	 * Redirect to the add new post page for the current post type.
	 *
	 * @param string  $location Location to redirect to.
	 * @param integer $post_id  Current post ID.
	 * @return string The new location to redirect to.
	 */
	public function redirect_after_publish( $location, $post_id ) {

		// Verify our nonce.
		if ( ! check_admin_referer( 'ppa-actions', '_ppanonce' ) ) {
			return;
		}

		// If either button was pressed, get URL variables ready.
		if ( isset( $_POST['ppa-add-new'] ) || isset( $_POST['ppa-go-back'] ) ) {
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			) );

			$post_type = get_post_type();
			$query_args = array( 'post_type' => $post_type );
		}

		// Add new after save/update.
		if ( isset( $_POST['ppa-add-new'] ) ) {
			$admin_url = admin_url( 'post-new.php' );
			$location  = add_query_arg( $query_args, $admin_url );
		}

		// Return to post type list after save/update.
		if ( isset( $_POST['ppa-go-back'] ) ) {
			$admin_url = admin_url( 'edit.php' );
			$location  = add_query_arg( $query_args, $admin_url );
		}

		return $location;

	}

}

/**
 * Start the plugin.
 *
 * @since 1.0.0
 */
function practical_publishing_actions() {

	$practical_publishing_actions = new Practical_Publishing_Actions();
	$practical_publishing_actions->init();

}

/**
 * Hook the plugin bootstrap function.
 */
if ( is_admin() ) {
	add_action( 'plugins_loaded', 'practical_publishing_actions' );
}
