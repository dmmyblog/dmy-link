<?php
/*
Plugin Name: 大绵羊外链跳转插件
Description: 大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
Version: 1.3.5
Author: Author: 大绵羊&天无神话
Author URI: https://dmyblog.cn
*/

if (!defined('ABSPATH')) {
    exit;
}

// 插件统一版本
function dmy_link_plugin_version()
{
    return "1.3.5";
}
$version = dmy_link_plugin_version();

// 定义插件路径常量
define('DMY_LINK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DMY_LINK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMY_LINK_URL', DMY_LINK_PLUGIN_URL);

// 判断当前主题是否是zibll主题或其子主题
function is_zibll_themes()
{
// 获取当前主题对象
    $current_theme = wp_get_theme();

// 检测当前主题是否是zibll主主题
    if ($current_theme->get_stylesheet() === "zibll") {
        return true;
    }

// 检测当前主题是否是zibll的子主题（父主题为zibll）
    if ($current_theme->get("Template") === "zibll") {
        return true;
    }

    // Neither // 都不是
    return false;
}

// 初始化所有功能
function dmy_link_init_functions() {
    // 全局配置变量
    global $dmy_link_config;
    $dmy_link_config = get_option("dmy_link_settings", []);
    
    // 记录CSF初始化状态的变量
    $csf_initialized = false;

    // 初始化CSF设置面板
    if (class_exists("CSF")) {
        $csf_initialized = dmy_link_init_csf_settings();
    } else {
        $csf_initialized = false;
    }

    // 添加备用菜单注册方式，确保在CSF无法正常工作时仍能显示插件入口
    if (!$csf_initialized) {
        if (!is_zibll_themes()) {
            add_action("admin_menu", "dmy_link_add_fallback_menu");
        }
    }
}
add_action('init', 'dmy_link_init_functions');

// CSF设置文件加载逻辑
if (is_zibll_themes()) {
    // 使用子比函数挂载
    require_once DMY_LINK_PLUGIN_DIR . "codestar-framework/admin-settings/dmylink-settings.php";
    add_action("zib_require_end", "dmy_link_settings");
} else {
    // 非子比引入必要文件
    $required_files = [
        "/codestar-framework/codestar-framework.php",
        "/codestar-framework/admin-settings/dmylink-settings.php",
    ];

    // 检查Codestar Framework是否已存在
    $csf_exists = class_exists("CSF");
    foreach ($required_files as $file) {
        $full_path = DMY_LINK_PLUGIN_DIR . $file;
        // 如果是Codestar框架文件且已存在，则跳过加载
        if (
            $file === "/codestar-framework/codestar-framework.php" &&
            $csf_exists
        ) {
            continue;
        }
        // 加载其他文件
        if (file_exists($full_path)) {
            require_once $full_path;
        } else {
            error_log("大绵羊外链插件错误：缺少必要文件 - " . $full_path);
        }
    }
}

// 备用菜单函数
function dmy_link_add_fallback_menu() {
    add_menu_page(
        "大绵羊外链跳转设置",
        "外链跳转",
        "manage_options",
        "dmy_link_fallback",
        "dmy_link_fallback_page",
        "dashicons-admin-links",
        58
    );
}

function dmy_link_fallback_page() {
    if (!current_user_can("manage_options")) {
        wp_die("您没有足够的权限访问此页面。");
    }

    $csf_loaded = class_exists("CSF") ? "已加载" : "未加载";
    echo '<div class="wrap">';
    echo "<h1>大绵羊外链跳转设置</h1>";
    echo '<div class="notice notice-warning">';
    echo "<p>检测到配置面板框架未正常加载，可能是文件缺失或损坏。</p>";
    echo "<p>CSF框架状态: " . esc_html($csf_loaded) . "</p>";
    echo "<p>请检查 <code>codestar-framework/</code> 文件夹是否存在且完整。</p>";
    echo "<p>如果问题持续存在，请重新安装插件。</p>";
    echo "</div>";
    echo "</div>";
}

