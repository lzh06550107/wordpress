<?php
/**
 * 用于设置和修复常用变量，并包含WordPress程序和类库。也就是说WordPress运行过程中使用的大多数变量、函数和类等核心代码都是在这个文件中定义的。
 *
 * 允许在wp-config.php中进行一些配置（请参阅default-constants.php）
 *
 * @package WordPress
 */

/**
 * WordPress函数、类和核心内容的目录位置
 *
 * @since 1.0.0
 */
define( 'WPINC', 'wp-includes' );

// 包含初始化必须的文件
require( ABSPATH . WPINC . '/load.php' ); // 该文件无执行代码，主要用于定义一些WP可能用到的一些函数
require( ABSPATH . WPINC . '/default-constants.php' ); // 该文件无执行代码，主要用于定义一些WP常量
require_once( ABSPATH . WPINC . '/plugin.php' ); // 初始化全局过滤器，动作等，还定义了一些过滤器、钩子以及插件相关的函数

/*
 * 在version.php中，这些值不能直接设置为全局变量。当更新时，如果这些变量已经设置，我们包含来自另一个安装的version.php
 * 文件时，需要避免这些值被覆盖。
 */
global $wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $wp_local_package;
require( ABSPATH . WPINC . '/version.php' );

/**
 * 如果没有配置，在一个单站点中默认值为1。在多站点中，默认地，在ms-settings.php中被覆盖。
 *
 * @global int $blog_id
 * @since 2.0.0
 */
global $blog_id;

// 设置初始化默认常量：WP_MEMORY_LIMIT, WP_MAX_MEMORY_LIMIT, WP_DEBUG, SCRIPT_DEBUG, WP_CONTENT_DIR 和 WP_CACHE
wp_initial_constants();

// 执行在load.php中定义的该函数，检查需要PHP版本和MySQL扩展或者数据库插件
wp_check_php_mysql_versions();

// 在运行时禁用魔术引用。 wp-settings.php稍后会使用wpdb时添加魔术引号(需要总结)
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// WordPress calculates offsets from UTC.
date_default_timezone_set( 'UTC' );

// 执行在load.php中定义的该函数，关闭全局注册变量
wp_unregister_GLOBALS();

// 执行在load.php中定义的该函数，启动时规范跨web服务器的$_SERVER
wp_fix_server_vars();

// 执行在load.php中定义的该函数，检查我们是否因为缺少favicon.ico而收到请求，如果是，则直接返回空的图片
wp_favicon_request();

// 执行在load.php中定义的该函数，检查是否处于维护模式
wp_maintenance();

// 启动计时器
timer_start();

// 执行在load.php中定义的该函数，检查是否处于调试模式
wp_debug_mode();

/**
 * 过滤是否启用加载advanced-cache.php插件。
 *
 * 此过滤器在插件可以使用之前运行。它是为非Web运行时设计的。如果返回false，则将不会加载advanced-cache.php。
 *
 * @since 4.6.0
 *
 * @param bool $enable_advanced_cache Whether to enable loading advanced-cache.php (if present).
 *                                    Default true.
 */
if ( WP_CACHE && apply_filters( 'enable_loading_advanced_cache_dropin', true ) ) {
    // 对于高级缓存插件的使用。因为你只想要一个，所以使用一个静态插件
	WP_DEBUG ? include( WP_CONTENT_DIR . '/advanced-cache.php' ) : @include( WP_CONTENT_DIR . '/advanced-cache.php' );

    //重新初始化由advanced-cache.php手动添加的任何钩子
	if ( $wp_filter ) {
		$wp_filter = WP_Hook::build_preinitialized_hooks( $wp_filter );
	}
}

// 执行在load.php中定义的该函数，设置语言包目录
wp_set_lang_dir();

