<?php

/**
 * Plugin Name: Cusome Careers Management Plugin
 * Plugin URI: http://mindutopia.com
 * Description: Manages job listing
 * Version: v1
 * Author: Mindutopia
 * Author URI: http://mindutopia.com
 */

	// Register Custom Post Type
function create_mu_careers() {

	$labels = array(
		'name'                => _x( 'Careers', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Career', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Career Management ', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Career:', 'text_domain' ),
		'all_items'           => __( 'All Careers', 'text_domain' ),
		'view_item'           => __( 'View Career', 'text_domain' ),
		'add_new_item'        => __( 'Add New Career', 'text_domain' ),
		'add_new'             => __( 'Add Career', 'text_domain' ),
		'edit_item'           => __( 'Edit Career', 'text_domain' ),
		'update_item'         => __( 'Update Career', 'text_domain' ),
		'search_items'        => __( 'Search Career', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                => 'careers_list',
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	$args = array(
		'label'               => __( 'mu_careers', 'text_domain' ),
		'description'         => __( 'Custom Careers Manager', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'revisions', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => $rewrite,
		'capability_type'     => 'page',
	);
	register_post_type( 'mu_careers', $args );

}

// Hook into the 'init' action
add_action( 'init', 'create_mu_careers', 0 );

/*!------- Flush Right Ruels -------*/
//if plugin activated 
function mu_careers_activate() {
	// register taxonomies/post types here
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'mu_careers_activate' );
//if plugin removed.
function mu_careers_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mu_careers_deactivate' );
/*------- Flush Right Ruels -------*/


/*!------- Post Type Meta -------*/

// add meta box call out
function mindu_add_mu_careers_meta(){
	add_meta_box('page-meta', 'Add Content', 'mindu_add_mu_careers_meta_html', 'mu_careers', 'normal', 'low');
}

function mindu_add_mu_careers_meta_html(){
	global $post;
	
	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'post_type'        => 'page',
		'post_status'      => 'publish',
		'suppress_filters' => true );
	$posts_array = get_posts( $args );
	
	$form_page_value = get_post_meta($post->ID, 'form_page_url', 1);
	
	?>
	<table class='form-table'>
<!--
		<tr>
			<th>Form Page URL:</th>
			<td>
				<input type="text" name="form_page_url" class="widefat"  value="<?php echo esc_url(get_post_meta($post->ID, 'form_page_url', 1)); ?>"/>
			</td>
		</tr>
-->	
			<tr>
			<th>Form Page:</th>
			<td>
				<select name="form_page_url">
					<option value="default" <?php selected($form_page_value,'default'); ?>>Default Option</option>
					<?php foreach($posts_array as $page){
						?><option value="<?php echo $page->ID; ?>" <?php selected($form_page_value,$page->ID); ?>><?php echo $page->post_title; ?></option><?php
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>Note:</th>
			<td><p><strong>"Default Option"</strong> This Setting will use the default form page. This page is set in the Careers Options Page on the left. </p></td>
		</tr>
		<?php
		$default_form_page_url = get_option('default_form_page_url');
		if($default_form_page_url != ''){ ?>
		<tr>
			<th>Default Option:</th>
			<td>
				<p><?php echo $default_form_page_url; ?> <a href="<?php echo $default_form_page_url; ?>">Go to Form -></a></p>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php
}

//save your values
function mindu_update_mu_careers_meta(){
	global $post;

	if( isset( $_POST['form_page_url'] ) ){
		update_post_meta($post->ID, 'form_page_url', $_POST['form_page_url']);
	}
}

//add save function to hook
add_action('save_post', 'mindu_update_mu_careers_meta');

//add to admin_init
add_action('add_meta_boxes', 'mindu_add_mu_careers_meta');

/*------- Post Type Meta -------*/


/*------- Options Page -------*/

function add_mind_careers_admin(){
		add_submenu_page('edit.php?post_type=mu_careers', 'Careers Management Options', 'Careers Options', 'manage_options', 'careers-settings-page', 'mind_careers_admin');
}

function mind_careers_admin(){
	ob_start();
	//gets
	$default_form_page_url = get_option('default_form_page_url');

	?>
		<div class="wrap">
				<h2>Careers Management Options</h2>
				<form method="post" action=''>
					<?php wp_nonce_field( 'career_options_nonce', 'update_mind_career_options' ); ?>
					<table class="form-table">
						<tr>
							<th>Default Form Page URL:</th>
							<td>
								<input type="text" name="default_form_page_url" class="widefat"  value="<?php echo $default_form_page_url; ?>"/>
							</td>
						</tr>
					</table>
					<?php submit_button('Save', 'primary'); ?>
				</form>
		</div>
	<?php
	ob_flush();
}

function mind_save_career_options(){
	if(isset($_POST['update_mind_career_options'])){
		update_option( 'default_form_page_url', esc_url($_POST['default_form_page_url']));
	}
}

add_action('admin_menu', 'add_mind_careers_admin');
add_action('admin_init', 'mind_save_career_options');

/*------- Options Page -------*/


/*helpers*/

function mu_careers_form_url($id){
	//check if the meta feild is set
	$form_page_value = get_post_meta($id, 'form_page_url', 1);
	if($form_page_value != 'default'){
		//get from meta feild
		$form_url = get_permalink($form_page_value);
	}else{
		//get form optoins
		$default_form_page_url = get_option('default_form_page_url');
		$form_url = $default_form_page_url;
	}
	
	return $form_url;
}

function mu_careers_pdf_url($id){
	//set perms
	$args = array(
		'posts_per_page'   => 1,
		'post_type'        => 'attachment',
		'post_mime_type'   => 'application/pdf',
		'post_parent'      => $id);
		
	$attachments = get_posts( $args );
	//check if have pdf attachments
	if(!empty($attachments)){
		//return the url to the pdf
		return $attachments[0]->guid;
	}
	else{
		return false;
	}
}



?>