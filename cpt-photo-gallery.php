<?php
/*
* Plugin Name: CPT Photo Gallery
* Description: Photo/Video Gallery with Custom Post Type
* Version: 1.0.0
* Plugin URI:
* Author: myhope1227
*
*/

//Exit if accessed directly

if ( !class_exists( 'cpt_photo_gallery' ) ):
class cpt_photo_gallery{
	public function instance() {
		add_action( 'init', array( $this, 'plugin_init' ) );
		add_action( 'add_meta_boxes', array($this, 'plugin_add_custom_box' ) );
		add_action( 'load-post-new.php', array($this, 'plugin_load_page_template' ) );
		add_action( 'wp_ajax_get_instagram_feed', array( $this, 'plugin_get_instagram_feed' ) );

		add_action( 'admin_head', function(){
			if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'gallery' ){
				wp_enqueue_media();
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'bootstrap_css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', '', '4.4.1' );
				wp_register_style( 'plugin_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', '', time(), 'screen' );
				wp_enqueue_style( 'plugin_admin_css' );
			}
		});

		// Add JS files to admin
		add_action( 'admin_enqueue_scripts', function(){
			if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'gallery' ){
				wp_enqueue_script('thickbox');
				wp_register_script( 'plugin_js', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array( 'jquery' ), time(), true );
				$ajaxObj = array(
			        'ajaxurl' => admin_url( 'admin-ajax.php' )
			    );
			    wp_localize_script( 'plugin-js', 'ajaxObj', $ajaxObj );
				wp_enqueue_script('plugin_js');
			}
		}, 20);

		//add_action( 'save_post', array( $this, 'plugin_save_meta_box_data' ) );
	}

	public function plugin_init() {
		$labels = array(
			'name' => __( 'Galleries', 'cpt_photo_gallery' ),
			'singular_name' => __( 'Gallery', 'cpt_photo_gallery' ),
			'add_new' => __( 'Add New', 'cpt_photo_gallery' ),
			'add_new_item' => __( 'Add New Gallery', 'cpt_photo_gallery' ),
			'edit_item' => __( 'Edit Gallery', 'cpt_photo_gallery' ),
			'new_item' => __( 'New Gallery', 'cpt_photo_gallery' ),
			'view_item' => __( 'View Galleries', 'cpt_photo_gallery' ),
			'search_items' => __( 'Search Galleries', 'cpt_photo_gallery' ),
			'not_found' =>  __( 'No Gallery Found', 'cpt_photo_gallery' ),
			'not_found_in_trash' => __( 'No Gallery found in Trash', 'cpt_photo_gallery' ),
		);

		$args = array(
			'labels' => $labels,
			'has_archive' => true,
			'public' => true,
			'hierarchical' => false,
			'show_ui'	=> true,
			'show_in_menu'	=> true,
			'supports' => array(
				'title',
				'editor',
				'page-attributes'
			),
			'rewrite'   => array( 'slug' => 'gallery' ),
			'show_in_rest' => true
		);

		register_post_type( 'gallery', $args );

		/*$page_id = get_option( 'card_view_page_id' );

		add_rewrite_rule( '^ecard/(.*)/?', 'index.php?page_id='.$page_id.'&card_id=$matches[1]', 'top' );
		add_rewrite_tag('%card_id%', '([^&/]+)');
		flush_rewrite_rules();*/
	}

	public function plugin_add_custom_box() {
		add_meta_box(
            'grid_column',          // Unique ID
            'Grid Column',			// Box title
            array( $this, 'plugin_box_grid_column_html' ),  // Content callback, must be of type callable
            'gallery',                   // Post type,
            'normal'
        );
	}

	public function plugin_save_meta_box_data( $post_id ) {
	    
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	        return;
	    }

	    if ( isset( $_POST['post_type'] ) && 'links' == $_POST['post_type'] ) {
	        if ( ! current_user_can( 'edit_page', $post_id ) ) {
	            return;
	        }
	    }
	    else {
	        if ( ! current_user_can( 'edit_post', $post_id ) ) {
	            return;
	        }
	    }

	    if ( ! isset( $_POST['grid_column'] ) ) {
	        return;
	    }

	    // Sanitize user input.
	    $grid_column = sanitize_text_field( $_POST['grid_column'] );

	    // Update the meta field in the database.
	    update_post_meta( $post_id, 'gallery_grid_column', $grid_column );
	}

	public function plugin_box_grid_column_html( $post ) {
    	$grid_column = get_post_meta( $post->ID, 'gallery_grid_column', true );
?>
		<input type="text" name="grid_column" id="grid_column_box" class="postbox" size="100" required value="<?php echo $grid_column;?>" />
<?php
	}

	public function plugin_box_url_html( $post ) {
		$link_url = get_post_meta( $post->ID, 'link_url', true );
?>
		<input type="text" name="link_url" id="link_url_box" class="postbox" size="100" required value="<?php echo $link_url;?>" />
<?php
	}

	public function plugin_load_page_template() {
		if ( $_GET['post_type'] == 'gallery' ){
			include( plugin_dir_path( __FILE__ ) . 'includes/editor.php' );
			die;
		}
	}

	public function plugin_get_instagram_feed() {
		$username = $_REQUEST['username'];
		$json = file_get_contents('https://www.instagram.com/'.$username.'/media/');
		$instagram_feed_data = json_decode($json, true);
		var_dump($instagram_feed_data);
		exit;
	}
}

$photo_gallery = new cpt_photo_gallery();
$photo_gallery->instance();

endif;
