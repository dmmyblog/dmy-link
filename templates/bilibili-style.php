<!-- bilibili风格 -->
<div class="dmylink-bilibili">
    <div class="dmylink-bilibili-box">
        <!-- 内容 -->
        <div class="dmylink-bilibili-title">
            <div class="dmylink-bilibili-title-div-title-no2">
                <div class="dmylink-bilibili-title-icon"> 
                    <img class="loading-img"
                    src="<?php echo DMY_LINK_URL . 'assets/img/dmylink-bilibili.png'; ?>" 
                        alt="">
                    <div class="dmylink-bilibili-title-text">即将离开
                        <?php echo get_bloginfo('name'); ?>，请保护好个人信息
                    </div>
                </div>
                <div class="dmylink-bilibili-title-div">
                    <div class="dmylink-csdn-title-icon">
                        <img class="loading-img"
                            src="<?php echo DMY_LINK_URL . 'assets/img/dmylink-bilibili-link.png'; ?>"
                            alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                        <span>
                            <?php echo esc_url($link); ?>
                        </span>
                    </div>
                </div>
                <div class="dmylink-bilibili-link-a">
                    <a class="dmylink-bilibili-link-a-no1" href="<?php echo home_url(); ?>">返回文章</a>
                    <a class="dmylink-bilibili-link-a-no2" href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
                </div>
            </div>
        </div>
    </div>
</div>
