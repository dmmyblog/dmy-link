<?php
/**
 * 大绵羊外链跳转插件 - CSF设置面板配置
 * 
 * @package 大绵羊外链跳转插件
 * @author 大绵羊 & 天无神话
 * @version 1.5.0
 */

// 防止直接访问
if (!defined('ABSPATH')) exit;

/**
 * 初始化CSF设置面板
 */
function dmy_link_settings() {
    
    // 只有后台才执行此代码
    if (!is_admin()) {
        return;
    }
    
    // 检查CSF是否可用
    if (!class_exists('CSF')) {
        return false;
    }
    
    $prefix = 'dmy_link_settings';
    $version = dmy_link_plugin_version();
    
    // 创建设置页面
    CSF::createOptions($prefix, [
        'menu_title'      => '外链跳转插件',
        'menu_slug'       => $prefix,
        'menu_type'       => 'menu',
        'menu_icon'       => 'dashicons-admin-links',
        'menu_position'   => 59,
        'framework_title' => '大绵羊外链跳转插件 <small style="color: #fff;">v'.$version.'</small>',
        'footer_text'     => '<style>html body .csf-theme-light .csf-header-inner::before { content: "DMY" !important; }</style>作者：大绵羊&天无神话 | 作者网站：<a href="https://dmyblog.cn" target="_blank">大绵羊博客</a> <a href="https://wxsnote.cn" target="_blank">王先生笔记</a> | 版本:V'.$version,
        'show_bar_menu'   => false,
        'theme'           => is_zibll_themes() ? 'light' : 'dark',
        'show_in_customizer' => false,
        'footer_credit'   => '<i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> 感谢您使用大绵羊外链跳转插件',
    ]);

    // 添加各个设置面板
    dmy_link_create_basic_section($prefix);
    dmy_link_create_whitelist_section($prefix);
    dmy_link_create_style_section($prefix);
    dmy_link_create_community_section($prefix);
    dmy_link_create_logo_section($prefix);
    dmy_link_create_ad_section($prefix);
    dmy_link_create_security_section($prefix);
    dmy_link_create_about_section($prefix);
    
    return true;
}

/**
 * 创建基本设置面板
 */
function dmy_link_create_basic_section($prefix) {
    $fields = [];

    // 子比主题环境下显示冲突提示
    if (is_zibll_themes()) {
        $go_link_s = _pz('go_link_s');
        $go_link_nonce_s = _pz('go_link_nonce_s');
        $needs_fix = !empty($go_link_s) || !empty($go_link_nonce_s);

        $notice_type = $needs_fix ? 'warning' : 'info';
        $notice_content = '<strong>检测到子比主题环境</strong><br>';
        if ($needs_fix) {
            $notice_content .= '<span style="color:#d63638;">子比主题的「外链重定向」或「外链重定向鉴权」已开启，会与插件冲突导致外链无法正确跳转。插件已在运行时自动接管，但建议前往 <a href="' . esc_url(admin_url('admin.php?page=zibll_options#外链重定向')) . '">子比主题设置</a> 关闭以下选项：</span>';
            if (!empty($go_link_s)) {
                $notice_content .= '<br>• <strong>外链重定向</strong>（go_link_s）— 当前：开启 → 建议关闭';
            }
            if (!empty($go_link_nonce_s)) {
                $notice_content .= '<br>• <strong>外链重定向鉴权</strong>（go_link_nonce_s）— 当前：开启 → 建议关闭';
            }
        } else {
            $notice_content .= '<span style="color:#0073aa;">子比主题的「外链重定向」和「外链重定向鉴权」均已关闭，插件可正常工作。</span>';
        }

        $fields[] = [
            'type'    => 'notice',
            'style'   => $notice_type,
            'content' => $notice_content,
        ];
    }

    $fields[] = [
        'id'      => 'dmy_link_enable',
        'type'    => 'switcher',
        'title'   => '启用插件功能',
        'label'   => '关闭后插件所有功能将停止工作',
        'default' => true,
    ];
    $fields[] = [
        'id'      => 'dmy_link_slug',
        'type'    => 'text',
        'title'   => '跳转页路径（Slug）',
        'desc'    => '用于生成跳转页地址，例如 /dinterception；只允许小写字母、数字和短横线。修改后保存设置会自动刷新固定链接。',
        'default' => 'dinterception',
        'sanitize' => 'dmy_link_sanitize_slug',
    ];

    CSF::createSection($prefix, [
        'title'  => '基本设置',
        'icon'   => 'fa fa-cog',
        'fields' => $fields,
    ]);
}

