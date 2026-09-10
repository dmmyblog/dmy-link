<?php
/**
 * 跳转页广告位（1.5.0 新增）
 *
 * 跳转页是全站曝光最高的页面之一，这里把它做成一个可配置的内容位：
 * 支持「图片 + 链接」或「自定义 HTML」两种形式，可放在提示框下方，
 * 也可以做成顶部 / 底部悬浮横幅；另附一个可被用户随时中断的倒计时自动跳转。
 *
 * 安全约定：
 *  - 广告 HTML 保存时按用户权限清洗（无 unfiltered_html 权限一律 wp_kses_post）；
 *  - 渲染时默认再过一遍 wp_kses_post，只有站长明确打开「原样输出」才跳过；
 *  - 广告链接必须是 http(s) 绝对地址，输出时带 rel="noopener noreferrer nofollow sponsored"；
 *  - 倒计时必须可被中断（点击「取消」或按任意键），这一条不能为了转化率妥协。
 *
 * @package 大绵羊外链跳转插件
 */

if (!defined('ABSPATH')) {
    exit;
}

// 倒计时上限（秒），避免把跳转页做成长时间的广告落地页
if (!defined('DMY_LINK_AD_MAX_COUNTDOWN')) {
    define('DMY_LINK_AD_MAX_COUNTDOWN', 30);
}

/**
 * 广告 HTML 保存时的清洗：有 unfiltered_html 权限的管理员可保留脚本类广告代码，
 * 否则按 wp_kses_post 过滤。渲染阶段还会再按「原样输出」开关决定是否二次过滤。
 */
function dmy_link_sanitize_ad_html($value) {
    $value = is_string($value) ? $value : '';
    if (function_exists('current_user_can') && current_user_can('unfiltered_html')) {
        return $value;
    }
    return wp_kses_post($value);
}

/**
 * 自定义 CSS 保存时的清洗：去掉所有标签，并杜绝用 </style> 逃逸出样式块
 */
function dmy_link_sanitize_css($value) {
    $value = is_string($value) ? $value : '';
    $value = wp_strip_all_tags($value);
    $value = preg_replace('#<\s*/?\s*style#i', '', $value);
    return trim($value);
}

/**
 * 倒计时文案（可在后台自定义，留空回落默认值）
 *
 * @return array{text:string, stop:string, cancelled:string}
 */
function dmy_link_ad_texts($settings = null) {
    if ($settings === null) {
        $settings = get_option('dmy_link_settings');
    }
    $defaults = array(
        'text'      => __('{seconds} 秒后自动前往目标网站', 'dmylink'),
        'stop'      => __('取消自动跳转', 'dmylink'),
        'cancelled' => __('已取消自动跳转，请手动点击「继续访问」', 'dmylink'),
    );
    $keys = array(
        'text'      => 'dmy_link_ad_countdown_text',
        'stop'      => 'dmy_link_ad_countdown_stop_text',
        'cancelled' => 'dmy_link_ad_countdown_cancelled_text',
    );
    $texts = array();
    foreach ($keys as $k => $option) {
        $v = isset($settings[$option]) ? trim(wp_strip_all_tags((string) $settings[$option])) : '';
        $texts[$k] = ($v === '') ? $defaults[$k] : $v;
    }
    return apply_filters('dmy_link_ad_texts', $texts, $settings);
}

/**
 * 外观自定义 → 内联样式（CSS 变量 + 站长自定义 CSS）。全部为默认值时返回空串。
 */
