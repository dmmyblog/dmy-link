<?php
/*
Plugin Name: 大绵羊外链跳转插件
Description: 大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
Version: 1.0
Author: 大绵羊
*/

require_once plugin_dir_path(__FILE__) . 'codestar-framework/codestar-framework.php';
require_once plugin_dir_path(__FILE__) . 'codestar-framework/admin-settings/dmylink-settings.php';
function dmy_link_enqueue_styles() {
    wp_enqueue_style('dmylink-csf-css', plugin_dir_url(__FILE__) . 'css/dmylink.css', array(), '1.0', 'all');
    $settings = get_option('dmy_link_settings');
    $selected_style = isset($settings['dmy_link_style']) ? $settings['dmy_link_style'] : 'dmylibk-default';
    if ($selected_style) {
        $css_file_path = plugin_dir_path(__FILE__) . 'css/' . $selected_style . '.css';
        if (file_exists($css_file_path)) {
            wp_enqueue_style('dmylink-custom-style', plugin_dir_url(__FILE__) . 'css/' . $selected_style . '.css', array(), filemtime($css_file_path), 'all');
        } else {
            error_log("CSS file not found: " . $css_file_path);
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

// 拦截所有外部链接
function dmy_link_intercept_links($content) {
    return preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/i', function($matches) {
        $url = $matches[1];
        if (!is_internal_link($url) && !is_whitelisted_link($url, 'dmy_link_settings')) {
            $encoded_url = base64_encode($url);
            return '<a href="' . esc_url(add_query_arg('a', $encoded_url, home_url('/redirect'))) . '" target="_blank">';
        }
        return $matches[0];
    }, $content);
}
add_filter('the_content', 'dmy_link_intercept_links');


function is_internal_link($url) {
    $parsed_url = parse_url($url);
    $home_url = parse_url(home_url());
    return isset($parsed_url['host']) && $parsed_url['host'] === $home_url['host'];
}

// 检查链接是否在白名单中
function is_whitelisted_link($url, $option_name) {
    $whitelist = get_option($option_name)['dmy_link_whitelist'];
    $whitelist = explode("\n", $whitelist);

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

// 显示提示页面
function dmy_link_redirect() {
    if (isset($_GET['a'])) {
        $encoded_link = $_GET['a'];
        $link = base64_decode($encoded_link);
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