// 初始化CSF设置
function dmy_link_init_csf_settings() {
    // 只有后台才执行此代码
    if (!is_admin()) {
        return false;
    }
    
    // 检查CSF是否可用
    if (!class_exists('CSF')) {
        return false;
    }
    
    // 调用设置函数
    if (function_exists('dmy_link_settings')) {
        dmy_link_settings();
        return true;
    }
    
    return false;
}



// 加载 CSS 样式
function dmy_link_enqueue_styles() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不加载样式
    }

    wp_enqueue_style('dmylink-csf-css', plugin_dir_url(__FILE__) . 'css/dmylink.css', array(), '1.0', 'all');
    
    $selected_style = isset($settings['dmy_link_style']) ? $settings['dmy_link_style'] : 'dmylink-default';

    if ($selected_style) {
        $css_file_path = plugin_dir_path(__FILE__) . 'css/' . $selected_style . '.css';
        if (file_exists($css_file_path)) {
            wp_enqueue_style('dmylink-custom-style', plugin_dir_url(__FILE__) . 'css/' . $selected_style . '.css', array(), filemtime($css_file_path), 'all');
        }
    }

    // 加载样式定义文件
    $style_file = plugin_dir_path(__FILE__) . 'styles/' . $selected_style . '.php';
    if (file_exists($style_file)) {
        include_once $style_file;
        $style_function = 'dmylink_' . str_replace('-', '_', $selected_style) . '_style';
        if (function_exists($style_function)) {
            call_user_func($style_function);
        }
    }
}
add_action('wp_enqueue_scripts', 'dmy_link_enqueue_styles');

// 统一URL加密函数
function dmy_link_encrypt_url($url) {
    $settings = get_option('dmy_link_settings');
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    
    if ($method === 'aes_encryption') {
        // AES加密方式（固定IV实现）
        $key = isset($settings['dmy_link_aes_key']) ? $settings['dmy_link_aes_key'] : '';
        if (empty($key)) {
            // 密钥未设置时使用随机字符串方式
            return generate_random_string(16);
        }
        
        // 使用密钥派生固定IV（确保相同URL生成相同加密结果）
        $iv = substr(hash('sha256', $key, true), 0, 16);
        $encrypted = openssl_encrypt($url, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    } else {
        // 随机字符串方式（默认）
        return generate_random_string(16);
    }
}

// 生成随机字符串（用于随机字符串方式）
function generate_random_string($length = 16) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string . '_' . time();
}

/**
 * 跳转页 slug 清洗
 */
function dmy_link_sanitize_slug($slug) {
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '') { $slug = 'dinterception'; }
    return $slug;
}

/**
 * 获取跳转页 slug（可在设置中自定义，默认 dinterception）
 */
function dmy_link_get_slug() {
    $settings = get_option('dmy_link_settings');
    $slug = isset($settings['dmy_link_slug']) ? $settings['dmy_link_slug'] : 'dinterception';
    return dmy_link_sanitize_slug($slug);
}

/**
 * 构造跳转链接（根据自定义 slug 生成）
 */
function dmy_link_build_redirect_url($encrypted_key) {
    $slug = dmy_link_get_slug();
    return esc_url(home_url('/' . $slug . '?a=' . urlencode($encrypted_key)));
}

