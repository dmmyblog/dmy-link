<!-- 腾讯风格 -->
<div class="dmylink-tencent">
    <div class="dmylink-tencent-box">
        <!-- logo -->
        <div class="dmylink-tencent-logo">
            <img src="<?php echo $logourl; ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="dmylink-tencent-title">
            <div class="dmylink-tencent-title-div">
                <div class="dmylink-tencent-title-icon">
                    您即将离开
                    <?php echo get_bloginfo('name'); ?>，请注意您的账号财产安全
                </div>
                <div class="dmylink-tencent-titlelink">
                    <a>
                        <?php echo esc_url($link); ?>
                    </a>
                </div>
            </div>
            <div class="dmylink-tencent-link-a">
                <a href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
            </div>
        </div>
    </div>
</div>
