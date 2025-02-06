<?php
/*
Plugin Name: 大绵羊外链跳转插件
Description: 大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
Version: 1.2.0
Author: 大绵羊
Author URI: https://dmyblog.cn
*/

// 引入 Codestar Framework 进行插件设置
require_once plugin_dir_path(__FILE__) . 'codestar-framework/codestar-framework.php';
require_once plugin_dir_path(__FILE__) . 'codestar-framework/admin-settings/dmylink-settings.php';

// 加载 CSS 样式
function dmy_link_enqueue_styles() {
    wp_enqueue_style('dmylink-csf-css', plugin_dir_url(__FILE__) . 'css/dmylink.css', array(), '1.0', 'all');
    
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

// 生成 16 位随机字符串
function generate_random_string($length = 16) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string . '_' . time(); 
}

// 拦截所有外部链接并生成随机跳转 Key
function dmy_link_intercept_links($content) {
    return preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/i', function($matches) {
        $url = $matches[1];

        // 检查是否是内部链接或白名单链接
        if (!is_internal_link($url) && !is_whitelisted_link($url, 'dmy_link_settings')) {
            $random_key = generate_random_string(16);
            $settings = get_option('dmy_link_settings');
            $expiration = isset($settings['dmy_link_expiration']) ? intval($settings['dmy_link_expiration']) : 5; // 默认过期时间为5分钟
            set_transient('dmy_link_' . $random_key, $url, $expiration * 60); // 存储时间

            return '<a href="' . esc_url(home_url('/redirect?a=' . $random_key)) . '" target="_blank">';
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

function dmy_link_redirect() {
    if (isset($_GET['a'])) {
        $random_key = sanitize_text_field($_GET['a']);
        $link = get_transient('dmy_link_' . $random_key);

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
    add_rewrite_rule('^redirect/?$', 'index.php?redirect=1', 'top');
}
add_action('init', 'dmy_link_rewrite_rules');

// 添加查询变量
function dmy_link_query_vars($vars) {
    $vars[] = 'redirect';
    return $vars;
}
add_filter('query_vars', 'dmy_link_query_vars');

// 处理重定向逻辑
function dmy_link_template_redirect() {
    if (get_query_var('redirect') == 1) {
        dmy_link_redirect();
    }
}
add_action('template_redirect', 'dmy_link_template_redirect');
