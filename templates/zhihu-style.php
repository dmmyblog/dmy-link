<!-- 知乎风格 -->
<div class="dmylink-zhihu">
    <div class="dmylink-zhihu-box">
        <!-- logo -->
        <div class="dmylink-zhihu-logo">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="dmylink-zhihu-title">
            <div class="dmylink-zhihu-title-div">
                <div class="dmylink-zhihu-title-icon">
                    <div class="dmylink-zhihu-title-text">即将离开
                        <?php echo get_bloginfo('name'); ?>
                    </div>
                    <p>您即将离开
                        <?php echo get_bloginfo('name'); ?>，请注意您的帐号和财产安全。
                    </p>
                    <p class="dmylink-zhihu-titlelink-p-no2">
                        <?php echo esc_url($link); ?>
                    </p>
                </div>
                <div class="dmylink-zhihu-link-a">
                    <a href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
                </div>
            </div>
        </div>
    </div>
</div>
