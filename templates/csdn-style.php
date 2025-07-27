<!-- csdn风格 -->
<div class="dmylink-csdn">
    <div class="dmylink-csdn-box">
        <!-- logo -->
        <div class="dmylink-csdn-logo">
            <img src="<?php echo $logourl; ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="dmylink-csdn-title">
            <div class="dmylink-csdn-title-div">
                <div class="dmylink-csdn-title-icon">
                    <img class="loading-img"
                        src="<?php echo DMY_LINK_URL . '/assets/img/dmylink-csdn.png'; ?> "
                        alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                    <div class="dmylink-csdn-title-text">请注意您的账号和财产安全</div>
                </div>
                <div class="dmylink-csdn-titlelink">
                    <span>您即将离开
                        <?php echo get_bloginfo('name'); ?>，去往：
                    </span>
                    <a>
                        <?php echo esc_url($link); ?>
                    </a>
                </div>
            </div>
            <div class="dmylink-csdn-link-a">
                <a href="<?php echo esc_url($link); ?>" target="_self">继续</a>
            </div>
        </div>
    </div>
</div>
