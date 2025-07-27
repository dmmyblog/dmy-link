<!-- 默认风格 -->
<div class="dmylink-default">
    <div class="dmylink-default-box">
        <!-- logo -->
        <div class="dmylink-default-logo">
            <img src="<?php echo $logourl; ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="dmylink-default-title">
            <div class="dmylink-default-title-div">
                <div class="dmylink-default-title-icon">
                    <img class="loading-img"
                        src="<?php echo DMY_LINK_URL . 'assets/img/dmylink-default.png'; ?>"
                        alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                    <div class="dmylink-default-title-text">请注意您的账号和财产安全</div>
                </div>
                <div class="dmylink-default-titlelink">
                    <span>
                        您即将离开
                        <?php echo get_bloginfo('name'); ?>，去往:
                        <?php echo esc_url($link); ?> 请注意您的帐号和财产安全
                    </span>
                </div>
            </div>
            <div class="dmylink-default-link-a">
                <a href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
                <a href="<?php echo home_url(); ?>">返回文章</a>
            </div>
        </div>
    </div>
</div>