function dmy_link_ad_inline_style($settings = null) {
    if ($settings === null) {
        $settings = get_option('dmy_link_settings');
    }
    if (!is_array($settings)) {
        $settings = array();
    }

    $vars = array();

    $width = isset($settings['dmy_link_ad_width']) ? (int) $settings['dmy_link_ad_width'] : 0;
    if ($width >= 200 && $width <= 1200 && $width !== 450) {
        $vars['--dmylink-ad-width'] = $width . 'px';
    }

    $radius = isset($settings['dmy_link_ad_radius']) ? (int) $settings['dmy_link_ad_radius'] : 12;
    if ($radius >= 0 && $radius <= 60 && $radius !== 12) {
        $vars['--dmylink-ad-radius'] = $radius . 'px';
    }

    $colors = array(
        '--dmylink-ad-bg'     => 'dmy_link_ad_bg',
        '--dmylink-ad-color'  => 'dmy_link_ad_text_color',
        '--dmylink-ad-accent' => 'dmy_link_ad_accent_color',
    );
    foreach ($colors as $var => $option) {
        $raw = isset($settings[$option]) ? trim((string) $settings[$option]) : '';
        // 只接受 #hex / rgb() / rgba() / hsl() / hsla() / 颜色关键字，防止把任意 CSS 塞进变量
        if ($raw !== '' && preg_match('/^(#[0-9a-f]{3,8}|(rgb|hsl)a?\([\d\s.,%\/]+\)|[a-z]{3,20})$/i', $raw)) {
            $vars[$var] = $raw;
        }
    }

    $css = '';
    if (!empty($vars)) {
        $css .= ':root{';
        foreach ($vars as $k => $v) {
            $css .= $k . ':' . $v . ';';
        }
        $css .= '}';
    }

    $custom = isset($settings['dmy_link_ad_custom_css']) ? dmy_link_sanitize_css($settings['dmy_link_ad_custom_css']) : '';
    if ($custom !== '') {
        $css .= "\n" . $custom;
    }

    return apply_filters('dmy_link_ad_inline_style', $css, $settings);
}

/**
 * 广告位允许的 HTML（在 wp_kses_post 基础上放开 iframe，供第三方广告位嵌入）
 */
function dmy_link_ad_allowed_html() {
    $allowed = wp_kses_allowed_html('post');
    $allowed['iframe'] = array(
        'src'             => true,
        'width'           => true,
        'height'          => true,
        'frameborder'     => true,
        'scrolling'       => true,
        'allow'           => true,
        'allowfullscreen' => true,
        'loading'         => true,
        'referrerpolicy'  => true,
        'sandbox'         => true,
        'title'           => true,
        'style'           => true,
        'class'           => true,
        'id'              => true,
    );
    return apply_filters('dmy_link_ad_allowed_html', $allowed);
}

/**
 * 读取并规范化广告位配置；未启用或内容为空时返回 null
 *
 * @param array|null $settings 插件设置（传入可避免重复 get_option）
 * @return array|null
 */
function dmy_link_get_ad_config($settings = null) {
    if ($settings === null) {
        $settings = get_option('dmy_link_settings');
    }
    if (!is_array($settings) || empty($settings['dmy_link_ad_enable'])) {
        return null;
    }

    // 「仅对未登录访客展示」：登录用户（通常是站长自己或会员）不看广告
    if (!empty($settings['dmy_link_ad_hide_logged_in']) && function_exists('is_user_logged_in') && is_user_logged_in()) {
        return null;
    }

    $type = isset($settings['dmy_link_ad_type']) && $settings['dmy_link_ad_type'] === 'html' ? 'html' : 'image';

    // after：提示框下方跟随内容；top：提示框上方跟随内容；bottom：底部悬浮横幅
    $position = isset($settings['dmy_link_ad_position']) ? (string) $settings['dmy_link_ad_position'] : 'after';
    if (!in_array($position, array('after', 'top', 'bottom'), true)) {
        $position = 'after';
    }

    $config = array(
        'type'     => $type,
        'position' => $position,
        'label'    => isset($settings['dmy_link_ad_label']) ? trim((string) $settings['dmy_link_ad_label']) : __('广告', 'dmylink'),
        'raw'      => !empty($settings['dmy_link_ad_raw_html']),
        'bare'     => !empty($settings['dmy_link_ad_bare']),
        'image'    => '',
        'url'      => '',
        'alt'      => '',
        'html'     => '',
    );

    if ($type === 'image') {
        $image = isset($settings['dmy_link_ad_image']) ? trim((string) $settings['dmy_link_ad_image']) : '';
        $url   = isset($settings['dmy_link_ad_url']) ? trim((string) $settings['dmy_link_ad_url']) : '';
        if ($image === '') {
            return null; // 图片模式没有图片就没有可展示的东西
        }
        $config['image'] = $image;
        $config['url']   = dmy_link_is_http_url($url) ? $url : '';
        $config['alt']   = isset($settings['dmy_link_ad_alt']) ? trim((string) $settings['dmy_link_ad_alt']) : '';
    } else {
        $html = isset($settings['dmy_link_ad_html']) ? (string) $settings['dmy_link_ad_html'] : '';
        if (trim($html) === '') {
            return null;
        }
        $config['html'] = $html;
    }

    return apply_filters('dmy_link_ad_config', $config, $settings);
}

