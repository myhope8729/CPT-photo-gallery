<?php
/**
 * Manage Gallery Editor Screen
 *
 */

header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );
if ( ! defined( 'WP_ADMIN' ) ) {
	require_once( dirname( __FILE__ ) . '/admin.php' );
}

global $post_type, $post_type_object, $post;

$post_type = $_GET['post_type'];
$post_type_object = get_post_type_object( $post_type );

if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->create_posts ) ) {
	wp_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to create posts as this user.' ) . '</p>',
		403
	);
}

$post_id = 0;
if ( isset( $_REQUEST['post_id'] ) ) {
	$post_id = absint( $_REQUEST['post_id'] );
	if ( ! get_post( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		$post_id = 0;
	}
}

if ( $_POST ) {
	// Handle post data
}

$title = $post_type_object->labels->add_new_item;
$parent_file = "edit.php?post_type=$post_type";

wp_user_settings();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="gallery-editor">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo $title; ?></title>
	<?php wp_head(); ?>
	<script>
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
	</script>

<?php

wp_enqueue_style( 'colors' );
//wp_enqueue_style( 'ie' );

do_action( 'admin_enqueue_scripts', $hook_suffix );

do_action( 'admin_head' );

?>

</head>
<body>
	<div class="editor-wrap">
		<form method="post" action="" class="<?php echo esc_attr( $form_class ); ?>">
			<input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>" />
			<?php wp_nonce_field( 'gallery-form' ); ?>
			<div id="form-action">
				<div class="container">
					<input type="submit" class="button button-primary" name="preview" value="Preview" />
					<input type="submit" class="button button-primary" name="save" value="Save">
				</div>
			</div>
			<div id="form-header">
				<input type="text" class="post-title" name="post-title" value="" placeholder="Add Title" />
			</div>
			<div id="gallery-wrapper">
				<div class="container">
					<div class="add-buttons">
						<button class="button add-gallery-image">Add images</button>
						<button class="button add-gallery-video">Add video</button>
						<button class="button gallery-setting">Gallery Setting</button>
					</div>
					<div class="gallery-container grid-col-3">
					</div>
					<input type="hidden" name="image_gallery" id="image_gallery" value=""/>
				</div>
			</div>
			<div id="add_gallery_videos_modal" style="display:none;">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label>Video URL</label></th>
						<td><input placeholder="https://youtube.com/watch?v=xxxxxxxxxxx" class="regular-text code url" type="url" >
							<p class="description" id="tagline-description">Youtube, Facebook Video, Vimeo or Dailymotion URL</p></td>
					</tr>
					<tr>
						<th scope="row"><label>Title</label></th>
						<td><input class="regular-text title" type="text">
							<p class="description" id="tagline-description">Optional</p></td>
					</tr>
					</tbody>
				</table>
				<p class="submit"><input name="add_video" class="btn btn-info" value="Add video" type="submit"></p>
			</div>
			<div id="edit_title_modal" style="display:none;">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label>Title</label></th>
						<td><input class="regular-text title" type="text" >
							<p class="description" id="tagline-description">The title will be shown after opening the gallery.</p></td>
					</tr>
					</tbody>
				</table>
				<p class="submit"><input name="change_title" class="btn btn-info" value="Change title" type="submit"></p>
			</div>

			<div id="show_modal" style="display:none;">

			</div>

			<div id="gallery_settings_modal" style="display:none;">
				<table class="form-table gallery-setting-table">
					<tbody>
					<tr>
						<th scope="row"><label>Grid Columns</label></th>
						<td><input placeholder="3" class="regular-text grid-columns" type="number" value="3" min="1" max="7">
							<p class="description" id="tagline-description">Set grid column count for gallery.</p></td>
					</tr>
					<tr>
						<th scope="row"><label for="import_instagram">Import Instagram feed</label></th>
						<td><input class="regular-text title" id="import_instagram" type="checkbox"></td>
					</tr>
					</tbody>
				</table>
				<p class="submit"><input name="save_setting" class="btn btn-info" value="Save" type="submit"></p>
			</div>
		</form>
	</div>
	
<?php
include( ABSPATH . 'wp-admin/admin-footer.php' );

?>
<script>
	console.log(typeof jQuery);
</script>
<?php