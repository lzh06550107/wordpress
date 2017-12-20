<?php

/*
Plugin Name: Wordpress Plugin
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: 叼毛专看的插件描述。
Version: 1.0
Author: lzh
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

//require_once(dirname(__FILE__).'include/settings.php');
require_once 'vendor/autoload.php';

class ReStructuredText_Plugin {
    static $domain = 'restructuredtext';
    //Version
    static $version = '1.0';
    static $settings = null;

    public function __construct() {
        register_activation_hook(__FILE__, array(__CLASS__, 'install'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        add_action('init', array($this, 'init' ) );
    }
    /*
    * Callback to install the plugin configurations.
    */
    static function install(){
        update_option(self::$domain."_version", self::$version);
    }
    /*
    * Callback to uninstall the plugin configurations.
    */
    static function uninstall(){
        delete_option(self::$domain."_version");
    }
    /*
    * Callback to connect all the hooks we need.
    */
    public function init() {

        // 注册Rst文章类型
        register_post_type('Rst',
            [
                'labels' => array(
                    'name' => 'Rst',
                    'singular_name' => 'Rst',
                    'add_new' => '添加新的Rst',
                    'add_new_item' => '添加新的Rst',
                    'edit_item' => '编辑Rst',
                    'new_item' => '创建Rst',
                    'view_item' => '查看Rst',
                    'search_items' => '搜索Rst',
                    'not_found' => '没有找到Rst',
                    'not_found_in_trash' => '在垃圾箱找不到Rst'
                ),
                'taxonomies' => array( 'post_tag','category' ),
                'public'      => true,
                'has_archive' => true,
            ]
        );


        //Allow translations
        //load_plugin_textdomain('restructuredtext', false, basename(dirname(__FILE__)).'/languages');
        /*add_filter('save_post', array($this, 'save_post'), 0, 3);
        add_filter('content_edit_pre', array($this, 'content_edit_pre'), 0, 2 );
        add_filter('user_can_richedit', array($this, 'user_can_richedit'), 0);
        add_action('admin_print_footer_scripts', array($this, 'quicktags_settings'));*/
    }

    /*
    * Callback called after a post is saved to update it's metadata with the
    * RST source code and it's content with the rendered RST.
    */
    public function save_post($post_ID, $post, $update){
        global $wpdb;
        if( $this->is_Restable($post->post_type) || ($post->post_type == 'revision' && $this->is_Restable($post->post_parent))){
            $post_ID = $post->ID;
            // Retrieve reST source.
            $source = $post->post_content;
            if (get_magic_quotes_gpc()){
                $source = stripslashes($source);
            }
            // Save source as meta.
            update_post_meta($post_ID, 'post_rst', $source);
            // Convert rst to html.
            $content = $this->rst_to_html($source);
            // Save to the Database
            $where = array( 'ID' => $post_ID );
            $wpdb->update($wpdb->posts, array( 'post_content' => $content), $where);
            clean_post_cache($post_ID);
            $post = get_post($post_ID);
        }
    }
    /*
    * Callback called to show post RST source instead of rendered content
    * inside the editor.
    */
    public function content_edit_pre($content, $post_id) {
        $post = get_post($post_id);
        $meta = null;
        if (
            $this->is_Restable($post->post_type)
            || ($post->post_type == 'revision' && $this->is_Restable($post->post_parent))
        ){
            $meta = get_metadata('post', $post->ID, 'post_rst');
            if (is_array($meta))
                $meta = $meta[0];
        }
        return $meta ?: $content;
    }
    /*
    * Callback to apply Quicktags customizations.
    */
    public function quicktags_settings($qtInit){
        if ( wp_script_is( 'quicktags' ) ) {
            $js = file_get_contents(dirname(__FILE__).'/quicktags.js');
            print_r("
            <script language='javascript' type='text/javascript'>
            $js
            </script>
            ");
        }
    }
    /*
    * Callback to disable TinyMce for restable post types.
    */
    public function user_can_richedit($bool){
        $screen = get_current_screen();
        $post_type = $screen->post_type;
        if($this->is_Restable($post_type))
            return false;
        return $bool;
    }
    /**
     * reStructuredText to HTML.
     *
     * @param array $source The reST source.
     *
     * @return Html content.
     */
    static function rst_to_html($source){
        // Get the rst2html path.
        $option = get_option(self::$domain, false);
        if (!$option) { return; }
        $bin = $option['rst2html_path'];
        $args = $option['rst2html_args'];
        $cmd = "{$bin} {$args}";
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) {
            return 'Error opening process.';
        }
        $stdin = $pipes[0];
        $stdout = $pipes[1];
        $stderr = $pipes[2];
        fwrite($stdin, $source);
        fflush($stdin);
        fclose($stdin);
        fflush($stdout);
        $content = stream_get_contents($stdout);
        fclose($stdout);
        fflush($stderr);
        $errors = stream_get_contents($stderr);
        fclose($stderr);
        $ret = proc_close($proc);
        if ($ret != 0) {
            $msg = "Command: {$cmd} <br/>\nExit code: {$ret} <br/>\n{$errors} <br/>\n{$content} <br/>\n";
            print_r($msg);
            die();
        }
        $content = preg_replace('/(.*)<\/body>.*/ms', '$1', $content);
        $content = preg_replace('/.*<body>[\n\s]+(.*)/ms', '$1', $content);
        $content = str_replace('<!-- more -->', '<!--more-->', $content);
        return $content;
    }
    /*
    * Function to determine if restructuredtext has been enabled for the current post_type.
    * If an integer is passed it assumed to be a post ID. Otherwise it assumed to be the
    * the post type.
    *
    * @param (int|string) post ID or post type name
    * @return (true|false). True if restructuredtext is enabled for this post type. False otherwise.
    * @since 1.0
    */
    function is_Restable($id_or_type){
        if(is_int($id_or_type))
            $type = get_post_type($id_or_type);
        else
            $type = esc_attr($id_or_type);
        $options = get_option(self::$domain);
        $savedtypes = (array) $options['post_types'];
        return in_array($type, $savedtypes);
    }
}
$rst = new ReStructuredText_Plugin();