// 拦截所有外部链接并生成跳转Key
function dmy_link_intercept_links($content) {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $content; // 开关关闭时返回原始内容
    }

    return preg_replace_callback(
        '/<a\s+([^>]*?)href="([^"]*)"([^>]*?)>/i', 
        function($matches) {
            $url = $matches[2];
            $beforeHref = $matches[1];
            $afterHref = $matches[3];

            // 检查是否是内部链接或白名单链接
            if (!is_internal_link($url) && !is_whitelisted_link($url, 'dmy_link_settings')) {
                $encrypted_key = dmy_link_encrypt_url($url);
                $settings = get_option('dmy_link_settings');
                
                // 根据加密方式设置过期时间
                $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
                
                if ($method === 'random_string') {
                    $expiration = isset($settings['dmy_link_expiration']) ? intval($settings['dmy_link_expiration']) : 5;
                    $expiration_time = $expiration * 60;
                    set_transient('dmy_link_' . $encrypted_key, $url, $expiration_time);
                } else {
                    // AES方式永不过期（0表示永不过期）
                    set_transient('dmy_link_' . $encrypted_key, $url, 0);
                }
                
                $newHref = dmy_link_build_redirect_url($encrypted_key);
                
                // 检查是否已有 target="_blank"
                if (!preg_match('/target\s*=\s*[\'"][^"\']*_blank[^"\']*[\'"]/i', $afterHref)) {
                    $afterHref .= ' target="_blank"';
                }
                
                return '<a ' . $beforeHref . 'href="' . $newHref . '"' . $afterHref . '>';
            }
            
            // 检查原始链接是否已有 target="_blank"
            if (!preg_match('/target\s*=\s*[\'"][^"\']*_blank[^"\']*[\'"]/i', $afterHref)) {
                $afterHref .= ' target="_blank"';
            }
            
            return '<a ' . $beforeHref . 'href="' . $url . '"' . $afterHref . '>';
        }, 
        $content
    );
}
add_filter('the_content', 'dmy_link_intercept_links');

// 判断是否是内部链接
function is_internal_link($url) {
    $parsed_url = parse_url($url);
    $home_url = parse_url(home_url());
    return isset($parsed_url['host']) && strcasecmp($parsed_url['host'], $home_url['host']) === 0;
}

// 检查链接是否在白名单
function is_whitelisted_link($url, $option_name) {
    $options = get_option($option_name);
    if (!isset($options['dmy_link_whitelist']) || !is_string($options['dmy_link_whitelist'])) {
        return false;
    }
    $whitelist = explode("\n", trim($options['dmy_link_whitelist']));

    $parsed_url = parse_url($url);
    $host_and_path = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $host_and_path .= isset($parsed_url['path']) ? $parsed_url['path'] : '';

    foreach ($whitelist as $whitelisted) {
        $whitelisted = trim($whitelisted);
        if (empty($whitelisted)) {
            continue;
        }

        $whitelisted_parsed = parse_url($whitelisted);
        $whitelisted_host_and_path = isset($whitelisted_parsed['host']) ? $whitelisted_parsed['host'] : '';
        $whitelisted_host_and_path .= isset($whitelisted_parsed['path']) ? $whitelisted_parsed['path'] : '';

        if ($whitelisted_host_and_path === '/') {
            if ($host_and_path === '/') {
                return true;
            }
        } else {
            if (strpos($host_and_path, $whitelisted_host_and_path) === 0) {
                return true;
            }
        }
    }

    return false;
}

//
// Referer 防护辅助函数
//
function dmy_is_same_site_referer($referer) {
    if (empty($referer)) {
        return false;
    }
    $parsed = parse_url($referer);
    $home   = parse_url(home_url());
    return isset($parsed['host'], $home['host']) && strcasecmp($parsed['host'], $home['host']) === 0;
}

function dmy_is_referer_whitelisted($referer, $settings) {
    if (empty($referer)) {
        return false;
    }
    if (!isset($settings['dmy_link_referer_whitelist']) || !is_string($settings['dmy_link_referer_whitelist'])) {
        return false;
    }
    $whitelist = explode("\n", trim($settings['dmy_link_referer_whitelist']));

    $parsed = parse_url($referer);
    $host_and_path = (isset($parsed['host']) ? $parsed['host'] : '') . (isset($parsed['path']) ? $parsed['path'] : '');

    foreach ($whitelist as $whitelisted) {
        $whitelisted = trim($whitelisted);
        if ($whitelisted === '') {
            continue;
        }
        // 允许仅填写域名，自动补全协议便于 parse_url
        $candidate = (strpos($whitelisted, '://') !== false) ? $whitelisted : ('https://' . $whitelisted);
        $w_parsed = parse_url($candidate);
        $w_host_and_path = (isset($w_parsed['host']) ? $w_parsed['host'] : '') . (isset($w_parsed['path']) ? $w_parsed['path'] : '');
        if ($w_host_and_path === '/') {
            if ($host_and_path === '/') {
                return true;
            }
        } else {
            if (strpos($host_and_path, $w_host_and_path) === 0) {
                return true;
            }
        }
    }
    return false;
}

