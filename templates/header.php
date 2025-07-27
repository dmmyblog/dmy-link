<!DOCTYPE html>
<html class="dmy-overall-html">
<head>
    <title>即将离开<?php echo get_bloginfo('name'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <?php 
    $settings = get_option('dmy_link_settings');
    $style = isset($settings['dmy_link_style']) ? $settings['dmy_link_style'] : 'dmylink-default';
    ?>
    <?php wp_head(); ?>
</head>
<body class="dmy-overall-body">
    <div class="dmy-logo-box">
        <?php
        $logo_url = isset($settings['dmy_link_logo']) ? $settings['dmy_link_logo'] : plugins_url('/assets/img/default-logo.png', __FILE__);
        ?>
    </div>
