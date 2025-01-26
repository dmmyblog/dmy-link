<!DOCTYPE html>
<html class="dmy-overall-html">

<head>
    <title>即将离开
        <?php echo esc_html(get_bloginfo('name')); ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body class="dmy-overall-body">
    <?php
    $blog_name = esc_html(get_bloginfo('name'));
    $settings = get_option('dmy_link_settings');
    $logo_url = isset($settings['dmy_link_logo']) ? esc_url($settings['dmy_link_logo']) : plugins_url('/assets/img/default-logo.png', __FILE__);
    $safe_link = esc_url($link);
    $home_url = esc_url(home_url());
    ?>

    <!-- 默认风格 -->
    <div class="dmylibk-default">
        <div class="dmylibk-default-box">
            <!-- logo -->
            <div class="dmylibk-default-logo">
                <img src="<?php echo $logo_url; ?>" alt="">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-default-title">
                <div class="dmylibk-default-title-div">
                    <div class="dmylibk-default-title-icon">
                        <img src="<?php echo plugins_url('assets/img/dmylibk-default.png', __FILE__); ?>"
                            alt="Reminder-image">
                        <div class="dmylibk-default-title-text">请注意您的账号和财产安全</div>
                    </div>
                    <div class="dmylibk-default-titlelink">
                        <span>您即将离开
                            <?php echo $blog_name; ?>，去往:
                            <?php echo $safe_link; ?> 请注意您的帐号和财产安全
                        </span>
                    </div>
                </div>
                <div class="dmylibk-default-link-a">
                    <a href="<?php echo $safe_link; ?> ">继续访问</a>
                    <a href="<?php echo $home_url; ?>">返回首页</a>
                </div>
            </div>
        </div>
    </div>

    <!-- bilibili风格 -->
    <div class="dmylibk-bilibili">
        <div class="dmylibk-bilibili-title">
            <div class="dmylibk-bilibili-title-logo">
                <img src="<?php echo plugins_url('assets/img/dmylibk-bilibili.png', __FILE__); ?>" alt="Reminder-image">
                <span class="dmylibk-bilibili-title-span">即将离开
                    <?php echo $blog_name; ?>，请保护好个人信息
                </span>
            </div>
            <div class="dmylibk-bilibili-title-link">
                <div class="dmylibk-bilibili-title-div">
                    <img src="<?php echo plugins_url('assets/img/dmylink-bilibili-link.png', __FILE__); ?>"
                        alt="Reminder">
                    <span class="dmylibk-bilibili-title-span-no2">
                        <?php echo $safe_link; ?>
                    </span>
                </div>
                <div class="dmylibk-tencent-link-a">
                    <a class="dmylibk-tencent-link-a-no1" href="<?php echo $home_url; ?>">返回首页</a>
                    <a href="<?php echo $safe_link; ?>" class="dmy-confirm-a-no4">继续访问</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 腾讯风格 -->
    <div class="dmylibk-tencent">
        <div class="dmylibk-tencent-box">
            <!-- logo -->
            <div class="dmylibk-tencent-logo">
                <img src="<?php echo $logo_url; ?>" alt="">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-tencent-title">
                <div class="dmylibk-tencent-title-div">
                    <div class="dmylibk-tencent-title-icon">您即将离开
                        <?php echo $blog_name; ?>，请注意您的账号财产安全
                    </div>
                    <div class="dmylibk-tencent-titlelink"><a>
                            <?php echo $safe_link; ?>
                        </a></div>
                </div>
                <div class="dmylibk-tencent-link-a">
                    <a href="<?php echo $safe_link; ?>">继续访问</a>
                </div>
            </div>
        </div>
    </div>

    <!-- csnd风格 -->
    <div class="dmylibk-csdn">
        <div class="dmylibk-csdn-box">
            <!-- logo -->
            <div class="dmylibk-csdn-logo">
                <img src="<?php echo $logo_url; ?>" alt="">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-csdn-title">
                <div class="dmylibk-csdn-title-div">
                    <div class="dmylibk-csdn-title-icon">
                        <img src="<?php echo plugins_url('assets/img/dmylibk-csdn.png', __FILE__); ?>"
                            alt="Reminder-image">
                        <div class="dmylibk-csdn-title-text">请注意您的账号和财产安全</div>
                    </div>
                    <div class="dmylibk-csdn-titlelink">
                        <span>您即将离开
                            <?php echo $blog_name; ?>，去往：
                        </span>
                        <span class="dmylibk-csdn-titlelink-span">
                            <?php echo $safe_link; ?>
                        </span>
                    </div>
                </div>
                <div class="dmylibk-csdn-link-a">
                    <a href="<?php echo $safe_link; ?>">继续</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 知乎风格 -->
    <div class="dmylibk-zhihu">
        <div class="dmylibk-zhihu-box">
            <!-- logo -->
            <div class="dmylibk-zhihu-logo">
                <img src="<?php echo $logo_url; ?>" alt="">
            </div>
            <!-- 内容 -->
            <div class="dmylibk-zhihu-title">
                <div class="dmylibk-zhihu-title-div">
                    <div class="dmylibk-zhihu-title-icon">
                        <div class="dmylibk-zhihu-title-text">即将离开
                            <?php echo $blog_name; ?>
                        </div>
                        <p>您即将离开
                            <?php echo $blog_name; ?>，请注意您的帐号和财产安全。
                        </p>
                        <p class="dmylibk-zhihu-titlelink-p-no2">
                            <?php echo $safe_link; ?>
                        </p>
                    </div>
                    <div class="dmylibk-zhihu-link-a">
                        <a href="<?php echo $safe_link; ?>">继续访问</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>

</html>