// 部分代码是不使用的老代码/在部分情况可以触发
function dmy_link_redirect() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不处理重定向
    }


    if (isset($_GET['a'])) {
        // Referer 防护 禁止站外直接访问跳转页
        if (!empty($settings['dmy_link_referer_protect'])) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $allow_empty = !empty($settings['dmy_link_referer_allow_empty']);
            $is_safe = ($referer && (dmy_is_same_site_referer($referer) || dmy_is_referer_whitelisted($referer, $settings))) || (!$referer && $allow_empty);

            if (!$is_safe) {
                $home_url = home_url('/');
                $back_to_home_button = sprintf(
                    '<br><br><a href="%s" style="padding: 10px 20px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">返回首页</a>',
                    esc_url($home_url)
                );
                wp_die(
                    __('危险：禁止站外直接访问跳转页面', 'dmylink') . $back_to_home_button,
                    __('访问受限', 'dmylink'),
                    ['response' => 403, 'back_link' => false]
                );
            }
        }

        $encrypted_key = sanitize_text_field($_GET['a']);
        $link = get_transient('dmy_link_' . $encrypted_key);
        
        
        // 修复URL传输中+号被转换为空格的问题
            $encrypted_key = str_replace(' ', '+', $encrypted_key);
            error_log('Encrypted Key: ' . $encrypted_key);
        // 尝试AES解密（如果是AES加密的链接）
        if (!$link) {
            $settings = get_option('dmy_link_settings');
            if (isset($settings['dmy_link_verification_method']) && 
                $settings['dmy_link_verification_method'] === 'aes_encryption' &&
                !empty($settings['dmy_link_aes_key'])) {
                
                $key = $settings['dmy_link_aes_key'];
                // 使用密钥派生固定IV（与加密过程一致）
                $iv = substr(hash('sha256', $key, true), 0, 16);
                $encrypted = base64_decode($encrypted_key);
                $link = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            }
        }

        if (!$link) {
            // 返回首页的按钮
            $home_url = home_url('/'); 
            $back_to_home_button = sprintf(
                '<br><br><a href="%s" style="padding: 10px 20px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">返回首页</a>',
                esc_url($home_url)
            );

            // 显示错误信息
            wp_die(
                __('<span style="font-weight: 600; color: #d72c2cbd;">管理员:</span>拦截Token过期,你不可以在使用此Token,<span style="color: #d78d2cbd;">你可以刷新页面重新获取 </span><br> 如果刷新依旧看到这个页面请联系本站长处理', 'dmylink') . $back_to_home_button,
                __('拦截Token过期提示也有可能是wordpress出现错误', 'dmylink'), 
                ['response' => 404, 'back_link' => false]
            );
        }

        include_once(plugin_dir_path(__FILE__) . 'dmylink-template.php');
        exit;
    }
}
add_action('init', 'dmy_link_redirect');


// 添加重定向规则
function dmy_link_rewrite_rules() {
    $slug = dmy_link_get_slug();
    $pattern = '^' . preg_quote($slug, '/') . '/?$';
    add_rewrite_rule($pattern, 'index.php?dinterception=1', 'top');
}
add_action('init', 'dmy_link_rewrite_rules');

register_activation_hook(__FILE__, 'dmy_link_activate');
function dmy_link_activate() {
    // 激活时按照当前设置生成重写规则并刷新
    dmy_link_rewrite_rules();
    flush_rewrite_rules();
}