/**
 * 创建白名单设置面板
 */
function dmy_link_create_whitelist_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '白名单设置',
        'icon'   => 'fa fa-id-card-o',
        'fields' => [
            [
                'id'    => 'dmy_link_whitelist',
                'type'  => 'textarea',
                'attributes'  => array(
                    'rows' => 5,
                ),
                'title' => '白名单链接',
                'desc'  => 'wordpress设置的地址默认为白名单，每行一个链接，不需要加http://或者https://',
                'default' => '',
            ],
        ],
    ]);
}

/**
 * 创建样式设置面板
 */
function dmy_link_create_style_section($prefix) {
    $plugin_url = plugin_dir_url(dirname(__DIR__));
    
    CSF::createSection($prefix, [
        'title'  => '样式设置',
        'icon'   => 'fa fa-paint-brush',
        'fields' => [
            [
                'type' => 'content',
                'content' => '<style>
                    .csf--image-group{display:flex;flex-wrap:wrap;gap:12px}
                    .csf--image-group .csf--image{margin:0}
                    .csf--image-group .csf--image figure{width:120px;margin:0}
                    .csf--image-group .csf--image img{width:100%;height:auto;border:1px solid #eee;border-radius:6px;display:block}
                    .csf--image-group .csf--image figcaption{margin-top:6px;font-size:12px;color:#666;text-align:center;line-height:1.2}
                </style>
                <script>
                (function(){
                    window.addEventListener("load",function(){
                        var map = {
                            "dmylink-default":"默认样式(茉莉小栈)",
                            "dmylink-bilibili":"哔哩哔哩", 
                            "dmylink-tencent":"腾讯云社区",
                            "dmylink-csdn":"CSDN",
                            "dmylink-zhihu":"知乎",
                            "dmylink-jump":"通用跳转",
                            "dmylink-moxing":"墨星博客",
                            "dmylink-tiktok":"TikTok"
                        };
                        document.querySelectorAll(".csf--image-group .csf--image figure").forEach(function(fig){
                            var input = fig.querySelector("input");
                            if(!input){return}
                            var key = input.value;
                            var label = map[key] || key;
                            if(!fig.querySelector("figcaption")){
                                var cap = document.createElement("figcaption");
                                cap.textContent = label;
                                fig.appendChild(cap);
                            }
                        });
                    });
                })();
                </script>'
            ],
            [
                'id'      => 'dmy_link_style',
                'type'    => 'image_select',
                'title'   => '提示页面样式',
                'desc'    => '上方切换样式（点击图片进行选择），下方显示对应的预览图',
                'options' => [
                    'dmylink-default'  => $plugin_url . 'assets/img/default-min.png',
                    'dmylink-bilibili' => $plugin_url . 'assets/img/bilibili-min.png',
                    'dmylink-tencent'  => $plugin_url . 'assets/img/tencent-min.png',
                    'dmylink-csdn'     => $plugin_url . 'assets/img/csdn-min.png',
                    'dmylink-zhihu'    => $plugin_url . 'assets/img/zhihu-min.png',
                    'dmylink-jump'     => $plugin_url . 'assets/img/jump-min.png',
                    'dmylink-moxing'   => $plugin_url . 'assets/img/moxingbk-min.png',
                    'dmylink-tiktok'   => $plugin_url . 'assets/img/tiktok-min.png',
                ],
                'default' => 'dmylink-default',
                'inline'  => true
            ]
        ],
    ]);
}

/**
 * 创建主题社区功能设置面板
 */
function dmy_link_create_community_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '主题社区功能',
        'icon'   => 'fa fa-comments',
        'fields' => [
            [
                'id'      => 'dmy_link_function_type',
                'type'    => 'radio',
                'title'   => '选择社区功能类型',
                'desc'    => '选择您要启用的社区功能类型，只能选择一项',
                'options' => [
                    'none'   => '不启用任何社区功能',
                    'circle' => '7b2主题圈子功能',
                    'forums' => '子比主题社区帖子功能'
                ],
                'default' => 'none',
                'inline'  => true
            ],
            [
                'id'        => 'dmy_link_circle_selector',
                'type'      => 'text',
                'title'     => '圈子内容选择器',
                'desc'      => '用于识别圈子内容的CSS选择器，默认为.topic-content<br/>如果您的主题结构不同，可以修改此选择器',
                'default'   => '.topic-content',
                'dependency' => ['dmy_link_function_type', '==', 'circle'],
            ],
            [
                'id'        => 'dmy_link_forums_selector',
                'type'      => 'text',
                'title'     => '社区帖子选择器',
                'desc'      => '用于识别社区帖子内容的CSS选择器，默认为.forum-article<br/>如果您的主题结构不同，可以修改此选择器',
                'default'   => '.forum-article',
                'dependency' => ['dmy_link_function_type', '==', 'forums'],
            ],
        ],
    ]);
}

