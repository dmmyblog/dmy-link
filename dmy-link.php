<?php
/*
Plugin Name: 大绵羊外链跳转插件
Description: 大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
Version: 1.2.0
Author: 大绵羊
Author URI: https://dmyblog.cn
*/

// 定义插件URL常量
if (!defined('DMY_LINK_URL')) {
    define('DMY_LINK_URL', plugin_dir_url(__FILE__));
}

// 引入 Codestar Framework 进行插件设置
require_once plugin_dir_path(__FILE__) . 'codestar-framework/codestar-framework.php';
require_once plugin_dir_path(__FILE__) . 'codestar-framework/admin-settings/dmylink-settings.php';

// 加载 CSS 样式
function dmy_link_enqueue_styles() {
    // wp_enqueue_style('dmylink-csf-css', plugin_dir_url(__FILE__) . 'css/dmylink.css', array(), '1.0', 'all');
    
    $settings = get_option('dmy_link_settings');
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

// 拦截所有外部链接并生成跳转Key
function dmy_link_intercept_links($content) {
    return preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/i', function($matches) {
        $url = $matches[1];

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
            
            return '<a href="' . esc_url(home_url('/dinterception?a=' . $encrypted_key)) . '" target="_blank">';
        }
        return $matches[0];
    }, $content);
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
// 部分代码是不使用的老代码/在部分情况可以触发
function dmy_link_redirect() {
    if (isset($_GET['a'])) {
        $encrypted_key = sanitize_text_field($_GET['a']);
        $link = get_transient('dmy_link_' . $encrypted_key);
        
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
    add_rewrite_rule('^dinterception/?$', 'index.php?dinterception=1', 'top');
}
add_action('init', 'dmy_link_rewrite_rules');

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

    wp_send_json_success(array('url' => home_url("/dinterception?a=" . urlencode($encrypted_key))));
}
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'dmylink-circle',
        plugin_dir_url( __FILE__ ) . 'js/dmylink-circle.js',
        array(),            
        '1.0.0',
        true                
    );
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