add_action('update_option_dmy_link_settings', 'dmy_link_maybe_flush_on_slug_change', 10, 3);
function dmy_link_maybe_flush_on_slug_change($old_value, $value, $option) {
    // 设置保存时，若 slug 发生变化则刷新固定链接
    $old_slug = isset($old_value['dmy_link_slug']) ? dmy_link_sanitize_slug($old_value['dmy_link_slug']) : 'dinterception';
    $new_slug = isset($value['dmy_link_slug']) ? dmy_link_sanitize_slug($value['dmy_link_slug']) : 'dinterception';
    if ($old_slug !== $new_slug) {
        dmy_link_rewrite_rules();
        flush_rewrite_rules();
    }
}

// 添加查询变量
function dmy_link_query_vars($vars) {
    $vars[] = 'dinterception';
    return $vars;
}
add_filter('query_vars', 'dmy_link_query_vars');

// 处理重定向逻辑
function dmy_link_template_redirect() {
    if (get_query_var('dinterception') == 1) {
        dmy_link_redirect();
    }
}
add_action('template_redirect', 'dmy_link_template_redirect');
// 注册WordPress原生AJAX处理
add_action('wp_ajax_dmylink_convert', 'dmylink_ajax_convert');
add_action('wp_ajax_nopriv_dmylink_convert', 'dmylink_ajax_convert');

function dmylink_ajax_convert() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        wp_send_json_error(array('message' => '插件已关闭'));
    }

    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

    // 站内或白名单直接放行
    if (is_internal_link($url) || is_whitelisted_link($url, 'dmy_link_settings')) {
        wp_send_json_success(array('url' => $url));
    }

    // 使用统一加密函数
    $encrypted_key = dmy_link_encrypt_url($url);
    $settings = get_option('dmy_link_settings');
    
    // 根据加密方式设置过期时间
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    
    if ($method === 'random_string') {
        $ttl = isset($settings['dmy_link_expiration']) ? (int)$settings['dmy_link_expiration'] : 5;
        set_transient('dmy_link_' . $encrypted_key, $url, $ttl * 60);
    } else {
        // AES方式永不过期
        set_transient('dmy_link_' . $encrypted_key, $url, 0);
    }

    wp_send_json_success(array('url' => dmy_link_build_redirect_url($encrypted_key)));
}


// 根据设置条件加载圈子或社区功能脚本
add_action( 'wp_enqueue_scripts', function () {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不加载脚本
    }

    // 检查启用的功能类型
    $enabled_type = '';
    $selector = '';
    
    if (isset($settings['dmy_link_function_type'])) {
        $enabled_type = $settings['dmy_link_function_type'];
        
        if ($enabled_type === 'circle') {
            $selector = isset($settings['dmy_link_circle_selector']) && !empty($settings['dmy_link_circle_selector']) 
                ? $settings['dmy_link_circle_selector'] 
                : '.topic-content';
        } elseif ($enabled_type === 'forums') {
            $selector = isset($settings['dmy_link_forums_selector']) && !empty($settings['dmy_link_forums_selector']) 
                ? $settings['dmy_link_forums_selector'] 
                : '.forum-article';
        }
    }
    
    // 如果启用了任一功能，则加载脚本
    if (!empty($enabled_type) && !empty($selector)) {
        wp_enqueue_script(
            'dmylink-circle',
            plugin_dir_url( __FILE__ ) . 'js/dmylink-circle.js',
            array(),            
            '1.0.1',
            true                
        );
        
        // 传递选择器设置到JavaScript
        wp_localize_script('dmylink-circle', 'dmylink_circle_config', array(
            'selector' => $selector,
            'ajax_url' => admin_url('admin-ajax.php'),
            'function_type' => $enabled_type
        ));
    }
} );

