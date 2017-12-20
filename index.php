<?php
/**
 * WordPress应用前端。该文件仅仅是加载wp-blog-header.php和告诉WordPress加载主题。
 *
 * @package WordPress
 */

/**
 * 告诉WordPress加载WordPress主题并输出。
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** 加载WordPress环境和模板 */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );
