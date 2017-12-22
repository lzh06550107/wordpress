<?php
/**
 * 编辑文章管理面板。
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$parent_file = 'edit.php';
$submenu_file = 'edit.php';

wp_reset_vars( array( 'action' ) ); // 从$_get和$_post中获取值，并设置为全局变量

// 获取文章ID
if ( isset( $_GET['post'] ) )
 	$post_id = $post_ID = (int) $_GET['post'];
elseif ( isset( $_POST['post_ID'] ) )
 	$post_id = $post_ID = (int) $_POST['post_ID'];
else
 	$post_id = $post_ID = 0;

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;

if ( $post_id )
	$post = get_post( $post_id ); // 获取文章对象

if ( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type ); // 获取文章类型对象
}

if ( isset( $_POST['deletepost'] ) )
	$action = 'delete';
elseif ( isset($_POST['wp-preview']) && 'dopreview' == $_POST['wp-preview'] )
	$action = 'preview';

$sendback = wp_get_referer(); // 获取当前请求来源地址
if ( ! $sendback ||
     strpos( $sendback, 'post.php' ) !== false ||
     strpos( $sendback, 'post-new.php' ) !== false ) {
	if ( 'attachment' == $post_type ) {  // 如果是附件，操作完成后跳转回上传页面
		$sendback = admin_url( 'upload.php' );
	} else {
		$sendback = admin_url( 'edit.php' ); // 否则跳转到编辑文章页面
		if ( ! empty( $post_type ) ) {
			$sendback = add_query_arg( 'post_type', $post_type, $sendback ); // 如果文章类型存在，则跳转到指定文章类型的编辑页面
		}
	}
} else { // 其它？？？
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), $sendback );
}

switch($action) {
case 'post-quickdraft-save': // 文章草稿快速保存
	// Check nonce and capabilities
	$nonce = $_REQUEST['_wpnonce'];
	$error_msg = false;

	// For output of the quickdraft dashboard widget
	require_once ABSPATH . 'wp-admin/includes/dashboard.php';

	if ( ! wp_verify_nonce( $nonce, 'add-post' ) ) // 验证随机码
		$error_msg = __( 'Unable to submit this form, please refresh and try again.' );

	if ( ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) { // 验证用户权限
		exit;
	}

	if ( $error_msg )
		return wp_dashboard_quick_press( $error_msg ); // 快速草稿小构件显示和草稿创建。

	$post = get_post( $_REQUEST['post_ID'] ); // 获取文章对象
	check_admin_referer( 'add-' . $post->post_type ); // 确保当前请求来自另一个管理页面。

	$_POST['comment_status'] = get_default_comment_status( $post->post_type );
	$_POST['ping_status']    = get_default_comment_status( $post->post_type, 'pingback' );

	edit_post(); // 使用$_POST中的值更新存在的文章
	wp_dashboard_quick_press(); // 快速草稿小构件显示和草稿创建
	exit;

case 'postajaxpost':
case 'post':
	check_admin_referer( 'add-' . $post_type );
	$post_id = 'postajaxpost' == $action ? edit_post() : write_post(); // 使用来自"Write Post" 表单的$_POST信息创建一个新的文章
	redirect_post( $post_id ); // 重定向到预览页面
	exit();

case 'edit': // 就是显示所有页面
	$editing = true;

	if ( empty( $post_id ) ) { // 如果没有指定文章id，则跳转到显示所有页面
		wp_redirect( admin_url('post.php') );
		exit();
	}

	if ( ! $post )
		wp_die( __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Invalid post type.' ) );

	if ( ! in_array( $typenow, get_post_types( array( 'show_ui' => true ) ) ) ) {
		wp_die( __( 'Sorry, you are not allowed to edit posts in this post type.' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) )
		wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

	if ( 'trash' == $post->post_status )
		wp_die( __( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.' ) );

	if ( ! empty( $_GET['get-post-lock'] ) ) {
		check_admin_referer( 'lock-post_' . $post_id );
		wp_set_post_lock( $post_id );
		wp_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit();
	}

	$post_type = $post->post_type;
	if ( 'post' == $post_type ) {
		$parent_file = "edit.php";
		$submenu_file = "edit.php";
		$post_new_file = "post-new.php";
	} elseif ( 'attachment' == $post_type ) {
		$parent_file = 'upload.php';
		$submenu_file = 'upload.php';
		$post_new_file = 'media-new.php';
	} else {
		if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true )
			$parent_file = $post_type_object->show_in_menu;
		else
			$parent_file = "edit.php?post_type=$post_type";
		$submenu_file = "edit.php?post_type=$post_type";
		$post_new_file = "post-new.php?post_type=$post_type";
	}

	/**
	 * Allows replacement of the editor.
     * 允许替换编辑器
	 *
	 * @since 4.9.0
	 *
	 * @param boolean      Whether to replace the editor. Default false.
	 * @param object $post Post object.
	 */
	if ( apply_filters( 'replace_editor', false, $post ) === true ) {
		break;
	}

	if ( ! wp_check_post_lock( $post->ID ) ) { // 锁定编辑的文章
		$active_post_lock = wp_set_post_lock( $post->ID );

		if ( 'attachment' !== $post_type ) // 如果文章类型不是附件，则插入自动保存js脚本
			wp_enqueue_script('autosave'); // 这个脚本位于include/js目录中
	}

	$title = $post_type_object->labels->edit_item;
	$post = get_post($post_id, OBJECT, 'edit'); // 获取文章对象

	if ( post_type_supports($post_type, 'comments') ) { // 如果支持评论，则插入管理评论的js脚本
		wp_enqueue_script('admin-comments');
		enqueue_comment_hotkeys_js(); // 评论快捷键脚本
	}

	include( ABSPATH . 'wp-admin/edit-form-advanced.php' );

	break;

