<?php
/*
Plugin Name: 大绵羊外链跳转插件
Description: 大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
Version: 1.5.0
Author:  大绵羊
Author URI: https://dmyblog.cn
Plugin URI: https://github.com/dmmyblog/dmy-link
Text Domain: dmylink
Domain Path: /languages
License: GPL-3.0-or-later
*/


function dmy_redirect_add_author_link($plugin_meta, $plugin_file, $plugin_data) {
    // 仅作用于当前插件（通过插件文件路径匹配）
    if ($plugin_file !== plugin_basename(__FILE__)) {
        return $plugin_meta;
    }

    $new_author = '天无神话';
    $new_author_url = 'https://wxsnote.cn';

    $plugin_meta[] = '<a href="' . esc_url($new_author_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($new_author) . '</a>';

    return $plugin_meta;
}
add_filter('plugin_row_meta', 'dmy_redirect_add_author_link', 20, 3);

if (!defined('ABSPATH')) {
    exit;
}

// 插件统一版本
function dmy_link_plugin_version()
{
    return "1.5.0";
}
$version = dmy_link_plugin_version();

// 定义插件路径常量
define('DMY_LINK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DMY_LINK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMY_LINK_URL', DMY_LINK_PLUGIN_URL);

// 加载 GitHub Releases 自动更新
require_once DMY_LINK_PLUGIN_DIR . 'src/Update/GitHubReleaseUpdater.php';
DmyLink_GitHubReleaseUpdater::init(__FILE__, dmy_link_plugin_version());

// 加载翻译文件（此前缺失，所有 __(..., 'dmylink') 实际不生效）
function dmy_link_load_textdomain() {
    load_plugin_textdomain('dmylink', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'dmy_link_load_textdomain');

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
    add_action("after_setup_theme", "dmy_link_settings");
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
        59
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
    // 子比主题下由 after_setup_theme 钩子负责调用，这里跳过避免重复
    if (is_zibll_themes()) {
        return false;
    }

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

//
// ============ 安全核心：HMAC 签名令牌 & URL 判定（1.5.0） ============
//

// 令牌最长有效期（分钟），防止签发出长期可用的跳转链
if (!defined('DMY_LINK_MAX_TTL_MINUTES')) {
    define('DMY_LINK_MAX_TTL_MINUTES', 1440);
}

/**
 * 取签名密钥（首次调用时生成并落库，与 AES 旧密钥无关）
 */
function dmy_link_get_signing_key() {
    $key = get_option('dmy_link_signing_key');
    if (!is_string($key) || strlen($key) < 32) {
        $key = wp_generate_password(64, true, true);
        update_option('dmy_link_signing_key', $key, false);
    }
    return $key;
}

function dmy_link_b64url_encode($raw) {
    return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
}

function dmy_link_b64url_decode($str) {
    $str = strtr((string) $str, '-_', '+/');
    $pad = strlen($str) % 4;
    if ($pad) {
        $str .= str_repeat('=', 4 - $pad);
    }
    return base64_decode($str, true);
}

/**
 * 令牌有效期（秒），受 DMY_LINK_MAX_TTL_MINUTES 硬上限约束
 */
function dmy_link_get_ttl() {
    $settings = get_option('dmy_link_settings');
    // 旧版 AES 模式是「永不过期」，升级后给一个较长但有限的默认值，避免体验骤变
    $default = (isset($settings['dmy_link_verification_method']) && $settings['dmy_link_verification_method'] === 'aes_encryption') ? 1440 : 5;
    $minutes = isset($settings['dmy_link_expiration']) ? (int) $settings['dmy_link_expiration'] : $default;
    if ($minutes < 1) {
        $minutes = $default;
    }
    if ($minutes > DMY_LINK_MAX_TTL_MINUTES) {
        $minutes = DMY_LINK_MAX_TTL_MINUTES;
    }
    return $minutes * 60;
}

/**
 * 生成签名令牌：v1.<base64url(exp|url)>.<base64url(hmac)>
 * 完全无状态，不写数据库；过期时间取整到时间片，保证整页缓存内令牌稳定
 */
function dmy_link_sign_url($url) {
    $ttl  = dmy_link_get_ttl();
    $step = (int) apply_filters('dmy_link_token_time_step', 600);
    if ($step < 1) {
        $step = 1;
    }
    $exp     = (int) (ceil((time() + $ttl) / $step) * $step);
    $payload = dmy_link_b64url_encode($exp . '|' . $url);
    $sig     = dmy_link_b64url_encode(hash_hmac('sha256', $payload, dmy_link_get_signing_key(), true));

    return 'v1.' . $payload . '.' . $sig;
}

/**
 * 校验签名令牌，返回目标 URL 或 false
 */
function dmy_link_verify_token($token) {
    if (!is_string($token) || strncmp($token, 'v1.', 3) !== 0) {
        return false;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    $payload  = $parts[1];
    $sig      = $parts[2];
    $expected = dmy_link_b64url_encode(hash_hmac('sha256', $payload, dmy_link_get_signing_key(), true));
    if (!hash_equals($expected, $sig)) {
        return false;
    }

    $raw = dmy_link_b64url_decode($payload);
    if (!is_string($raw) || strpos($raw, '|') === false) {
        return false;
    }
    list($exp, $url) = explode('|', $raw, 2);
    if (!ctype_digit($exp) || (int) $exp < time()) {
        return false;
    }

    return dmy_link_is_http_url($url) ? $url : false;
}

/**
 * 归一化：解 HTML 实体、补全协议相对地址
 */
function dmy_link_prepare_url($raw) {
    $url = trim(html_entity_decode((string) $raw, ENT_QUOTES, 'UTF-8'));
    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    }
    return $url;
}

/**
 * 是否为可跳转的 http(s) 绝对地址（javascript:/data:/mailto: 等一律为 false）
 */
function dmy_link_is_http_url($url) {
    if (!is_string($url) || trim($url) === '') {
        return false;
    }
    $parsed = parse_url(trim($url));
    if (!is_array($parsed) || empty($parsed['host']) || empty($parsed['scheme'])) {
        return false;
    }
    $scheme = strtolower($parsed['scheme']);
    return ($scheme === 'http' || $scheme === 'https');
}

/**
 * host/path 规则匹配（域名要求完全相等或为子域，路径按 / 分段，杜绝前缀绕过）
 */
function dmy_link_matches_rule_list($url, $rules) {
    $parsed = parse_url(dmy_link_prepare_url($url));
    if (!is_array($parsed) || empty($parsed['host'])) {
        return false;
    }
    $host = strtolower($parsed['host']);
    $path = isset($parsed['path']) && $parsed['path'] !== '' ? $parsed['path'] : '/';

    foreach (preg_split('/[\r\n]+/', (string) $rules) as $rule) {
        $rule = trim($rule);
        if ($rule === '') {
            continue;
        }
        if (strpos($rule, '://') === false) {
            $rule = 'https://' . ltrim($rule, '/');
        }
        $r = parse_url($rule);
        if (!is_array($r) || empty($r['host'])) {
            continue;
        }
        $rule_host = strtolower($r['host']);
        $rule_path = isset($r['path']) ? rtrim($r['path'], '/') : '';

        // 关键：example.com 不得命中 example.com.evil.com
        if ($host !== $rule_host && substr($host, -(strlen($rule_host) + 1)) !== '.' . $rule_host) {
            continue;
        }
        if ($rule_path === '') {
            return true;
        }
        if ($path === $rule_path || strpos($path, $rule_path . '/') === 0) {
            return true;
        }
    }

    return false;
}

/**
 * 是否站内链接（锚点、查询串、相对路径为站内；非 http(s) 协议不算站内）
 */
function dmy_link_is_internal_url($url) {
    $url = dmy_link_prepare_url($url);
    if ($url === '' || $url[0] === '#' || $url[0] === '?') {
        return true;
    }
    $parsed = parse_url($url);
    if (!is_array($parsed)) {
        return true; // 解析不了就不动它
    }
    if (empty($parsed['host'])) {
        // 无 host：只有不带协议的相对路径才是站内
        return empty($parsed['scheme']);
    }
    $home = parse_url(home_url());
    return isset($home['host']) && strcasecmp($parsed['host'], $home['host']) === 0;
}

function dmy_link_is_whitelisted_url($url, $option_name = 'dmy_link_settings') {
    $options = get_option($option_name);
    if (!is_array($options) || empty($options['dmy_link_whitelist']) || !is_string($options['dmy_link_whitelist'])) {
        return false;
    }
    return dmy_link_matches_rule_list($url, $options['dmy_link_whitelist']);
}

/**
 * 统一的拦截判定：只有「http(s) + 站外 + 非白名单」才改写
 */
function dmy_link_should_intercept($url) {
    $url = dmy_link_prepare_url($url);
    if (!dmy_link_is_http_url($url)) {
        return false;
    }
    if (dmy_link_is_internal_url($url)) {
        return false;
    }
    return !dmy_link_is_whitelisted_url($url);
}

/**
 * 为改写后的外链补齐 target / rel，防止反向标签劫持
 */
function dmy_link_ensure_blank_attrs($attrs) {
    if (!preg_match('/\btarget\s*=/i', $attrs)) {
        $attrs .= ' target="_blank"';
    }
    if (preg_match('/\brel\s*=\s*(["\'])(.*?)\1/i', $attrs, $m)) {
        $rel = $m[2];
        foreach (array('noopener', 'noreferrer') as $token) {
            if (!preg_match('/\b' . $token . '\b/i', $rel)) {
                $rel = trim($rel . ' ' . $token);
            }
        }
        $attrs = str_replace($m[0], 'rel="' . esc_attr($rel) . '"', $attrs);
    } else {
        $attrs .= ' rel="noopener noreferrer"';
    }
    return $attrs;
}

/**
 * 客户端 IP（只认 REMOTE_ADDR，不信任任何可伪造的转发头）
 */
function dmy_link_get_client_ip() {
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

/**
 * 简易按 IP 限流，用于公开的 AJAX 接口
 */
function dmy_link_check_rate_limit($bucket, $limit = 120, $window = 300) {
    $limit = (int) apply_filters('dmy_link_rate_limit', $limit, $bucket);
    if ($limit <= 0) {
        return true;
    }
    $key  = 'dmy_rl_' . md5($bucket . '|' . dmy_link_get_client_ip());
    $hits = (int) get_transient($key);
    if ($hits >= $limit) {
        return false;
    }
    set_transient($key, $hits + 1, $window);
    return true;
}

/**
 * 带「返回首页」按钮的错误页（HTML 不进翻译字符串）
 */
function dmy_link_die_with_home($message, $title, $status) {
    $button = sprintf(
        '<br><br><a href="%s" style="padding:10px 20px;background-color:#0073aa;color:#fff;text-decoration:none;border-radius:5px;">%s</a>',
        esc_url(home_url('/')),
        esc_html__('返回首页', 'dmylink')
    );
    wp_die($message . $button, $title, array('response' => (int) $status, 'back_link' => false));
}

/**
 * 解析 ?a= 令牌，兼容 1.4.x 及更早签发的旧链接
 */
function dmy_link_resolve_token($token, $settings) {
    $url = dmy_link_verify_token($token);
    if ($url) {
        return $url;
    }

    // 兼容旧链接（可在设置中关闭）。旧的 AES 令牌永不过期，建议缓存刷新后关闭。
    if (isset($settings['dmy_link_legacy_token']) && empty($settings['dmy_link_legacy_token'])) {
        return false;
    }

    $legacy = str_replace(' ', '+', (string) $token);

    $stored = get_transient('dmy_link_' . $legacy);
    if ($stored && dmy_link_is_http_url($stored)) {
        return $stored;
    }

    if (!empty($settings['dmy_link_aes_key']) && function_exists('openssl_decrypt')) {
        $key = $settings['dmy_link_aes_key'];
        $iv  = substr(hash('sha256', $key, true), 0, 16);
        $raw = base64_decode($legacy, true);
        if ($raw !== false && $raw !== '' && strlen($raw) % 16 === 0) {
            $decrypted = openssl_decrypt($raw, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            if ($decrypted && dmy_link_is_http_url($decrypted)) {
                return $decrypted;
            }
        }
    }

    return false;
}

//
// 向后兼容的函数别名（旧名字过于通用，易与主题/插件冲突）
//
if (!function_exists('is_internal_link')) {
    function is_internal_link($url) {
        return dmy_link_is_internal_url($url);
    }
}
if (!function_exists('is_whitelisted_link')) {
    function is_whitelisted_link($url, $option_name = 'dmy_link_settings') {
        return dmy_link_is_whitelisted_url($url, $option_name);
    }
}
if (!function_exists('generate_random_string')) {
    function generate_random_string($length = 16) {
        return wp_generate_password((int) $length, false, false) . '_' . time();
    }
}

/**
 * @deprecated 1.5.0 保留函数名，内部改为 HMAC 签名，不再写数据库
 */
function dmy_link_encrypt_url($url) {
    return dmy_link_sign_url(dmy_link_prepare_url($url));
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
function dmy_link_build_redirect_url($token) {
    $slug = dmy_link_get_slug();
    // 返回未做 HTML 转义的 URL，由调用方按输出场景处理（HTML 用 esc_url，JSON 用原值）
    return esc_url_raw(home_url('/' . $slug . '?a=' . rawurlencode($token)));
}

// 拦截所有外部链接并生成跳转Key
function dmy_link_intercept_links($content) {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $content; // 开关关闭时返回原始内容
    }

    $result = preg_replace_callback(
        '/<a\s+([^>]*?)href\s*=\s*(["\'])(.*?)\2([^>]*?)>/i',
        function ($matches) {
            $beforeHref = $matches[1];
            $rawUrl     = $matches[3];
            $afterHref  = $matches[4];

            // 站内 / 白名单 / 非 http(s)（javascript:、mailto:、锚点等）一律保持原样
            if (!dmy_link_should_intercept($rawUrl)) {
                return $matches[0];
            }

            $url     = dmy_link_prepare_url($rawUrl);
            $newHref = dmy_link_build_redirect_url(dmy_link_sign_url($url));

            return '<a ' . $beforeHref . 'href="' . esc_url($newHref) . '"'
                 . dmy_link_ensure_blank_attrs($afterHref) . '>';
        },
        $content
    );

    // PCRE 回溯超限时会返回 null，此时必须回退原文，否则整篇内容会被清空
    return ($result === null) ? $content : $result;
}
add_filter('the_content', 'dmy_link_intercept_links');



//
// Referer 防护辅助函数
//
function dmy_link_get_request_referer() {
    if (empty($_SERVER['HTTP_REFERER']) || is_array($_SERVER['HTTP_REFERER'])) {
        return '';
    }

    return esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
}

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
    // 与外链白名单共用带边界的匹配，避免 example.com 命中 example.com.evil.com
    return dmy_link_matches_rule_list($referer, $settings['dmy_link_referer_whitelist']);
}

function dmy_link_is_allowed_referer($referer, $settings, $allow_empty = false) {
    if (empty($referer)) {
        return (bool) $allow_empty;
    }

    return dmy_is_same_site_referer($referer) || dmy_is_referer_whitelisted($referer, $settings);
}

function dmy_link_get_ajax_url_from_request() {
    if (empty($_POST['url']) || is_array($_POST['url'])) {
        return '';
    }

    return esc_url_raw(wp_unslash($_POST['url']));
}

// 跳转页处理：仅在前台且命中跳转页 slug 时接管
function dmy_link_redirect() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不处理重定向
    }

    // 只在前台跳转页生效：后台、AJAX、REST 一律不接管
    if (is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return;
    }
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    if (!isset($_GET['a']) || is_array($_GET['a'])) {
        return;
    }

    // Referer 防护：禁止站外直接访问跳转页
    if (!empty($settings['dmy_link_referer_protect'])) {
        $referer     = dmy_link_get_request_referer();
        $allow_empty = !empty($settings['dmy_link_referer_allow_empty']);
        if (!dmy_link_is_allowed_referer($referer, $settings, $allow_empty)) {
            dmy_link_die_with_home(
                esc_html__('危险：禁止站外直接访问跳转页面', 'dmylink'),
                esc_html__('访问受限', 'dmylink'),
                403
            );
        }
    }

    $token = sanitize_text_field(wp_unslash($_GET['a']));
    $link  = dmy_link_resolve_token($token, $settings);

    // 最终防线：只允许 http(s) 外链落到模板里
    if (!$link || !dmy_link_is_http_url($link)) {
        dmy_link_die_with_home(
            '<span style="font-weight:600;color:#d72c2c;">' . esc_html__('提示', 'dmylink') . '：</span>'
                . esc_html__('跳转链接已失效，请返回原页面刷新后重新点击。', 'dmylink'),
            esc_html__('跳转链接已失效', 'dmylink'),
            404
        );
    }

    include_once(plugin_dir_path(__FILE__) . 'dmylink-template.php');
    exit;
}


// 添加重定向规则
function dmy_link_rewrite_rules() {
    $slug = dmy_link_get_slug();
    $pattern = '^' . preg_quote($slug, '/') . '/?$';
    add_rewrite_rule($pattern, 'index.php?dinterception=1', 'top');
}
add_action('init', 'dmy_link_rewrite_rules');

register_activation_hook(__FILE__, 'dmy_link_activate');
function dmy_link_activate() {
    // 激活时生成签名密钥，并按当前设置生成重写规则
    dmy_link_get_signing_key();
    dmy_link_rewrite_rules();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'dmy_link_deactivate');
function dmy_link_deactivate() {
    // 停用时清掉跳转页重写规则，避免残留
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
        return;
    }

    // 重写规则未刷新时的兜底：请求路径正好等于跳转页 slug
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }
    $path = trim((string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/');
    if ($path !== '' && $path === dmy_link_get_slug()) {
        dmy_link_redirect();
    }
}
add_action('template_redirect', 'dmy_link_template_redirect');
// 注册WordPress原生AJAX处理
add_action('wp_ajax_dmylink_convert', 'dmylink_ajax_convert');
add_action('wp_ajax_nopriv_dmylink_convert', 'dmylink_ajax_convert');

function dmylink_ajax_convert() {
    // 公开的 URL 签名服务（注册了 nopriv）。Nginx 整页缓存下 PHP 输出的 nonce 会过期，
    // 因此不用 check_ajax_referer，改为「Referer 校验 + 按 IP 限流 + 令牌强制有限期」三重约束。
    $settings = get_option('dmy_link_settings');

    // 检查总开关状态
    if (empty($settings['dmy_link_enable'])) {
        wp_send_json_error(array('message' => __('插件已关闭', 'dmylink')), 403);
    }

    $referer = dmy_link_get_request_referer();
    if (!dmy_link_is_allowed_referer($referer, $settings, false)) {
        wp_send_json_error(array('message' => __('非法请求', 'dmylink')), 403);
    }

    if (!dmy_link_check_rate_limit('convert', 300, 300)) {
        wp_send_json_error(array('message' => __('请求过于频繁，请稍后再试', 'dmylink')), 429);
    }

    $url = dmy_link_get_ajax_url_from_request();
    if (empty($url) || !dmy_link_is_http_url($url) || !wp_http_validate_url($url)) {
        wp_send_json_error(array('message' => __('链接参数无效', 'dmylink')), 400);
    }

    // 站内或白名单直接放行
    if (!dmy_link_should_intercept($url)) {
        wp_send_json_success(array('url' => esc_url_raw($url)));
    }

    wp_send_json_success(array('url' => dmy_link_build_redirect_url(dmy_link_sign_url($url))));
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
            dmy_link_plugin_version(),
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
    delete_option('dmy_link_settings');
    delete_option('dmy_link_signing_key');
    delete_site_transient('dmy_link_latest_release');

    // 清理插件遗留的 transient（1.4.x 及更早的令牌、以及限流计数）
    global $wpdb;
    $rows = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '\_transient\_dmy\_link\_%'
            OR option_name LIKE '\_transient\_timeout\_dmy\_link\_%'
            OR option_name LIKE '\_transient\_dmy\_rl\_%'
            OR option_name LIKE '\_transient\_timeout\_dmy\_rl\_%'"
    );

    foreach ((array) $rows as $row) {
        $name = preg_replace('/^_transient_(timeout_)?/', '', $row);
        delete_transient($name);
    }

    flush_rewrite_rules();
}
register_uninstall_hook(__FILE__, 'dmy_link_uninstall');