// 插件卸载时清理数据
function dmy_link_uninstall() {
    // 删除插件设置选项
    delete_option('dmy_link_settings');
    
    // 清理所有插件相关的transient数据
    global $wpdb;
    $transients = $wpdb->get_col(
        "SELECT option_name FROM $wpdb->options 
        WHERE option_name LIKE '_transient_dmy_link_%' 
        OR option_name LIKE '_transient_timeout_dmy_link_%'"
    );
    
    foreach ($transients as $transient) {
        $name = str_replace('_transient_', '', $transient);
        delete_transient($name);
    }
}
register_uninstall_hook(__FILE__, 'dmy_link_uninstall');


// 适配子比主题：接管评论链接和用户中心重定向
if (is_zibll_themes()) {
    // 卸载主题的评论链接重定向钩子
    remove_filter('get_comment_author_link', 'add_redirect_comment_link', 5);
    remove_filter('comment_text', 'add_redirect_comment_link', 99);
    remove_action('wp_ajax_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    remove_action('wp_ajax_nopriv_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    // 挂载插件的评论链接重定向钩子（优先级高于主题）
    add_filter('get_comment_author_link', 'dmy_add_redirect_comment_link', 6);
    add_filter('comment_text', 'dmy_add_redirect_comment_link', 100);
    add_action('wp_ajax_user_details_data_modal', 'dmy_zib_ajax_user_details_data_modal');
    add_action('wp_ajax_nopriv_user_details_data_modal', 'dmy_zib_ajax_user_details_data_modal');
}


/**
 * 插件的评论链接处理函数（替换主题的add_redirect_comment_link）
 */
function dmy_add_redirect_comment_link($text = '') {
    $settings = get_option('dmy_link_settings');
    // 若插件总开关关闭，直接返回原始内容
    if (empty($settings['dmy_link_enable'])) {
        return $text;
    }
    // 处理评论内容中的<a>标签链接
    return dmy_go_link($text);
}

/**
 * 插件的链接处理逻辑（替代主题的go_link）
 */
function dmy_go_link($text = '') {
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $text;
    }

    // 1. 处理纯链接（如评论作者链接，可能直接是URL而非<a>标签）
    if (preg_match('/^https?:\/\//', $text) && !preg_match('/<a.*?>/', $text)) {
        if (!is_internal_link($text) && !is_whitelisted_link($text, 'dmy_link_settings')) {
            return dmy_get_redirect_url($text);
        }
        return $text;
    }

    // 2. 处理带<a>标签的链接（如评论内容中的链接）
    preg_match_all("/<a(.*?)href=['\"](.*?)['\"](.*?)>/", $text, $matches);
    if ($matches) {
        foreach ($matches[2] as $val) {
            if (!is_internal_link($val) && !is_whitelisted_link($val, 'dmy_link_settings')) {
                $redirect_url = dmy_get_redirect_url($val);
                $text = str_replace(
                    array("href=\"$val\"", "href='$val'"),
                    "href=\"$redirect_url\"",
                    $text
                );
            }
        }
        // 统一添加target="_blank"（避免重复添加）
        foreach ($matches[0] as $a_tag) {
            if (!preg_match('/target=["\']_blank["\']/', $a_tag)) {
                $text = str_replace($a_tag, str_replace('<a', '<a target="_blank"', $a_tag), $text);
            }
        }
    }
    return $text;
}

/**
 * 生成插件的跳转链接（替代主题的zib_get_gourl）
 */
function dmy_get_redirect_url($url) {
    $encrypted_key = dmy_link_encrypt_url($url);
    // 存储链接（复用插件原有逻辑）
    $settings = get_option('dmy_link_settings');
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    if ($method === 'random_string') {
        $expiration = isset($settings['dmy_link_expiration']) ? intval($settings['dmy_link_expiration']) : 5;
        set_transient('dmy_link_' . $encrypted_key, $url, $expiration * 60);
    } else {
        set_transient('dmy_link_' . $encrypted_key, $url, 0); // AES模式永不过期
    }
    return dmy_link_build_redirect_url($encrypted_key);
}


//查看用户全部详细资料的模态框
function dmy_zib_ajax_user_details_data_modal()
{
    $user_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $user = get_userdata($user_id);
    if (!$user_id || empty($user->ID)) {
        zib_ajax_notice_modal('danger', '用户不存在或参数传入错误');
    }

    echo dmy_zib_get_user_details_data_modal($user_id);
    exit();
}