/**
 * 倒计时秒数（0 表示关闭），受 DMY_LINK_AD_MAX_COUNTDOWN 上限约束
 */
function dmy_link_get_ad_countdown($settings = null) {
    if ($settings === null) {
        $settings = get_option('dmy_link_settings');
    }
    $seconds = isset($settings['dmy_link_ad_countdown']) ? (int) $settings['dmy_link_ad_countdown'] : 0;
    if ($seconds < 0) {
        $seconds = 0;
    }
    if ($seconds > DMY_LINK_AD_MAX_COUNTDOWN) {
        $seconds = DMY_LINK_AD_MAX_COUNTDOWN;
    }
    return (int) apply_filters('dmy_link_ad_countdown', $seconds, $settings);
}

/**
 * 是否需要在跳转页加载广告位样式表
 */
function dmy_link_ad_assets_needed($settings = null) {
    return dmy_link_get_ad_config($settings) !== null || dmy_link_get_ad_countdown($settings) > 0;
}

/**
 * 生成广告位 HTML（不输出）
 *
 * @param array $config dmy_link_get_ad_config() 的返回值
 * @return string
 */
function dmy_link_build_ad_html($config) {
    if (empty($config) || !is_array($config)) {
        return '';
    }

    $classes = array('dmylink-ad', 'dmylink-ad--' . $config['type'], 'dmylink-ad--' . $config['position']);
    $fixed   = ($config['position'] === 'bottom');
    if ($fixed) {
        $classes[] = 'dmylink-ad--fixed';
    }
    if (!empty($config['bare'])) {
        $classes[] = 'dmylink-ad--bare';
    }

    $inner = '';
    if ($config['type'] === 'image') {
        $img = sprintf(
            '<img src="%s" alt="%s" loading="lazy" decoding="async">',
            esc_url($config['image']),
            esc_attr($config['alt'])
        );
        if ($config['url'] !== '') {
            $inner = sprintf(
                '<a class="dmylink-ad__link" href="%s" target="_blank" rel="noopener noreferrer nofollow sponsored">%s</a>',
                esc_url($config['url']),
                $img
            );
        } else {
            $inner = $img;
        }
        $inner = '<div class="dmylink-ad__image">' . $inner . '</div>';
    } else {
        $html  = $config['raw'] ? $config['html'] : wp_kses($config['html'], dmy_link_ad_allowed_html());
        $inner = '<div class="dmylink-ad__html">' . $html . '</div>';
    }

    $label = '';
    if ($config['label'] !== '') {
        $label = '<span class="dmylink-ad__label">' . esc_html($config['label']) . '</span>';
    }

    $close = '';
    if ($fixed) {
        $close = sprintf(
            '<button type="button" class="dmylink-ad__close" aria-label="%s">&times;</button>',
            esc_attr__('关闭广告', 'dmylink')
        );
    }

    $html = sprintf(
        '<div class="%s" role="complementary">%s%s%s</div>',
        esc_attr(implode(' ', $classes)),
        $label,
        $close,
        $inner
    );

    return apply_filters('dmy_link_ad_html', $html, $config);
}

/**
 * 生成倒计时条 HTML（不输出）。倒计时逻辑由 dmy_link_ad_inline_script() 驱动。
 *
 * @param string $link    经过校验的目标外链
 * @param int    $seconds 倒计时秒数
 * @return string
 */
function dmy_link_build_countdown_html($link, $seconds, $settings = null) {
    $seconds = (int) $seconds;
    if ($seconds <= 0 || !dmy_link_is_http_url($link)) {
        return '';
    }

    $texts = dmy_link_ad_texts($settings);
    $num   = '<b class="dmylink-countdown__num">' . $seconds . '</b>';
    $text  = esc_html($texts['text']);
    // 文案里没写 {seconds} 占位符时，把秒数放到最前面
    $text = (strpos($text, '{seconds}') !== false) ? str_replace('{seconds}', $num, $text) : $num . ' ' . $text;

    return sprintf(
        '<div id="dmylink-countdown" class="dmylink-countdown" data-seconds="%d" data-url="%s" role="status" aria-live="polite">'
            . '<span class="dmylink-countdown__text">%s</span>'
            . '<a href="#" class="dmylink-countdown__stop">%s</a>'
        . '</div>',
        $seconds,
        esc_attr($link),
        $text,
        esc_html($texts['stop'])
    );
}