// 加载早期的WordPress文件。
require( ABSPATH . WPINC . '/compat.php' ); // 该文件无执行代码，提供某些旧PHP版本缺少或默认不包含的函数（用于支持不同版本 PHP 上的兼容和移植）
require( ABSPATH . WPINC . '/class-wp-list-util.php' ); // 数组列表工具类
require( ABSPATH . WPINC . '/functions.php' ); // 该文件无执行代码（除加载option.php文件外），定义WP主要的API（API是一组函数，通常以库的形式存在供用户调用）
require( ABSPATH . WPINC . '/class-wp-matchesmapregex.php' ); // ??
require( ABSPATH . WPINC . '/class-wp.php' ); // 定义WP类，无执行代码，定义类WP，WP类中部分方法功能如设定查询列表、解析查询parse_request()、创建主循环query_posts()、处理404请求、定义main()方法等；
require( ABSPATH . WPINC . '/class-wp-error.php' ); // 包含WP_Error类和检查该类的is_wp_error()函数。
require( ABSPATH . WPINC . '/pomo/mo.php' ); // 用于处理MO文件的类

// Include the wpdb class and, if present, a db.php database drop-in.
global $wpdb;
// 加载数据库类文件并实例化$wpdb全局变量。
require_wp_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
// 执行在load.php中定义的该函数，设置数据库前缀
wp_set_wpdb_vars();

// Start the WordPress object cache, or an external object cache if the drop-in is present.
wp_start_object_cache(); // 加载并配置外部缓存或者内部缓存

// WP所有的大多数过滤钩子和动作钩子都是通过本文件设置，即添加挂载函数
require( ABSPATH . WPINC . '/default-filters.php' );