/**
 * 创建Logo设置面板
 */
function dmy_link_create_logo_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => 'Logo 设置',
        'icon'   => 'fa fa-image',
        'fields' => [
            [
                'id'    => 'dmy_link_logo',
                'type'  => 'upload',
                'title' => 'Logo 图片',
                'desc'  => '上传一个图片作为 logo,如果您不设置，插件并不会自动获取您网站的logo',
                'default' => '',
            ],
        ],
    ]);
}

/**
 * 创建跳转页广告位面板（1.5.0 新增）
 */
function dmy_link_create_ad_section($prefix) {
    $max_countdown = defined('DMY_LINK_AD_MAX_COUNTDOWN') ? DMY_LINK_AD_MAX_COUNTDOWN : 30;

    $preview_url  = function_exists('dmy_link_ad_preview_url') ? dmy_link_ad_preview_url() : '';
    $preview_note = '保存设置后，可以 ';
    if ($preview_url !== '') {
        $preview_note .= '<a href="' . esc_url($preview_url) . '" target="_blank" rel="noopener noreferrer" class="button button-small">预览跳转页</a>'
                       . ' 查看实际效果（示例目标为 example.com，链接按当前「链接有效期」签发，过期后刷新本页即可重新生成）。';
    } else {
        $preview_note .= '在前台任意点击一条外链查看实际效果。';
    }

    CSF::createSection($prefix, [
        'title'  => '跳转页广告',
        'icon'   => 'fa fa-bullhorn',
        'fields' => [
            [
                'type'    => 'subheading',
                'content' => '跳转页是全站曝光最高的页面之一，这里可以放一个<strong>广告位 / 内容位</strong>：'
                           . '图片横幅、公众号二维码、赞助信息或第三方广告代码都可以。'
                           . '不影响原有的提示与「继续访问」按钮。',
            ],
            [
                'type'    => 'notice',
                'style'   => 'info',
                'content' => $preview_note,
            ],
            [
                'id'      => 'dmy_link_ad_enable',
                'type'    => 'switcher',
                'title'   => '启用跳转页广告位',
                'label'   => '关闭后跳转页不输出任何广告相关内容',
                'default' => false,
            ],
            [
                'id'         => 'dmy_link_ad_hide_logged_in',
                'type'       => 'switcher',
                'title'      => '仅对未登录访客展示',
                'desc'       => '开启后，已登录用户（站长自己、会员）在跳转页看不到广告位；倒计时不受影响。',
                'default'    => false,
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_position',
                'type'       => 'radio',
                'title'      => '广告位位置',
                'options'    => [
                    'after'  => '提示框下方（跟随内容）',
                    'top'    => '提示框上方（跟随内容）',
                    'bottom' => '底部悬浮横幅',
                ],
                'default'    => 'after',
                'inline'     => true,
                'desc'       => '底部悬浮横幅固定在屏幕底部、自带关闭按钮，不受各套皮肤布局影响。',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_type',
                'type'       => 'radio',
                'title'      => '广告内容类型',
                'options'    => [
                    'image' => '图片 + 链接',
                    'html'  => '自定义 HTML / 广告代码',
                ],
                'default'    => 'image',
                'inline'     => true,
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_image',
                'type'       => 'upload',
                'title'      => '广告图片',
                'desc'       => '建议宽度 900px 左右的横幅图，页面上会等比缩放到广告位宽度。',
                'default'    => '',
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|image'],
            ],
            [
                'id'         => 'dmy_link_ad_url',
                'type'       => 'text',
                'title'      => '点击跳转链接（可选）',
                'desc'       => '填写完整的 http(s) 地址；留空则图片不可点击。链接在新窗口打开，并带 <code>rel="nofollow sponsored"</code>。',
                'default'    => '',
                'sanitize'   => 'esc_url_raw',
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|image'],
            ],
            [
                'id'         => 'dmy_link_ad_alt',
                'type'       => 'text',
                'title'      => '图片替代文字（可选）',
                'default'    => '',
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|image'],
            ],
            [
                'id'         => 'dmy_link_ad_html',
                'type'       => 'textarea',
                'title'      => '广告 HTML 代码',
                'attributes' => ['rows' => 8, 'placeholder' => '<a href="https://example.com" target="_blank"><img src="..." alt=""></a>'],
                'desc'       => '支持常规 HTML 与 <code>&lt;iframe&gt;</code>。默认会按 WordPress 文章内容规则过滤，'
                              . '<code>&lt;script&gt;</code> 会被去掉；需要放第三方广告脚本时请打开下方「原样输出」。',
                'default'    => '',
                'sanitize'   => 'dmy_link_sanitize_ad_html',
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|html'],
            ],
            [
                'id'         => 'dmy_link_ad_raw_html',
                'type'       => 'switcher',
                'title'      => '原样输出广告代码（不过滤）',
                'desc'       => '<span style="color:#d63638;">仅在粘贴可信广告联盟（如自有广告系统）的代码时开启。</span>'
                              . '开启后广告 HTML 会原样输出到跳转页，包含其中的脚本；请勿粘贴来路不明的代码。'
                              . '没有 <code>unfiltered_html</code> 权限的账号保存时仍会被过滤。',
                'default'    => false,
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|html'],
            ],
            [
                'id'         => 'dmy_link_ad_bare',
                'type'       => 'switcher',
                'title'      => '透明容器（不加白色卡片）',
                'desc'       => '广告代码自带完整样式时开启，插件只负责居中摆放，不再套白色圆角卡片。',
                'default'    => false,
                'dependency' => ['dmy_link_ad_enable|dmy_link_ad_type', '==|==', 'true|html'],
            ],
            [
                'id'         => 'dmy_link_ad_label',
                'type'       => 'text',
                'title'      => '角标文字',
                'desc'       => '显示在广告位右上角的小标签，例如「广告」「赞助」「推荐」；留空则不显示。',
                'default'    => '广告',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'type'    => 'subheading',
                'content' => '<strong>外观自定义</strong>：以下选项同时作用于广告卡片和倒计时条。留默认值即可使用插件自带样式。',
            ],
            [
                'id'         => 'dmy_link_ad_width',
                'type'       => 'number',
                'title'      => '广告位宽度（px）',
                'desc'       => '默认 450，与各套皮肤的提示框同宽；范围 200～1200。手机端会自动收缩到屏幕的 94%。',
                'default'    => 450,
                'min'        => 200,
                'max'        => 1200,
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_bg',
                'type'       => 'color',
                'title'      => '卡片背景色',
                'desc'       => '支持透明度。默认 rgba(255,255,255,0.92)。',
                'default'    => 'rgba(255,255,255,0.92)',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_text_color',
                'type'       => 'color',
                'title'      => '文字颜色',
                'default'    => '#333333',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_accent_color',
                'type'       => 'color',
                'title'      => '强调色',
                'desc'       => '用于倒计时数字。默认 #fb7299。',
                'default'    => '#fb7299',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_radius',
                'type'       => 'number',
                'title'      => '圆角（px）',
                'default'    => 12,
                'min'        => 0,
                'max'        => 60,
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_ad_custom_css',
                'type'       => 'code_editor',
                'title'      => '自定义 CSS',
                'settings'   => ['mode' => 'css'],
                'desc'       => '只在跳转页输出。可用的类名：<code>.dmylink-slot</code>（外层容器）、<code>.dmylink-ad</code>（广告卡片）、'
                              . '<code>.dmylink-ad__label</code>（角标）、<code>.dmylink-ad__image</code> / <code>.dmylink-ad__html</code>（内容）、'
                              . '<code>.dmylink-countdown</code>（倒计时条）、<code>.dmylink-countdown__num</code>（秒数）。',
                'default'    => '',
                'sanitize'   => 'dmy_link_sanitize_css',
                'dependency' => ['dmy_link_ad_enable', '==', 'true'],
            ],
            [
                'type'    => 'subheading',
                'content' => '<strong>倒计时自动跳转</strong>：倒计时期间访客会停留在跳转页看到广告位。'
                           . '为了不让「即将离开本站」的安全提示形同虚设，倒计时<strong>随时可被访客中断</strong>'
                           . '（点击「取消自动跳转」或按任意键），切到后台标签页时也会自动暂停。',
            ],
            [
                'id'      => 'dmy_link_ad_countdown',
                'type'    => 'number',
                'title'   => '倒计时秒数',
                'desc'    => '0 表示关闭，不自动跳转；最大 ' . $max_countdown . ' 秒。倒计时不依赖广告位，单独开启也可以。',
                'default' => 0,
                'min'     => 0,
                'max'     => $max_countdown,
            ],
            [
                'id'         => 'dmy_link_ad_countdown_text',
                'type'       => 'text',
                'title'      => '倒计时文案',
                'desc'       => '用 <code>{seconds}</code> 表示秒数位置。留空使用默认：「{seconds} 秒后自动前往目标网站」。',
                'default'    => '',
                'attributes' => ['placeholder' => '{seconds} 秒后自动前往目标网站'],
                'dependency' => ['dmy_link_ad_countdown', '!=', '0'],
            ],
            [
                'id'         => 'dmy_link_ad_countdown_stop_text',
                'type'       => 'text',
                'title'      => '「取消」按钮文字',
                'default'    => '',
                'attributes' => ['placeholder' => '取消自动跳转'],
                'dependency' => ['dmy_link_ad_countdown', '!=', '0'],
            ],
            [
                'id'         => 'dmy_link_ad_countdown_cancelled_text',
                'type'       => 'text',
                'title'      => '取消后的提示',
                'default'    => '',
                'attributes' => ['placeholder' => '已取消自动跳转，请手动点击「继续访问」'],
                'dependency' => ['dmy_link_ad_countdown', '!=', '0'],
            ],
        ],
    ]);
}

