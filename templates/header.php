<?php
$settings = get_option('dmy_link_settings');
$user_logo = isset($settings['dmy_link_logo']) ? $settings['dmy_link_logo'] : '';
$custom_logo_id = get_theme_mod('custom_logo');
$wp_logo = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';

if (!empty($user_logo)) {
    // 用户上传的logo优先级最高
    $logourl = esc_url($user_logo);
} elseif (is_zibll_themes()) {
    $theme_logo = function_exists('_pz') ? _pz('logo_src') : '';
    $logourl = esc_url($theme_logo ? $theme_logo : $wp_logo);
} elseif (!empty($wp_logo)) {
    $logourl = esc_url($wp_logo);
} else {
    $logourl = esc_url(plugins_url('/assets/img/hint.png', dirname(__FILE__)));
}
?>
<!DOCTYPE html>
<html class="dmy-overall-html">
<head>
    <title><?php echo esc_html(sprintf(__('即将离开%s', 'dmylink'), get_bloginfo('name'))); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel='stylesheet' href='<?php echo esc_url($css_url); ?>' type='text/css'/>
    <?php if (!empty($ad_css_url)) : ?>
    <link rel='stylesheet' href='<?php echo esc_url($ad_css_url); ?>' type='text/css'/>
    <?php endif; ?>
    <?php if (!empty($ad_inline_css)) : ?>
    <style id="dmylink-ad-custom"><?php echo $ad_inline_css; // 已在 dmy_link_ad_inline_style() 内清洗 ?></style>
    <?php endif; ?>
    <?php //wp_head(); ?>
</head>
<body class="dmy-overall-body">
    <div class="dmy-logo-box"></div>
