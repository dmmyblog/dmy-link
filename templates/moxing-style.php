<div class="dmylink-moxing-box">
  <div class="dmylink-moxing-logo">
   <img src="<?php echo $logourl; ?>" alt="<?php echo get_bloginfo('name'); ?>logo">
  </div>

  <p class="dmylink-moxing-title">
    <span class="dmylink-moxing-title-icon">🔗</span>
    <span class="dmylink-moxing-title-text">
      <?php echo esc_url($link); ?>
    </span>
  </p>

  <div class="dmylink-moxing-link-a">
    <a href="<?php echo esc_url($link); ?>" class="dmylink-moxing-link-background-pink">
      继续前往
    </a>
    <a href="<?php echo home_url(); ?>" class="dmylink-moxing-link-background-blue">
      回到主页
    </a>
  </div>
</div>