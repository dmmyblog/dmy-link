<?php
/**
 * 大绵羊外链跳转模板(优化版)
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

// 确保样式表加载
$css_file = plugin_dir_path(__FILE__) . 'css/' . $style . '.css';
$css_url = plugin_dir_url(__FILE__) . 'css/' . $style . '.css';

// 检查文件是否存在，不存在则使用默认样式
if (!file_exists($css_file)) {
    $style = 'dmylink-default';
    $css_file = plugin_dir_path(__FILE__) . 'css/' . $style . '.css';
    $css_url = plugin_dir_url(__FILE__) . 'css/' . $style . '.css';
}

// 仅当文件存在时才加载样式
if (file_exists($css_file)) {
    wp_enqueue_style('dmylink-template-style', $css_url, array(), filemtime($css_file));
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

// 加载页面底部
wp_footer();
?>
</body>
</html>