/**
 * 跳转页内联脚本：倒计时（可被点击 / 任意按键中断）与悬浮广告的关闭按钮
 */
function dmy_link_ad_inline_script($settings = null) {
    $texts     = dmy_link_ad_texts($settings);
    $cancelled = esc_js($texts['cancelled']);

    return <<<JS
(function () {
  var closes = document.querySelectorAll('.dmylink-ad__close');
  for (var i = 0; i < closes.length; i++) {
    closes[i].addEventListener('click', function () {
      var ad = this.closest ? this.closest('.dmylink-ad') : this.parentNode;
      if (ad && ad.parentNode) { ad.parentNode.removeChild(ad); }
    });
  }

  var box = document.getElementById('dmylink-countdown');
  if (!box) { return; }
  var left   = parseInt(box.getAttribute('data-seconds'), 10) || 0;
  var target = box.getAttribute('data-url') || '';
  var num    = box.querySelector('.dmylink-countdown__num');
  var text   = box.querySelector('.dmylink-countdown__text');
  var stop   = box.querySelector('.dmylink-countdown__stop');
  if (left <= 0 || !/^https?:\\/\\//i.test(target)) { return; }

  var timer = null, done = false;
  function cancel() {
    if (done) { return; }
    done = true;
    if (timer) { clearInterval(timer); }
    box.className += ' is-cancelled';
    if (text) { text.textContent = '{$cancelled}'; }
    if (stop && stop.parentNode) { stop.parentNode.removeChild(stop); }
  }
  function tick() {
    if (done) { return; }
    left -= 1;
    if (num) { num.textContent = left; }
    if (left <= 0) {
      done = true;
      clearInterval(timer);
      window.location.href = target;
    }
  }

  if (stop) {
    stop.addEventListener('click', function (e) { e.preventDefault(); cancel(); });
  }
  // 任意按键都视为「用户想留下来看看」，立即停止自动跳转
  document.addEventListener('keydown', cancel);
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) { cancel(); }
  });

  timer = setInterval(tick, 1000);
})();
JS;
}

/**
 * 在跳转页输出广告位 + 倒计时 + 内联脚本
 *
 * 模板流程里会调用两次：
 *  - $slot = 'top'   在皮肤内容之前，只输出「提示框上方」的广告；
 *  - $slot = 'after' 在皮肤内容之后，输出倒计时条、「提示框下方」/「底部悬浮」的广告以及脚本。
 *
 * @param string     $link     经过校验的目标外链
 * @param array|null $settings 插件设置
 * @param string     $slot     'top' | 'after'
 */
function dmy_link_render_ad_slot($link, $settings = null, $slot = 'after') {
    if ($settings === null) {
        $settings = get_option('dmy_link_settings');
    }

    $config   = dmy_link_get_ad_config($settings);
    $position = $config ? $config['position'] : '';

    if ($slot === 'top') {
        if ($position !== 'top') {
            return;
        }
        echo '<div class="dmylink-slot dmylink-slot--top">' . dmy_link_build_ad_html($config) . '</div>';
        return;
    }

    $ad_html        = ($position === 'after' || $position === 'bottom') ? dmy_link_build_ad_html($config) : '';
    $countdown_html = dmy_link_build_countdown_html($link, dmy_link_get_ad_countdown($settings), $settings);

    if ($ad_html === '' && $countdown_html === '') {
        return;
    }

    // 只有悬浮横幅、没有倒计时条时，外层容器不占用文档流高度
    $in_flow = $countdown_html !== '' || $position === 'after';
    $class   = 'dmylink-slot' . ($in_flow ? '' : ' dmylink-slot--floating');

    echo '<div class="' . esc_attr($class) . '">' . $countdown_html . $ad_html . '</div>';
    echo "<script>\n" . dmy_link_ad_inline_script($settings) . "\n</script>";
}

/**
 * 生成一条用于预览跳转页的链接（目标为示例站点），供设置面板使用
 */
function dmy_link_ad_preview_url() {
    if (!function_exists('dmy_link_sign_url') || !function_exists('dmy_link_build_redirect_url')) {
        return '';
    }
    return dmy_link_build_redirect_url(dmy_link_sign_url('https://example.com/'));
}
