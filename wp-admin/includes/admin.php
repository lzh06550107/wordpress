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
require_once(ABSPATH . 'wp-admin/includes/image.php'); // 定义图片相关的函数

/** WordPress Media Administration API */
require_once(ABSPATH . 'wp-admin/includes/media.php'); // 定义媒体相关的函数

/** WordPress Import Administration API */
require_once(ABSPATH . 'wp-admin/includes/import.php'); // 定义导入器相关的函数

/** WordPress Misc Administration API */
require_once(ABSPATH . 'wp-admin/includes/misc.php'); // 定义其它相关函数

/** WordPress Options Administration API */
require_once(ABSPATH . 'wp-admin/includes/options.php'); // 选项相关的函数

/** WordPress Plugin Administration API */
require_once(ABSPATH . 'wp-admin/includes/plugin.php'); // 插件相关的管理函数

/** WordPress Post Administration API */
require_once(ABSPATH . 'wp-admin/includes/post.php'); // 文章相关的函数

/** WordPress Administration Screen API */
require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php'); // 定义WP_Screen类
require_once(ABSPATH . 'wp-admin/includes/screen.php'); // 定义管理界面相关的函数

/** WordPress Taxonomy Administration API */
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); // 定义分类法相关的函数

/** WordPress Template Administration API */
require_once(ABSPATH . 'wp-admin/includes/template.php'); // 定义模板相关的函数

/** WordPress List Table Administration API and base class */
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php'); // 定义列表类
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table-compat.php'); // 向后兼容的列表类
require_once(ABSPATH . 'wp-admin/includes/list-table.php'); // 定义列表相关函数

/** WordPress Theme Administration API */
require_once(ABSPATH . 'wp-admin/includes/theme.php'); // 定义主题管理函数

/** WordPress User Administration API */
require_once(ABSPATH . 'wp-admin/includes/user.php'); // 定义用户管理函数

/** WordPress Site Icon API */
require_once(ABSPATH . 'wp-admin/includes/class-wp-site-icon.php'); // 定义WP_Site_Icon类

/** WordPress Update Administration API */
require_once(ABSPATH . 'wp-admin/includes/update.php'); // 定义系统更新相关函数

/** WordPress Deprecated Administration API */
require_once(ABSPATH . 'wp-admin/includes/deprecated.php'); // 过时函数

/** WordPress Multisite support API */
if ( is_multisite() ) {
	require_once(ABSPATH . 'wp-admin/includes/ms-admin-filters.php');
	require_once(ABSPATH . 'wp-admin/includes/ms.php');
	require_once(ABSPATH . 'wp-admin/includes/ms-deprecated.php');
}
