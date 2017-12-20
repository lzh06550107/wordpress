<?php

/**
 * 当插件卸载时启动。
 *
 * 编辑本脚本时需要考虑遵循如下控制流：
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 * - 方法应该是静态的；
 * - 检查$_REQUEST内容是否是插件的名称；
 * - 进行管理员身份验证；
 * - 验证$ _GET的输出是合理的；
 *
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 */

// 如果直接调用本脚本，则直接退出。
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