// 适配子比主题：接管评论链接和用户中心重定向
// after_setup_theme 兼容子比 8.9+；zib_require_end 兜底处理后续自定义函数注册
if (is_zibll_themes()) {
    add_action('after_setup_theme', 'dmy_link_override_zibll_filters', 99);
    add_action('zib_require_end', 'dmy_link_override_zibll_filters', 99);
}

/**
 * 在主题加载完毕后，移除主题原版及自定义版的评论/用户模态框处理器，替换为插件版
 * 同时强制关闭子比主题的外链重定向功能，避免与插件冲突
 */
function dmy_link_override_zibll_filters() {
    // 强制关闭子比主题的外链重定向和外链重定向鉴权
    // _pz() 使用静态缓存无法从外部重置，因此同时用 _spz() 写入 option 确保下次请求生效
    if (function_exists('_pz') && function_exists('_spz')) {
        if (_pz('go_link_s')) {
            _spz('go_link_s', false);
        }
        if (_pz('go_link_nonce_s')) {
            _spz('go_link_nonce_s', false);
        }
    }

    // 移除主题原版评论链接处理
    remove_filter('get_comment_author_link', 'add_redirect_comment_link', 5);
    remove_filter('comment_text', 'add_redirect_comment_link', 99);
    // 移除主题自定义版（zidingyi 中可能注册的）
    remove_filter('get_comment_author_link', 'wxs_add_redirect_comment_link', 5);
    remove_filter('comment_text', 'wxs_add_redirect_comment_link', 99);

    // 移除子比主题 the_content 中的外链处理
    remove_filter('the_content', 'the_content_nofollow', 999);
    // 移除自定义版 the_content 外链处理
    if (function_exists('wxs_the_content_nofollow')) {
        remove_filter('the_content', 'wxs_the_content_nofollow', 999);
    }

    // 注册插件版评论链接处理
    add_filter('get_comment_author_link', 'dmy_add_redirect_comment_link', 6);
    add_filter('comment_text', 'dmy_add_redirect_comment_link', 100);

    // 移除主题原版用户详情模态框
    remove_action('wp_ajax_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    remove_action('wp_ajax_nopriv_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    // 移除主题自定义版用户详情模态框
    remove_action('wp_ajax_user_details_data_modal', 'wxs_zib_ajax_user_details_data_modal');
    remove_action('wp_ajax_nopriv_user_details_data_modal', 'wxs_zib_ajax_user_details_data_modal');

    // 注册插件版用户详情模态框
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
 * @param string $text 链接文本或含<a>标签的HTML
 * @param bool $link 为true时视为纯URL，直接返回跳转后的URL
 */
function dmy_go_link($text = '', $link = false) {
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $text;
    }

    // 纯链接模式：直接返回跳转URL或原URL
    if ($link) {
        return dmy_link_should_intercept($text) ? dmy_get_redirect_url($text) : $text;
    }

    // 1. 处理纯链接（如评论作者链接，可能直接是URL而非<a>标签）
    if (preg_match('/^https?:\/\//i', trim($text)) && !preg_match('/<a[\s>]/i', $text)) {
        return dmy_link_should_intercept($text) ? dmy_get_redirect_url($text) : $text;
    }

    // 2. 处理带<a>标签的链接（如评论内容中的链接）
    $result = preg_replace_callback(
        '/<a\s+([^>]*?)href\s*=\s*(["\'])(.*?)\2([^>]*?)>/i',
        function ($matches) {
            $beforeHref = $matches[1];
            $rawUrl     = $matches[3];
            $afterHref  = $matches[4];

            if (!dmy_link_should_intercept($rawUrl)) {
                return $matches[0];
            }

            $newHref = dmy_get_redirect_url(dmy_link_prepare_url($rawUrl));

            return '<a ' . $beforeHref . 'href="' . esc_url($newHref) . '"'
                 . dmy_link_ensure_blank_attrs($afterHref) . '>';
        },
        $text
    );

    return ($result === null) ? $text : $result;
}

/**
 * 生成插件的跳转链接（替代主题的zib_get_gourl）
 */
function dmy_get_redirect_url($url) {
    return dmy_link_build_redirect_url(dmy_link_sign_url(dmy_link_prepare_url($url)));
}


//查看用户全部详细资料的模态框
function dmy_zib_ajax_user_details_data_modal()
{
    // 公开接口（注册了 nopriv），限流以阻断按 id 批量枚举用户资料
    if (!dmy_link_check_rate_limit('user_modal', 60, 300)) {
        if (function_exists('zib_ajax_notice_modal')) {
            zib_ajax_notice_modal('danger', __('请求过于频繁，请稍后再试', 'dmylink'));
        }
        wp_die(esc_html__('请求过于频繁，请稍后再试', 'dmylink'), '', array('response' => 429));
    }

    $user_id = (!empty($_REQUEST['id']) && !is_array($_REQUEST['id'])) ? absint(wp_unslash($_REQUEST['id'])) : 0;

    $user = get_userdata($user_id);
    if (!$user_id || empty($user->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
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
            'title'   => __('签名', 'zib_language'),
            'value'   => get_user_desc($user_id, false),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ),
        array(
            'title'   => __('注册时间', 'zib_language'),
            'value'   => get_date_from_gmt($udata->user_registered),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ), array(
            'title'   => __('最后登录', 'zib_language'),
            'value'   => get_user_meta($user_id, 'last_login', true),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ), array(
            'title'   => __('邮箱', 'zib_language'),
            'value'   => esc_attr($udata->user_email),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('性别', 'zib_language'),
            'value'   => esc_attr(get_user_meta($user_id, 'gender', true)),
            'spare'   => __('保密', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('地址', 'zib_language'),
            'value'   => esc_textarea(zib_get_user_meta($user_id, 'address', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('个人网站', 'zib_language'),
            'value'   => dmy_zib_get_url_link($user_id), //修改
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('QQ', 'zib_language'),
            'value'   => esc_attr(zib_get_user_meta($user_id, 'qq', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('微信', 'zib_language'),
            'value'   => esc_attr(zib_get_user_meta($user_id, 'weixin', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('微博', 'zib_language'),
            'value'   => esc_url(zib_get_user_meta($user_id, 'weibo', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('Github', 'zib_language'),
            'value'   => esc_url(zib_get_user_meta($user_id, 'github', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ),
    );

    $lists = '';

    //用户认证
    if (_pz('user_auth_s', true)) {
        $auth_name = zib_get_user_auth_info_link($user_id, 'c-blue');
        $auth_name = $auth_name ? $auth_name : __('未认证', 'zib_language');
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . __('认证', 'zib_language') . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $auth_name . '</div>';
        $lists .= '</div>';
    }

    //用户徽章
    if (_pz('user_medal_s', true)) {
        $user_medal = zib_get_user_medal_show_link($user_id, '', 5);
        $user_medal = $user_medal ? $user_medal : __('暂无徽章', 'zib_language');

        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . __('徽章', 'zib_language') . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $user_medal . '</div>';
        $lists .= '</div>';
    }

    // 游客脱敏：未登录访客一律看不到敏感字段，防止匿名批量收集邮箱/QQ/微信
    $settings   = get_option('dmy_link_settings');
    $guard_guest = !isset($settings['dmy_link_userinfo_guard']) || !empty($settings['dmy_link_userinfo_guard']);

    foreach ($datas as $data) {
        $hidden = false;
        if ($guard_guest && !$current_id && $data['no_show']) {
            $hidden = true;
        } elseif (!is_super_admin() && $data['no_show'] && 'public' != $privacy && $current_id != $user_id) {
            if (('just_logged' == $privacy && !$current_id) || 'just_logged' != $privacy) {
                $hidden = true;
            }
        }
        if ($hidden) {
            $data['value'] = __('用户未公开', 'zib_language');
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
    $userdata = get_userdata($user_id);
    if (!$userdata) {
        return 0;
    }

    $user_url = $userdata->user_url;
    $url_name = zib_get_user_meta($user_id, 'url_name', true) ?: $user_url;
    $user_url = dmy_go_link($user_url, true);
    return $user_url ? '<a class="' . esc_attr($class) . '" href="' . esc_url($user_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($url_name) . '</a>' : 0;
}
