<?php
/**
 * Created by PhpStorm.
 * User: lzh
 * Date: 2017/12/17
 * Time: 17:28
 */

// 卸载插件执行的动作

// If uninstall not called from WordPress exit
if( !defined( ‘WP_UNINSTALL_PLUGIN’ ) )
    exit ();
// Delete option from options table
delete_option( 'lzh_myplugin_options' );
//remove any additional options and custom tables
