<?php
/**
 * 大绵羊外链跳转模板
 * 
 * 安全加载跳转页面模板，包含错误处理和缓存机制
 */

// 缓存设置数据
static $settings = null;
if ($settings === null) {
    $settings = get_option('dmy_link_settings');
}

// 获取风格标识
static $style = null;
if ($style === null) {
    $style = isset($settings['dmy_link_style']) ? 
             sanitize_text_field($settings['dmy_link_style']) : 
             'dmylink-default';
}

// 定义风格模板映射
define('DMYLINK_TEMPLATES', [
    'dmylink-bilibili' => 'bilibili-style.php',
    'dmylink-tencent'  => 'tencent-style.php',
    'dmylink-csdn'     => 'csdn-style.php',
    'dmylink-zhihu'    => 'zhihu-style.php',
    'dmylink-jump'     => 'jump-style.php',
    'dmylink-default'  => 'default-style.php',
    'dmylink-moxing'   => 'moxing-style.php',
    'dmylink-tiktok'   => 'tiktok-style.php'
]);

// 风格标识必须落在已知白名单内，避免任意路径拼接
if (!isset(DMYLINK_TEMPLATES[$style])) {
    $style = 'dmylink-default';
}

// 确保样式表加载
$css_file = plugin_dir_path(__FILE__) . 'css/' . $style . '.css';
$css_url = plugin_dir_url(__FILE__) . 'css/' . $style . '.css';

// 检查文件是否存在，不存在则使用默认样式
if (!file_exists($css_file)) {
    $style = 'dmylink-default';
    $css_file = plugin_dir_path(__FILE__) . '/css/' . $style . '.css';
    $css_url = plugin_dir_url(__FILE__) . '/css/' . $style . '.css';
}

// 仅当文件存在时才加载样式
if (file_exists($css_file)) {
    wp_enqueue_style('dmylink-template-style', $css_url, array(), filemtime($css_file));
}

// 广告位 / 倒计时样式：仅在启用时由 header.php 输出
$ad_css_url    = '';
$ad_inline_css = '';
if (function_exists('dmy_link_ad_assets_needed') && dmy_link_ad_assets_needed($settings)) {
    $ad_css_file = plugin_dir_path(__FILE__) . 'css/dmylink-ad.css';
    if (file_exists($ad_css_file)) {
        $ad_css_url = add_query_arg('ver', filemtime($ad_css_file), plugin_dir_url(__FILE__) . 'css/dmylink-ad.css');
    }
    // 外观自定义（宽度 / 颜色 / 圆角 / 自定义 CSS）
    $ad_inline_css = dmy_link_ad_inline_style($settings);
}

// 安全加载头部模板
$header_file = plugin_dir_path(__FILE__) . 'templates/header.php';
if (file_exists($header_file)) {
    include_once $header_file;
} else {
    // 头部模板缺失的fallback
    get_header();
    echo '<div class="container">';
}

// 「提示框上方」的广告位（未启用或位置不是顶部时不输出）
if (function_exists('dmy_link_render_ad_slot') && isset($link)) {
    dmy_link_render_ad_slot($link, $settings, 'top');
}

// 确定要加载的模板文件
$template_file = isset(DMYLINK_TEMPLATES[$style]) ? 
                DMYLINK_TEMPLATES[$style] : 
                DMYLINK_TEMPLATES['dmylink-default'];

// 安全加载内容模板
$template_path = plugin_dir_path(__FILE__) . 'templates/' . $template_file;
if (file_exists($template_path)) {
    // 添加模板加载调试信息
    if (WP_DEBUG) {
        error_log('Loading template: ' . $template_path);
    }
    include_once $template_path;
} else {
    // 模板缺失的fallback处理
    if (WP_DEBUG) {
        error_log('Template not found: ' . $template_path);
    }
    echo '<div class="alert alert-warning">';
    echo '<p>'.__('跳转页面加载失败，请稍后再试。', 'dmylink').'</p>';
    echo '<p>'.__('当前样式: ', 'dmylink') . esc_html($style) . '</p>';
    echo '</div>';
}

// 倒计时条 + 「提示框下方 / 底部悬浮」的广告位 + 脚本（未启用时不输出任何内容）
if (function_exists('dmy_link_render_ad_slot') && isset($link)) {
    dmy_link_render_ad_slot($link, $settings, 'after');
}

// 加载页面底部
//wp_footer();
?>
</div>
</body>
</html>