case 'editattachment':
	check_admin_referer('update-post_' . $post_id);

	// Don't let these be changed
	unset($_POST['guid']);
	$_POST['post_type'] = 'attachment';

	// Update the thumbnail filename
	$newmeta = wp_get_attachment_metadata( $post_id, true );
	$newmeta['thumb'] = $_POST['thumb'];

	wp_update_attachment_metadata( $post_id, $newmeta );

case 'editpost':
	check_admin_referer('update-post_' . $post_id);

	$post_id = edit_post();

	// Session cookie flag that the post was saved
	if ( isset( $_COOKIE['wp-saving-post'] ) && $_COOKIE['wp-saving-post'] === $post_id . '-check' ) {
		setcookie( 'wp-saving-post', $post_id . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl() );
	}

	redirect_post($post_id); // Send user on their way while we keep working

	exit();

case 'trash':
	check_admin_referer('trash-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'The item you are trying to move to the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Invalid post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'Sorry, you are not allowed to move this item to the Trash.' ) );

	if ( $user_id = wp_check_post_lock( $post_id ) ) {
		$user = get_userdata( $user_id );
		wp_die( sprintf( __( 'You cannot move this item to the Trash. %s is currently editing.' ), $user->display_name ) );
	}

	if ( ! wp_trash_post( $post_id ) )
		wp_die( __( 'Error in moving to Trash.' ) );

	wp_redirect( add_query_arg( array('trashed' => 1, 'ids' => $post_id), $sendback ) );
	exit();

case 'untrash':
	check_admin_referer('untrash-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'The item you are trying to restore from the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Invalid post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'Sorry, you are not allowed to restore this item from the Trash.' ) );

	if ( ! wp_untrash_post( $post_id ) )
		wp_die( __( 'Error in restoring from Trash.' ) );

	wp_redirect( add_query_arg('untrashed', 1, $sendback) );
	exit();

case 'delete':
	check_admin_referer('delete-post_' . $post_id);

	if ( ! $post )
		wp_die( __( 'This item has already been deleted.' ) );

	if ( ! $post_type_object )
		wp_die( __( 'Invalid post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		wp_die( __( 'Sorry, you are not allowed to delete this item.' ) );

	if ( $post->post_type == 'attachment' ) {
		$force = ( ! MEDIA_TRASH );
		if ( ! wp_delete_attachment( $post_id, $force ) )
			wp_die( __( 'Error in deleting.' ) );
	} else {
		if ( ! wp_delete_post( $post_id, true ) )
			wp_die( __( 'Error in deleting.' ) );
	}

	wp_redirect( add_query_arg('deleted', 1, $sendback) );
	exit();

case 'preview':
	check_admin_referer( 'update-post_' . $post_id );

	$url = post_preview();

	wp_redirect($url);
	exit();

default:
	/**
	 * Fires for a given custom post action request.
     * 触发指定的动作
	 *
	 * The dynamic portion of the hook name, `$action`, refers to the custom post action.
	 *
	 * @since 4.6.0
	 *
	 * @param int $post_id Post ID sent with the request.
	 */
	do_action( "post_action_{$action}", $post_id );

	wp_redirect( admin_url('edit.php') ); // 上面动作都没有处理，则跳转到显示所有文章的列表页面。
	exit();
} // end switch
include( ABSPATH . 'wp-admin/admin-footer.php' );