// 如果开启，初始化多站点
if ( is_multisite() ) {
	require( ABSPATH . WPINC . '/class-wp-site-query.php' );
	require( ABSPATH . WPINC . '/class-wp-network-query.php' );
	require( ABSPATH . WPINC . '/ms-blogs.php' );
	require( ABSPATH . WPINC . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

// 在PHP脚本关闭执行之前运行。如执行shutdown钩子动作
register_shutdown_function( 'shutdown_action_hook' );

// 如果只需要基本功能（即 SHORTINIT 常量为真），则 wp-setting.php 文件执行到此即返回！
if ( SHORTINIT )
	return false;

// Load the L10n library. 无执行代码，定义语言翻译API
require_once( ABSPATH . WPINC . '/l10n.php' );
require_once( ABSPATH . WPINC . '/class-wp-locale.php' );
require_once( ABSPATH . WPINC . '/class-wp-locale-switcher.php' );

// Run the installer if WordPress is not installed.
wp_not_installed();

// Load most of WordPress.
require( ABSPATH . WPINC . '/class-wp-walker.php' ); // 显示各种树状结构的工具类
require( ABSPATH . WPINC . '/class-wp-ajax-response.php' ); // 将XML格式的响应发送回Ajax请求。
require( ABSPATH . WPINC . '/formatting.php' ); // 定义大多数用于格式化输出的函数
require( ABSPATH . WPINC . '/capabilities.php' );  // 定义用户角色和权限管理函数
require( ABSPATH . WPINC . '/class-wp-roles.php' ); // 定义WP_Roles类
require( ABSPATH . WPINC . '/class-wp-role.php' ); // 定义WP_Role类
require( ABSPATH . WPINC . '/class-wp-user.php' ); // 定义WP_User类
require( ABSPATH . WPINC . '/class-wp-query.php' ); // 定义WP_Query类
require( ABSPATH . WPINC . '/query.php' ); // 定义查询函数，内部调用WP_Query类
require( ABSPATH . WPINC . '/date.php' ); // 定义WP_Date_Query类，用于根据日期生成过滤主查询的SQL子句的类。
require( ABSPATH . WPINC . '/theme.php' ); // 定义主题、模板和样式表相关函数
require( ABSPATH . WPINC . '/class-wp-theme.php' ); // 定义WP_Theme类
require( ABSPATH . WPINC . '/template.php' ); // 定义模板加载函数
require( ABSPATH . WPINC . '/user.php' ); // 定义用户操作相关函数
require( ABSPATH . WPINC . '/class-wp-user-query.php' ); // 定义WP_User_Query类，用于用户查询
require( ABSPATH . WPINC . '/class-wp-session-tokens.php' ); // 定义WP_Session_Tokens类，用于管理用户会话token的抽象类
require( ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php' ); // 定义WP_User_Meta_Session_Tokens类，使用用户元数据保存会话
require( ABSPATH . WPINC . '/meta.php' ); // 定义操作各种对象类型元数据的函数
require( ABSPATH . WPINC . '/class-wp-meta-query.php' ); // 定义WP_Meta_Query类，用于为Meta API实现元查询的核心类
require( ABSPATH . WPINC . '/class-wp-metadata-lazyloader.php' ); // 定义WP_Metadata_Lazyloader类，用作懒加载元数据
require( ABSPATH . WPINC . '/general-template.php' ); // 定义常用的模板标签，它们可以在模板中任意使用
require( ABSPATH . WPINC . '/link-template.php' ); // 定义链接模板标签
require( ABSPATH . WPINC . '/author-template.php' ); // 定义作者模板标签
require( ABSPATH . WPINC . '/post.php' ); // 定义post相关的函数
require( ABSPATH . WPINC . '/class-walker-page.php' ); // 定义Walker_Page类，用于创建页面的HTML列表。
require( ABSPATH . WPINC . '/class-walker-page-dropdown.php' ); // 定义Walker_PageDropdown类，用于创建页面的HTML下拉列表的核心类。
require( ABSPATH . WPINC . '/class-wp-post-type.php' ); // 定义WP_Post_Type类，用于与帖子类型交互的核心类。
require( ABSPATH . WPINC . '/class-wp-post.php' ); // 定义WP_Post类，用于实现WP_Post对象的核心类。
require( ABSPATH . WPINC . '/post-template.php' ); // 定义post模板标签
require( ABSPATH . WPINC . '/revision.php' ); // 定义post版本函数
require( ABSPATH . WPINC . '/post-formats.php' ); //  定义Post format的函数
require( ABSPATH . WPINC . '/post-thumbnail-template.php' ); // Post缩略图模板标签
require( ABSPATH . WPINC . '/category.php' ); // 定义分类法中category分类函数
require( ABSPATH . WPINC . '/class-walker-category.php' ); // 定义Walker_Category类，用于创建category的HTML列表。
require( ABSPATH . WPINC . '/class-walker-category-dropdown.php' );  // 定义Walker_CategoryDropdown类，用于创建一个category的HTML下拉列表。
require( ABSPATH . WPINC . '/category-template.php' ); // 定义category的模板标签
require( ABSPATH . WPINC . '/comment.php' ); // 定义评论相关函数
require( ABSPATH . WPINC . '/class-wp-comment.php' ); // 定义WP_Comment类，用于将评论组织为实例化对象的核心类。
require( ABSPATH . WPINC . '/class-wp-comment-query.php' ); // 定义WP_Comment_Query类，用于查询评论
require( ABSPATH . WPINC . '/class-walker-comment.php' ); // 定义Walker_Comment类，用于创建comment的HTML列表。
require( ABSPATH . WPINC . '/comment-template.php' ); // 定义comment模板标签
require( ABSPATH . WPINC . '/rewrite.php' ); // 定义URL重写的相关函数
require( ABSPATH . WPINC . '/class-wp-rewrite.php' ); // 定义WP_Rewrite类，用于实现重写组件API的核心类。
require( ABSPATH . WPINC . '/feed.php' ); // 定义feed相关的函数
require( ABSPATH . WPINC . '/bookmark.php' ); // 定义链接/书签相关的函数
require( ABSPATH . WPINC . '/bookmark-template.php' ); // 定义Bookmark模板标签
require( ABSPATH . WPINC . '/kses.php' ); // HTML / XHTML过滤器，只允许指定元素和属性
require( ABSPATH . WPINC . '/cron.php' ); // 定义定时任务相关的函数
require( ABSPATH . WPINC . '/deprecated.php' ); // 过时函数，用来兼容以前的版本
require( ABSPATH . WPINC . '/script-loader.php' ); // 脚本和样式默认加载器
require( ABSPATH . WPINC . '/taxonomy.php' ); // 定义分类法相关函数
require( ABSPATH . WPINC . '/class-wp-taxonomy.php' ); // 定义WP_Taxonomy类，用于与分类法交互的核心类。
require( ABSPATH . WPINC . '/class-wp-term.php' ); // 定义WP_Term类，用于实现WP_Term对象的核心类。
require( ABSPATH . WPINC . '/class-wp-term-query.php' ); // 定义WP_Term_Query类，用于查询term的类。
require( ABSPATH . WPINC . '/class-wp-tax-query.php' ); // 定义WP_Tax_Query类，用于实现Taxonomy API的分类查询的核心类。
require( ABSPATH . WPINC . '/update.php' ); // 定义与更新相关的函数
require( ABSPATH . WPINC . '/canonical.php' ); // 定义处理重定向的规范函数
require( ABSPATH . WPINC . '/shortcodes.php' ); // 定义短代码相关的函数
/**
 * oEmbed 为这个问题提供一个解决方案，允许用户将到外部网站（如 Flickr 和 YouTube）上富内容（比如照片和视频）的 URL 转换成这些内容的嵌入式表示。
 */
require( ABSPATH . WPINC . '/embed.php' ); // 定义embed相关的函数
require( ABSPATH . WPINC . '/class-wp-embed.php' ); // 定义WP_Embed类，用于将丰富媒体（如视频和图像）轻松嵌入到内容中的API。
/**
 * 用于获取HTML以根据提供的URL嵌入远程内容的API。由WP_Embed类内部使用，但被设计为通用的。
 */
require( ABSPATH . WPINC . '/class-oembed.php' );
require( ABSPATH . WPINC . '/class-wp-oembed-controller.php' ); // 定义WP_oEmbed_Controller类，用于提供一个oEmbed端点。
require( ABSPATH . WPINC . '/media.php' ); // 定义用于媒体展示的函数
require( ABSPATH . WPINC . '/http.php' ); // 定义Http操作的函数
require( ABSPATH . WPINC . '/class-http.php' ); // 定义WP_Http类，用于管理HTTP传输和发出HTTP请求的核心类。
require( ABSPATH . WPINC . '/class-wp-http-streams.php' ); // 定义WP_Http_Streams类，用于将PHP Streams集成为HTTP传输的核心类。
require( ABSPATH . WPINC . '/class-wp-http-curl.php' ); // 定义WP_Http_Curl类，用于将Curl整合为HTTP传输的核心类。
require( ABSPATH . WPINC . '/class-wp-http-proxy.php' ); // 定义WP_HTTP_Proxy类，用于实现HTTP API代理支持的核心类。
require( ABSPATH . WPINC . '/class-wp-http-cookie.php' ); // 定义WP_Http_Cookie类，核心类用于封装一个单一的cookie对象供内部使用。
require( ABSPATH . WPINC . '/class-wp-http-encoding.php' ); // 定义WP_Http_Encoding类，用于实现deflate和gzip传输编码支持HTTP请求的核心类。
require( ABSPATH . WPINC . '/class-wp-http-response.php' ); // 定义WP_HTTP_Response类，用于准备HTTP响应的核心类。
require( ABSPATH . WPINC . '/class-wp-http-requests-response.php' ); // 定义WP_HTTP_Requests_Response类，用于标准化的Requests_Response的核心包装器对象。
require( ABSPATH . WPINC . '/class-wp-http-requests-hooks.php' ); //定义WP_HTTP_Requests_Hooks类，桥接请求内部挂钩到WordPress的action。
require( ABSPATH . WPINC . '/widgets.php' ); // 定义Widgets相关的函数
require( ABSPATH . WPINC . '/class-wp-widget.php' ); // 定义注册小部件继承的核心基类
require( ABSPATH . WPINC . '/class-wp-widget-factory.php' ); // 定义WP_Widget_Factory类，注册和实例化WP_Widget类的单例。
require( ABSPATH . WPINC . '/nav-menu.php' ); // 定义导航菜单函数
require( ABSPATH . WPINC . '/nav-menu-template.php' ); // 定义导航菜单模板标签
require( ABSPATH . WPINC . '/admin-bar.php' ); // 定义顶部工具的相关函数
require( ABSPATH . WPINC . '/rest-api.php' ); // 定义REST API函数
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-server.php' ); // 用于实现WordPress REST API服务器的核心类。
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-response.php' ); // 用于实现REST响应对象的核心类。
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-request.php' );  // 用于实现REST请求对象的核心类。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-controller.php' ); // 用于管理REST API项目并与其交互的核心控制器基类。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-posts-controller.php' ); // 通过REST API访问文章的核心类。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-attachments-controller.php' ); // 用于通过REST API访问附件的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-types-controller.php' ); // 用于通过REST API访问文章类型的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-statuses-controller.php' ); // 用于通过REST API访问帖子状态的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-revisions-controller.php' ); // 用于通过REST API访问版本的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-taxonomies-controller.php' ); // 用于通过REST API访问分类法的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-terms-controller.php' ); // 用于通过REST API访问具体分类的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-users-controller.php' ); // 用于通过REST API访问用户的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-comments-controller.php' ); // 用于通过REST API访问评论的核心控制器。
require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-settings-controller.php' ); // 用于通过REST API访问设置的核心控制器。
require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-meta-fields.php' ); // 用于通过REST API访问元数据的核心控制器。
require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-comment-meta-fields.php' ); // 用于通过REST API访问评论元数据的核心控制器。
require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-post-meta-fields.php' ); // 用于通过REST API访问文章元数据的核心控制器。
require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-term-meta-fields.php' ); // 用于通过REST API访问具体分类的元数据的核心控制器。
require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-user-meta-fields.php' ); // 用于通过REST API访问用户元数据的核心控制器。

$GLOBALS['wp_embed'] = new WP_Embed(); // 将富媒体（如视频和图像）嵌入到内容中的封装类

// Load multisite-specific files.
if ( is_multisite() ) {
	require( ABSPATH . WPINC . '/ms-functions.php' );
	require( ABSPATH . WPINC . '/ms-default-filters.php' );
	require( ABSPATH . WPINC . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
wp_plugin_directory_constants(); // 定义插件相关的常量

$GLOBALS['wp_plugin_paths'] = array();

// Load must-use plugins.
foreach ( wp_get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
	foreach ( wp_get_active_network_plugins() as $network_plugin ) {
		wp_register_plugin_realpath( $network_plugin );
		include_once( $network_plugin );
	}
	unset( $network_plugin );
}

/**
 * 一旦所有必须使用和跨站点的激活的插件被加载，就会触发。
 *
 * @since 2.8.0
 */
do_action( 'muplugins_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded.
wp_cookie_constants();

// Define and enforce our SSL constants
wp_ssl_constants();

// Create common globals.
require( ABSPATH . WPINC . '/vars.php' );

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types(); // 创建帖子类型文章

wp_start_scraping_edited_file_errors();

// Register the default theme directory root
register_theme_directory( get_theme_root() ); // 检索并注册主题目录下的所有可用的主题

// Load active plugins.
foreach ( wp_get_active_and_valid_plugins() as $plugin ) { // 检索一组活动的和有效的插件文件。
	wp_register_plugin_realpath( $plugin ); // 注册插件目录下的所有可用的插件
	include_once( $plugin ); // 加载所有插件相关的文件
}
unset( $plugin );

// Load pluggable functions.
require( ABSPATH . WPINC . '/pluggable.php' ); // 加载可插拔函数，这些函数可以在插件中覆盖
require( ABSPATH . WPINC . '/pluggable-deprecated.php' ); // 加载过时的可插拔函数，用于向后兼容

// Set internal encoding.
wp_set_internal_encoding();

// Run wp_cache_postload() if object cache is enabled and the function exists.
if ( WP_CACHE && function_exists( 'wp_cache_postload' ) )
	wp_cache_postload(); // ??

/**
 * 一旦激活插件加载完成，就会触发该动作。此时可以使用可插拔函数
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
wp_functionality_constants(); // 定义功能相关的WordPress常量

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
wp_magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * WordPress Query object
 * @global WP_Query $wp_the_query
 * @since 2.0.0
 */
$GLOBALS['wp_the_query'] = new WP_Query();

/**
 * Holds the reference to @see $wp_the_query
 * Use this global for WordPress queries
 * @global WP_Query $wp_query
 * @since 1.5.0
 */
$GLOBALS['wp_query'] = $GLOBALS['wp_the_query']; // 保留对$ wp_the_query的引用,使用此全局变量进行查询

/**
 * Holds the WordPress Rewrite object for creating pretty URLs
 * @global WP_Rewrite $wp_rewrite
 * @since 1.5.0
 */
$GLOBALS['wp_rewrite'] = new WP_Rewrite();

/**
 * WordPress Object
 * @global WP $wp
 * @since 2.0.0
 */
$GLOBALS['wp'] = new WP(); // WordPress应用运行环境类

/**
 * WordPress Widget Factory Object
 * @global WP_Widget_Factory $wp_widget_factory
 * @since 2.8.0
 */
$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();

/**
 * WordPress User Roles
 * @global WP_Roles $wp_roles
 * @since 2.0.0
 */
$GLOBALS['wp_roles'] = new WP_Roles();

/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_theme' ); // 在主题被加载前触发

// Define the template related constants.
wp_templating_constants(  ); // 定义模板相关的常量

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale(); // 获取区域
$locale_file = WP_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file ); // 加载
unset( $locale_file );

/**
 * WordPress Locale object for loading locale domain date and various strings.
 * @global WP_Locale $wp_locale
 * @since 2.1.0
 */
$GLOBALS['wp_locale'] = new WP_Locale(); // 存储一个语言环境的翻译数据。

/**
 *  WordPress Locale Switcher object for switching locales.
 *
 * @since 4.7.0
 *
 * @global WP_Locale_Switcher $wp_locale_switcher WordPress locale switcher object.
 */
$GLOBALS['wp_locale_switcher'] = new WP_Locale_Switcher(); // 切换区域的类
$GLOBALS['wp_locale_switcher']->init();

// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! wp_installing() || 'wp-activate.php' === $pagenow ) { // 加载并执行主题中的function.php文件
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the theme is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_theme' ); // 主题加载完成后触发的动作

// Set up current user.
$GLOBALS['wp']->init();  // 用户认证并实例化，设置$current_user全局变量为当前用户对象

/**
 * 在WordPress加载完毕之后，发送任何头文件之前触发。
 *
 * WP在这个阶段大部分被加载，且用户被认证。WP继续使用init钩子来加载，如widgets，然后
 * 并且许多插件出于各种原因（例如，它们需要用户，分类等）在其上进行实例化。
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * 一旦WP所有插件和主题完全加载和实例化，就会触发此钩子。
 *
 * Ajax请求应该使用wp-admin/admin-ajax.php。admin-ajax.php可以处理未登录用户的请求。
 *
 * @link https://codex.wordpress.org/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action( 'wp_loaded' );