//获取用户详细资料
function dmy_zib_get_user_details_data_modal($user_id = '', $class = 'mb10 flex', $t_class = 'muted-2-color', $v_class = '')
{
    if (!$user_id) {
        return;
    }

    $current_id = get_current_user_id();
    $udata      = get_userdata($user_id);
    if (!$udata) {
        return;
    }

    $privacy = zib_get_user_meta($user_id, 'privacy', true);

    $datas = array(
        array(
            'title'   => '签名',
            'value'   => get_user_desc($user_id, false),
            'spare'   => '未知',
            'no_show' => false,
        ),
        array(
            'title'   => '注册时间',
            'value'   => get_date_from_gmt($udata->user_registered),
            'spare'   => '未知',
            'no_show' => false,
        ), array(
            'title'   => '最后登录',
            'value'   => get_user_meta($user_id, 'last_login', true),
            'spare'   => '未知',
            'no_show' => false,
        ), array(
            'title'   => '邮箱',
            'value'   => esc_attr($udata->user_email),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '性别',
            'value'   => esc_attr(get_user_meta($user_id, 'gender', true)),
            'spare'   => '保密',
            'no_show' => true,
        ), array(
            'title'   => '地址',
            'value'   => esc_textarea(zib_get_user_meta($user_id, 'address', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '个人网站',
            'value'   => dmy_zib_get_url_link($user_id), //修改
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => 'QQ',
            'value'   => esc_attr(zib_get_user_meta($user_id, 'qq', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '微信',
            'value'   => esc_attr(zib_get_user_meta($user_id, 'weixin', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '微博',
            'value'   => esc_url(zib_get_user_meta($user_id, 'weibo', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => 'Github',
            'value'   => esc_url(zib_get_user_meta($user_id, 'github', true)),
            'spare'   => '未知',
            'no_show' => true,
        ),
    );

    $lists = '';

    //用户认证
    if (_pz('user_auth_s', true)) {
        $auth_name = zib_get_user_auth_info_link($user_id, 'c-blue');
        $auth_name = $auth_name ? $auth_name : '未认证';
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">认证</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $auth_name . '</div>';
        $lists .= '</div>';
    }

    //用户徽章
    if (_pz('user_medal_s', true)) {
        $user_medal = zib_get_user_medal_show_link($user_id, '', 5);
        $user_medal = $user_medal ? $user_medal : '暂无徽章';

        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">徽章</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $user_medal . '</div>';
        $lists .= '</div>';
    }

    foreach ($datas as $data) {
        if (!is_super_admin() && $data['no_show'] && 'public' != $privacy && $current_id != $user_id) {
            if (('just_logged' == $privacy && !$current_id) || 'just_logged' != $privacy) {
                $data['value'] = '用户未公开';
            }
        }
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . $data['title'] . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . ($data['value'] ? $data['value'] : $data['spare']) . '</div>';
        $lists .= '</div>';
    }

    $header = '<div class="mb10 border-bottom touch" style="padding-bottom: 12px;">';
    $header .= '<button class="close ml10" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>';
    $header .= '<div class="" style="">';
    $header .= zib_get_post_user_box($user_id);
    $header .= '</div>';
    $header .= '</div>';

    $html = '<div class="mini-scrollbar scroll-y max-vh5 flex hh">' . $lists . '</div>';
    return $header . $html;
}


function dmy_zib_get_url_link($user_id, $class = 'focus-color')
{
    $user_url = get_userdata($user_id)->user_url;
    $url_name = zib_get_user_meta($user_id, 'url_name', true) ?: $user_url;
    $user_url = dmy_go_link($user_url, true);
    return $user_url ? '<a class="' . $class . '" href="' . esc_url($user_url) . '" target="_blank">' . esc_attr($url_name) . '</a>' : 0;
}