/**
 * 创建安全设置面板
 */
function dmy_link_create_security_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '安全设置',
        'icon'   => 'fa fa-lock',
        'fields' => [
            [
                'type'    => 'subheading',
                'content' => '<strong>1.5.0 起统一使用 HMAC-SHA256 签名令牌</strong>：跳转令牌不再写入数据库，'
                           . '与 CDN / Nginx 整页缓存天然兼容；令牌内置有效期，硬上限 24 小时，'
                           . '旧版「AES 加密 + 永不过期」模式已停止签发。',
            ],
            [
                'id'        => 'dmy_link_expiration',
                'type'      => 'number',
                'title'     => '链接有效期（分钟）',
                'desc'      => '跳转令牌的有效期，单位分钟。默认 5 分钟，最长 1440 分钟（24 小时）。<br/>'
                             . '有效期越长，令牌被复制到站外滥用的窗口越大，建议保持较小值。',
                'default'   => 5,
                'min'       => 1,
                'max'       => 1440,
            ],
            [
                'id'      => 'dmy_link_legacy_token',
                'type'    => 'switcher',
                'title'   => '兼容 1.4.x 及更早的旧链接',
                'desc'    => '开启后仍可解析升级前签发的旧令牌（随机串 / AES 密文）。<br/>'
                           . '<strong>注意：旧的 AES 令牌永不过期</strong>，站点缓存刷新完毕后建议关闭此项，'
                           . '关闭后所有历史旧链接立即失效。',
                'default' => true,
            ],
            [
                'id'      => 'dmy_link_userinfo_guard',
                'type'    => 'switcher',
                'title'   => '游客访问用户资料时脱敏',
                'desc'    => '仅在子比主题下生效。开启后，未登录访客无法通过用户资料模态框看到'
                           . '邮箱 / QQ / 微信 / 地址等字段，防止匿名批量收集。',
                'default' => true,
            ],
            [
                'id'         => 'dmy_link_verification_method',
                'type'       => 'radio',
                'title'      => '链接验证方式（旧版遗留）',
                'options'    => [
                    'random_string'  => '随机字符串 + 过期机制（旧）',
                    'aes_encryption' => 'AES加密 + 后端验证（旧）',
                ],
                'default'    => 'random_string',
                'desc'       => '此项仅用于识别升级前的历史配置，不再影响新链接的签发方式。',
                'dependency' => ['dmy_link_legacy_token', '==', 'true'],
            ],
            [
                'id'         => 'dmy_link_aes_key',
                'type'       => 'text',
                'title'      => 'AES 密钥（旧版遗留）',
                'desc'       => '仅用于解析升级前用 AES 模式生成的历史链接，请勿再修改。'
                              . '关闭上方「兼容旧链接」后本项失效。',
                'default'    => '',
                'dependency' => ['dmy_link_legacy_token', '==', 'true'],
            ],
            [
                'id'      => 'dmy_link_referer_protect',
                'type'    => 'switcher',
                'title'   => '启用 Referer 防护',
                'desc'    => '开启后，禁止非本站 Referer 直接访问跳转页（例如 /dinterception 或自定义）。<br/>'
                           . 'Referer 可被伪造，它只能挡住顺手滥用，不能替代令牌有效期；建议与较短的有效期配合使用。',
                'default' => false,
            ],
            [
                'id'        => 'dmy_link_referer_allow_empty',
                'type'      => 'switcher',
                'title'     => '允许空 Referer',
                'desc'      => '某些浏览器/场景可能不发送 Referer，可选择放行空 Referer',
                'default'   => true,
                'dependency' => ['dmy_link_referer_protect', '==', 'true'],
            ],
            [
                'id'        => 'dmy_link_referer_whitelist',
                'type'      => 'textarea',
                'title'     => 'Referer 白名单（可选）',
                'desc'      => '每行一个域名或URL（例如 example.com 或 https://sub.example.com）。在启用 Referer 防护时，允许这些来源访问跳转页。',
                'default'   => '',
                'dependency' => ['dmy_link_referer_protect', '==', 'true'],
            ],
        ],
    ]);
}

