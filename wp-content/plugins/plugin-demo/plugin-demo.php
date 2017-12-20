<?php

/**
 * 这是插件的启动文件
 *
 * 该文件被WordPress读取用来生产插件管理区域的插件信息的。该文件同样包括
 * 所有被插件使用的依赖文件、注册激活和停用函数以及顶一个开始插件的函数。
 *
 * Plugin Name:       WordPress Plugin Boilerplate
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// 如果直接调用本脚本，则直接退出
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * 当前插件的版本
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * 在插件激活期间运行的函数。
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-activator.php';
	Plugin_Name_Activator::activate();
}

/**
 * 在插件停用期间调用的函数。
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * 用来定义特定于管理员的挂钩以及面向公众的站点挂钩的核心插件类
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name.php';

/**
 * 开始执行插件。
 *
 * 由于插件中的所有内容都是通过钩子注册的，所以从文件中的这一点开始插件不会影响页面的生命周期。
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Plugin_Name();
	$plugin->run();

}
run_plugin_name();
