<?php
/**
 * 引导文件，用于设置ABSPATH常量并加载wp-config.php文件，
 * 然后加载wp-config.php文件，wp-config.php文件将加载wp-settings.php文件来设置WordPress环境。
 *
 * 如果没有找到wp-config.php文件，则会显示一个错误，要求访问者设置wp-config.php文件。
 *
 * 同样会在WordPress的父目录中搜索wp-config.php，以允许WordPress目录保持不变。
 *
 * @package WordPress
 */

/** 定义ABSPATH作为这个文件的目录，相当于设置wordpress的根目录 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/*
 * 如果在WordPress根目录存在wp-config.php文件，或者wp-config.php存在web根目录而wp-settings.php不存在，则加载该wp-config.php.。
 * 检查wp-setting.php不存在是为了避免当前目录是嵌套安装。如果条件都不成立，则开始设置进程。

 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'wp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another installation */
	require_once( dirname( ABSPATH ) . '/wp-config.php' );

} else {

	// A config file doesn't exist

	define( 'WPINC', 'wp-includes' );
	require_once( ABSPATH . WPINC . '/load.php' );

    // 跨web服务器的配置标准化$_SERVER变量
	wp_fix_server_vars();

	require_once( ABSPATH . WPINC . '/functions.php' );

	$path = wp_guess_url() . '/wp-admin/setup-config.php'; // 加载配置文件的脚本


	 // 如果不是访问setup-config的URI，则重定向到配置路径，防止无限循环
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	require_once( ABSPATH . WPINC . '/version.php' );

	// 检查PHP和Mysql版本
	wp_check_php_mysql_versions();
	// 加载翻译函数
	wp_load_translations_early();

	// Die with an error message
	$die  = sprintf(
		/* translators: %s: wp-config.php */
		__( "There doesn't seem to be a %s file. I need this before we can get started." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: Codex URL */
		__( "Need more help? <a href='%s'>We got it</a>." ),
		__( 'https://codex.wordpress.org/Editing_wp-config.php' )
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: wp-config.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	wp_die( $die, __( 'WordPress &rsaquo; Error' ) );
}