/**
 * 创建关于插件面板
 */
function dmy_link_create_about_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '关于插件',
        'icon'   => 'fa fa-users',
        'fields' => [
            [
                'type'    => 'notice',
                'style'   => 'warning',
                'content' => '作者：大绵羊&天无神话<br/>
                             共同开发：天无神话(<a href="https://wxsnote.cn" target="_blank">王先生笔记</a>)<br/>
                             作者博客：大绵羊(<a href="https://dmyblog.cn" target="_blank">大绵羊博客</a>)<br/>'
            ],
            [
                'type'    => 'notice',
                'style'   => 'info',
                'content' => '鸣谢(使用平台素材&或者样式)<br/> 
                              1.哔哩哔哩<br/>
                              2.腾讯云社区<br/>
                              3.知乎<br/>
                              4.CSDN<br/>
                              5.tiktok<br/>
                              6.墨星博客<br/>
                              7.茉莉小栈(默认)<br/>'
            ],
            [
                'type'    => 'notice', 
                'style'   => 'success',
                'content' => '欢迎加入QQ群947328468 <br/> 
                              我的网站: <a href="https://dmyblog.cn" target="_blank">https://dmyblog.cn</a>'
            ],
            [
                'type'    => 'notice',
                'style'   => 'success', 
                'content' => '插件赞助:泉州市鲤城区柠萌科技有限公司'
            ],
            [
                'type'    => 'notice',
                'style'   => 'success', 
                'content' => '关于开源协议<br/>
                              本插件采用GPLv3协议开源<br/>
                              CC BY-NC-SA 4.0'
            ],
            [
                'type'    => 'notice',
                'style'   => 'danger', 
                'content' => '使用的框架: Codestar Framework<br/>
                              框架参考链接: <a href="https://codestarframework.com" target="_blank">https://codestarframework.com</a><br/>
                              你可以任意修改插件作为自己使用，但请不要删除作者信息谢谢！<br/>
                              你不可以抹除这个插件作者的信息，不得说这个插件原创作者为你，你不得将这个插件作为商业使用，因为它本身就作为免费插件发布！<br/>
                              若您执意这样做了，我们会保留证据，您将会收到网友的谴责，必要时，我们保留诉讼的权力。'
            ]
        ]
    ]);
}
