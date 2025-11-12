<?php
/**
 * 大绵羊外链跳转插件 - CSF设置面板配置
 * 
 * @package 大绵羊外链跳转插件
 * @author 大绵羊 & 天无神话
 * @version 1.3.6
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
        'menu_position'   => 58,
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
    dmy_link_create_security_section($prefix);
    dmy_link_create_about_section($prefix);
    
    return true;
}

/**
 * 创建基本设置面板
 */
function dmy_link_create_basic_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '基本设置',
        'icon'   => 'fa fa-cog',
        'fields' => [
            [
                'id'      => 'dmy_link_enable',
                'type'    => 'switcher',
                'title'   => '启用插件功能',
                'label'   => '关闭后插件所有功能将停止工作',
                'default' => true,
            ],
            [
                'id'      => 'dmy_link_slug',
                'type'    => 'text',
                'title'   => '跳转页路径（Slug）',
                'desc'    => '用于生成跳转页地址，例如 /dinterception；只允许小写字母、数字和短横线。修改后保存设置会自动刷新固定链接。',
                'default' => 'dinterception',
                'sanitize' => 'dmy_link_sanitize_slug',
            ],
        ],
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
 * 创建安全设置面板
 */
function dmy_link_create_security_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '安全设置',
        'icon'   => 'fa fa-lock',
        'fields' => [
            [
                'id'      => 'dmy_link_verification_method',
                'type'    => 'radio',
                'title'   => '链接验证方式',
                'options' => [
                    'random_string'  => '随机字符串 + 过期机制',
                    'aes_encryption' => 'AES加密 + 后端验证',
                ],
                'default' => 'random_string',
                'desc'    => '选择链接验证的安全机制'
            ],
            [
                'id'        => 'dmy_link_expiration',
                'type'      => 'number',
                'title'     => '过期时间（分钟）',
                'desc'      => '设置外链跳转链接的过期时间，单位为分钟<br/>默认为5分钟',
                'default'   => 5,
                'min'       => 1,
                'max'       => 1440,
                'dependency' => ['dmy_link_verification_method', '==', 'random_string'],
            ],
            [
                'id'        => 'dmy_link_aes_key',
                'type'      => 'text',
                'title'     => 'AES加密密钥',
                'desc'      => '请输入32个字符的密钥（用于加密跳转链接）',
                'default'   => bin2hex(openssl_random_pseudo_bytes(16)),
                'dependency' => ['dmy_link_verification_method', '==', 'aes_encryption'],
            ],
            [
                'id'      => 'dmy_link_referer_protect',
                'type'    => 'switcher',
                'title'   => '启用 Referer 防护',
                'desc'    => '开启后，禁止非本站 Referer 直接访问跳转页（例如 /dinterception 或自定义）',
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
                             作者网站：<a href="https://dmyblog.cn" target="_blank">大绵羊博客</a><br/>'
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
