<?php
$settings = get_option('dmy_link_settings');
$user_logo = isset($settings['dmy_link_logo']) ? $settings['dmy_link_logo'] : '';

if (!empty($user_logo)) {
    // 用户上传的logo优先级最高
    $logourl = esc_url($user_logo);
} elseif (is_zibll_themes()) {
    // 如果没有用户logo且是子比主题，使用子比主题logo
    $logourl = _pz('logo_src');
} else {
    $logourl = plugins_url('/assets/img/hint.png', dirname(__FILE__));
}
?>
<!DOCTYPE html>
<html class="dmy-overall-html">
<head>
    <title>即将离开<?php echo get_bloginfo('name'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel='stylesheet' href='<?php echo $css_url; ?>' type='text/css'/>
    <?php 
    $settings = get_option('dmy_link_settings');
    $style = isset($settings['dmy_link_style']) ? $settings['dmy_link_style'] : 'dmylink-default';
    ?>
    <?php //wp_head(); ?>
</head>
<body class="dmy-overall-body">
    <div class="dmy-logo-box">
        <?php
        $logo_url = isset($settings['dmy_link_logo']) ? $settings['dmy_link_logo'] : plugins_url('/assets/img/default-logo.png', __FILE__);
        ?>
    </div>
