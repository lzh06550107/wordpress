<?php
/**
 * 加载WordPress环境和模板
 *
 * @package WordPress
 */

// wp_did_header变量，相当于一个flag，确保每次刷新时，wp-blog-header.php文件只执行一次。
if ( !isset($wp_did_header) ) {

	$wp_did_header = true;

	// 加载WordPress库。
	require_once( dirname(__FILE__) . '/wp-load.php' );

	// 设置WordPress查询。
	wp();

	// 加载主题模板。
	require_once( ABSPATH . WPINC . '/template-loader.php' );

}
