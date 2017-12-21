<?php
/**
 * 管理界面核心API
 *
 * @package WordPress
 * @subpackage Administration
 * @since 2.3.0
 */

if ( ! defined('WP_ADMIN') ) {
	/*
	 * 这个文件被wp-admin / admin.php以外的文件包含时，会跳过了一些设置。
	 * 因为load_default_textdomain()(这个在wp-settings.php的399行被调用)在此上下文中不会运行并加载翻译消息。
	 * 为了确保管理消息目录已被加载，需要下面的动作。
	 */
	load_textdomain( 'default', WP_LANG_DIR . '/admin-' . get_locale() . '.mo' );
}

/** WordPress Administration Hooks */
require_once(ABSPATH . 'wp-admin/includes/admin-filters.php'); // 管理页面加载的钩子

/** WordPress Bookmark Administration API */
require_once(ABSPATH . 'wp-admin/includes/bookmark.php'); // 定义书签相关的函数

/** WordPress Comment Administration API */
require_once(ABSPATH . 'wp-admin/includes/comment.php'); // 定义评论相关的函数

/** WordPress Administration File API */
require_once(ABSPATH . 'wp-admin/includes/file.php'); // 定义文件相关的函数

/** WordPress Image Administration API */
require_once(ABSPATH . 'wp-admin/includes/image.php');

/** WordPress Media Administration API */
require_once(ABSPATH . 'wp-admin/includes/media.php');

/** WordPress Import Administration API */
require_once(ABSPATH . 'wp-admin/includes/import.php');

/** WordPress Misc Administration API */
require_once(ABSPATH . 'wp-admin/includes/misc.php');

/** WordPress Options Administration API */
require_once(ABSPATH . 'wp-admin/includes/options.php');

/** WordPress Plugin Administration API */
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

/** WordPress Post Administration API */
require_once(ABSPATH . 'wp-admin/includes/post.php');

/** WordPress Administration Screen API */
require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
require_once(ABSPATH . 'wp-admin/includes/screen.php');

/** WordPress Taxonomy Administration API */
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');

/** WordPress Template Administration API */
require_once(ABSPATH . 'wp-admin/includes/template.php');

/** WordPress List Table Administration API and base class */
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table-compat.php');
require_once(ABSPATH . 'wp-admin/includes/list-table.php');

/** WordPress Theme Administration API */
require_once(ABSPATH . 'wp-admin/includes/theme.php');

/** WordPress User Administration API */
require_once(ABSPATH . 'wp-admin/includes/user.php');

/** WordPress Site Icon API */
require_once(ABSPATH . 'wp-admin/includes/class-wp-site-icon.php');

/** WordPress Update Administration API */
require_once(ABSPATH . 'wp-admin/includes/update.php');

/** WordPress Deprecated Administration API */
require_once(ABSPATH . 'wp-admin/includes/deprecated.php');

/** WordPress Multisite support API */
if ( is_multisite() ) {
	require_once(ABSPATH . 'wp-admin/includes/ms-admin-filters.php');
	require_once(ABSPATH . 'wp-admin/includes/ms.php');
	require_once(ABSPATH . 'wp-admin/includes/ms-deprecated.php');
}
