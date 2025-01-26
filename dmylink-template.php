<!DOCTYPE html>
<html class="dmy-overall-html">

<head>
    <title>即将离开<?php echo get_bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body class="dmy-overall-body">
    <div class="dmy-logo-box">
        <?php
        $settings = get_option('dmy_link_settings');
        $logo_url = isset($settings['dmy_link_logo']) ? $settings['dmy_link_logo'] : plugins_url('/assets/img/default-logo.png', __FILE__);
        ?>
    </div>
    <!-- 默认风格 -->
    <div class="dmylibk-default">
        <div class="dmylibk-default-box-customize">
            <div class="dmylibk-tencent-img"> <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo get_bloginfo('name'); ?>Logo"></div>
            <div class="dmylibk-default-title">
                <div class="dmylibk-default-title-prompt">
                    <img src="<?php echo plugins_url('assets\img\dmylibk-default.png', __FILE__); ?>"
                        alt="<?php echo get_bloginfo('name'); ?>Logo">
                    <span class="dmylibk-default-title-span">即将离开大绵羊博客</span>
                </div>
                <div class="dmylibk-default-title-div">
                    您即将离开
                    <?php echo get_bloginfo('name'); ?>，去往:
                    <?php echo esc_url($link); ?> 请注意您的帐号和财产安全
                </div>
                <div class="dmylibk-default-link-a">
                    <a href="<?php echo esc_url($link); ?>">继续访问</a>
                    <a href="<?php echo home_url(); ?>">返回首页</a>
                </div>
            </div>
        </div>


    </div>


    <!-- bilibili风格 -->
    <div class="dmylibk-bilibili">
        <div class="dmylibk-bilibili-title">
            <div class="dmylibk-bilibili-title-logo">
                <img src="<?php echo plugins_url('assets\img\dmylibk-bilibili.png', __FILE__); ?>" alt="Reminder-image">

                <span class="dmylibk-bilibili-title-span">即将离开
                    <?php echo get_bloginfo('name'); ?>，请保护好个人信息
                </span>
            </div>
            <div class="dmylibk-bilibili-title-link">
                <div class="dmylibk-bilibili-title-div">
                    <img src="<?php echo plugins_url('assets\img\dmylink-bilibili-link.png', __FILE__); ?>"
                        alt="Reminder">
                    <span class="dmylibk-bilibili-title-span-no2">
                        <?php echo esc_url($link); ?>
                    </span>

                </div>
                <div class="dmylibk-tencent-link-a">
                    <a href="<?php echo home_url(); ?>">返回首页</a>
                    <a href="<?php echo esc_url($link); ?>" class="dmy-confirm-a-no4">继续访问</a>
                </div>
            </div>
        </div>
    </div>
    <!-- 腾讯风格 -->
    <div class="dmylibk-tencent ">
        <div class="dmylibk-tencent-box-customize">
            <div class="dmylibk-tencent-img"> <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo get_bloginfo('name'); ?>Logo"></div>
            <div class="dmylibk-tencent-title">
                <span class="dmylibk-tencent-title-span">您即将离开
                    <?php echo get_bloginfo('name'); ?>，请注意您的账号财产安全
                </span>
                <span class="dmylibk-tencent-titlelink-span">
                    <?php echo esc_url($link); ?>
                </span>
                <div class="dmylibk-tencent-link-a">
                    <a href="<?php echo esc_url($link); ?>" class="dmy-confirm-a-no4">继续访问</a>
                </div>
            </div>
        </div>
    </div>
    <!-- csnd风格 -->
    <div class="dmylibk-csdn">
        <div class="dmylibk-csdn-box-customize">
            <div class="dmylibk-csdn-img"> <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo get_bloginfo('name'); ?>Logo"></div>
            <div class="dmylibk-csdn-title">
                <div class="dmylibk-csdn-title-div">
                    <img src="<?php echo plugins_url('assets\img\dmylibk-csdn.png', __FILE__); ?>" alt="Reminder-image">
                    <span> 请注意您的账号和财产安全</span>
                </div>
                <div class="dmylibk-csdn-titlelink-div">
                    <span>您即将离开
                        <?php echo get_bloginfo('name'); ?>，去往：
                    </span>
                    <span class="dmylibk-csdn-titlelink-span">
                        <?php echo esc_url($link); ?>
                    </span>
                </div>
                <div class="dmylibk-csdn-link-a">
                    <a href="<?php echo esc_url($link); ?>" class="dmy-confirm-a-no3">继续</a>
                </div>
            </div>
        </div>
    </div>
    <!-- 知乎风格 -->
    <div class="dmylibk-zhihu">
        <div class="dmylibk-zhihu-box-customize">
            <div class="dmylibk-zhihu-img"> <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo get_bloginfo('name'); ?>Logo"></div>
            <div class="dmylibk-zhihu-title">
                <h1>即将离开
                    <?php echo get_bloginfo('name'); ?>
                </h1>
                <span>您即将离开
                    <?php echo get_bloginfo('name'); ?>，请注意您的帐号和财产安全。
                </span>
                <div class="dmylibk-zhihu-title-div">
                    <span class="">
                        <?php echo esc_url($link); ?>
                    </span>
                </div>
                <div class="dmylibk-zhihu-link-a">
                    <a href="<?php echo esc_url($link); ?>" class="dmy-confirm-a-no3">继续访问</a>
                </div>
            </div>
        </div>
    </div>


    <?php wp_footer(); ?>
</body>

</html>