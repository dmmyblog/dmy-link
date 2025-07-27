<!-- JUMP风格 -->
<div class="dmylink-jump">
  <div class="dmylink-jump-box">
    <!-- 内容 -->
    <div class="dmylink-jump-title">
      <div class="dmylink-jump-title-div">
        <div class="dmylink-jump-alert-icon">
          <svg viewBox="0 0 24 24" width="40" height="40" fill="white">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z">
            </path>
          </svg>
        </div>
        <h1>
          <svg viewBox="0 0 24 24" width="40" height="32" fill="#667eea">
            <path
              d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z">
            </path>
            <path d="M12 7.99h-2v6h2zM12 15.99h-2v2h2z"></path>
          </svg>
          跳转提示
        </h1>

        <p>您即将离开当前站点</p>
        <span class="dmylink-jump-color">前往外部网站
          <a>
            <?php echo esc_url($link); ?>
          </a>
        </span>

      </div>
      <div class="dmylink-jump-cancel-btn">
        <a href="<?php echo home_url(); ?>">
          返回首页
        </a>
        <a href="<?php echo esc_url($link); ?>">
          继续访问
        </a>
      </div>
    </div>
  </div>
</div>