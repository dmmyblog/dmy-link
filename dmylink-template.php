<!DOCTYPE html>
<html class="dmy-overall-html">

<head>
    <title>即将离开
        <?php echo get_bloginfo('name'); ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <div class="dmylibk-default-box">
            <!-- logo -->
            <div class="dmylibk-default-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-default-title">
                <div class="dmylibk-default-title-div">
                    <div class="dmylibk-default-title-icon">
                        <img class="loading-img"
                            src="<?php echo plugins_url('assets\img\dmylibk-default.png', __FILE__); ?>"
                            alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                        <div class="dmylibk-default-title-text">请注意您的账号和财产安全</div>
                    </div>
                    <div class="dmylibk-default-titlelink">
                        <span>
                            您即将离开
                            <?php echo get_bloginfo('name'); ?>，去往:
                            <?php echo esc_url($link); ?> 请注意您的帐号和财产安全
                        </span>
                    </div>
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
        <div class="dmylibk-bilibili-box">
            <!-- 内容 -->
            <div class="dmylibk-bilibili-title">
                <div class="dmylibk-bilibili-title-div-title-no2">
                    <div class="dmylibk-bilibili-title-icon"> <img class="loading-img"
                            src="<?php echo plugins_url('assets\img\dmylibk-bilibili.png', __FILE__); ?>" alt="">
                        <div class="dmylibk-bilibili-title-text">即将离开
                            <?php echo get_bloginfo('name'); ?>，请保护好个人信息
                        </div>
                    </div>


                    <div class="dmylibk-bilibili-title-div">
                        <div class="dmylibk-csdn-title-icon">
                            <img class="loading-img"
                                src="<?php echo plugins_url('assets\img\dmylink-bilibili-link.png', __FILE__); ?>"
                                alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                            <span>
                                <?php echo esc_url($link); ?>
                            </span>
                        </div>
                    </div>
                    <div class="dmylibk-bilibili-link-a">
                        <a class="dmylibk-bilibili-link-a-no1" href="<?php echo home_url(); ?>">返回首页</a>
                        <a class="dmylibk-bilibili-link-a-no2" href="<?php echo esc_url($link); ?>">继续访问</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- 腾讯风格 2025-1-26-->
    <div class="dmylibk-tencent">
        <div class="dmylibk-tencent-box">
            <!-- logo -->
            <div class="dmylibk-tencent-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-tencent-title">
                <div class="dmylibk-tencent-title-div">
                    <div class="dmylibk-tencent-title-icon">
                        您即将离开
                        <?php echo get_bloginfo('name'); ?>，请注意您的账号财产安全
                    </div>

                    <div class="dmylibk-tencent-titlelink">
                        <a>
                            <?php echo esc_url($link); ?>
                        </a>
                    </div>
                </div>
                <div class="dmylibk-tencent-link-a">
                    <a href="<?php echo esc_url($link); ?>">继续访问
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- csnd风格 2025-1-26 -->
    <div class="dmylibk-csdn">
        <div class="dmylibk-csdn-box">
            <!-- logo -->
            <div class="dmylibk-csdn-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-csdn-title">
                <div class="dmylibk-csdn-title-div">
                    <div class="dmylibk-csdn-title-icon">
                        <img class="loading-img"
                            src="<?php echo plugins_url('assets\img\dmylibk-csdn.png', __FILE__); ?>"
                            alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                        <div class="dmylibk-csdn-title-text">请注意您的账号和财产安全</div>
                    </div>

                    <div class="dmylibk-csdn-titlelink"><span>您即将离开
                            <?php echo get_bloginfo('name'); ?>，去往：
                        </span>
                        <a>
                            <?php echo esc_url($link); ?>
                        </a>
                    </div>
                </div>
                <div class="dmylibk-csdn-link-a">
                    <a href="<?php echo esc_url($link); ?>">继续</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 知乎风格 2025-1-26-->
    <div class="dmylibk-zhihu">
        <div class="dmylibk-zhihu-box">
            <!-- logo -->
            <div class="dmylibk-zhihu-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-zhihu-title">
                <div class="dmylibk-zhihu-title-div">
                    <div class="dmylibk-zhihu-title-icon">
                        <div class="dmylibk-zhihu-title-text">即将离开
                            <?php echo get_bloginfo('name'); ?>
                        </div>
                        <p>您即将离开
                            <?php echo get_bloginfo('name'); ?>，请注意您的帐号和财产安全。
                        </p>
                        <p class="dmylibk-zhihu-titlelink-p-no2">
                            <?php echo esc_url($link); ?>
                        </p>
                    </div>
                    <div class="dmylibk-zhihu-link-a">
                        <a href="<?php echo esc_url($link); ?>">继续访问</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php wp_footer(); ?>
</body>

</html>