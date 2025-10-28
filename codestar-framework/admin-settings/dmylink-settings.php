<?php
// 引用 Codestar Framework
// 判断子比主题是否存在
function no_zibll_themes() {
    // 构建 zibll 主题 style.css 的绝对路径
    $style_file_path = WP_CONTENT_DIR . '/themes/zibll/style.css';
    
    // 检测文件是否存在且为文件（排除目录）
    return file_exists($style_file_path) && is_file($style_file_path);
}
if (!no_zibll_themes()){
    require_once plugin_dir_path(__FILE__) . '../../codestar-framework/codestar-framework.php';
} else {
    require_once get_template_directory() . '/inc/codestar-framework/codestar-framework.php';
}
// 引用设置页面文件
// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {

    // Set a unique slug-like ID
    $prefix = 'dmy_link_settings';


    $options = array(
        'menu_title'     => '外链跳转插件',
        'framework_title'=> '大绵羊外链跳转插件',
        'menu_slug'      => 'dmy_link_settings',
        'footer_text'    => '作者：大绵羊&天无神话 | 作者网站<a href="https://dmyblog.cn">大绵羊博客</a> | 版本:V1.3.4 | <i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> 感谢您使用大绵羊外链跳转插件。',
    );

    // 子比主题，启用 light 主题
    if ( no_zibll_themes() ) {
        $options['theme'] = 'light';
    }
    CSF::createOptions( $prefix, $options );
    // 资源基址（插件根 URL）
    $plugin_url = plugin_dir_url( dirname(__DIR__, 2) . '/dmy-link.php' );
    // 添加基本设置部分，包含总开关与跳转页 slug
    CSF::createSection($prefix, array(
        'title'  => '基本设置',
        'icon' => 'fa fa-cog',
        'fields' => array(
            array(
                'id'    => 'dmy_link_enable',
                'type'  => 'switcher',
                'title' => '启用插件功能',
                'desc'  => '关闭后插件所有功能将停止工作',
                'default' => true, // 默认开启
            ),
            array(
                'id'    => 'dmy_link_slug',
                'type'  => 'text',
                'title' => '跳转页路径（Slug）',
                'desc'  => '用于生成跳转页地址，例如 /dinterception；只允许小写字母、数字和短横线。修改后保存设置会自动刷新固定链接。',
                'default' => 'dinterception',
            ),
        ),
    ));
    // 白名单设置
    CSF::createSection($prefix, array(
        'title'  => '白名单设置',
        'icon' => 'fa fa-id-card-o',
        'fields' => array(
            array(
                'id'    => 'dmy_link_whitelist',
                'type'  => 'textarea',
                'title' => '白名单链接',
                'desc'  => '每行一个链接.不需要加http://或者https://',
                'default' => '',
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => '样式设置',
        'icon' => 'fa fa-paint-brush',
        'fields' => array(
            array(
                'type' => 'content',
                'content' => '<style>.csf--image-group{display:flex;flex-wrap:wrap;gap:12px}.csf--image-group .csf--image{margin:0}.csf--image-group .csf--image figure{width:120px;margin:0}.csf--image-group .csf--image img{width:100%;height:auto;border:1px solid #eee;border-radius:6px;display:block}.csf--image-group .csf--image figcaption{margin-top:6px;font-size:12px;color:#666;text-align:center;line-height:1.2}</style><script>(function(){window.addEventListener("load",function(){var map={"dmylink-default":"默认样式","dmylink-bilibili":"哔哩哔哩","dmylink-tencent":"腾讯云社区","dmylink-csdn":"CSDN","dmylink-zhihu":"知乎","dmylink-jump":"通用跳转","dmylink-moxing":"墨星博客","dmylink-tiktok":"TikTok"};document.querySelectorAll(".csf--image-group .csf--image figure").forEach(function(fig){var input=fig.querySelector("input");if(!input){return}var key=input.value;var label=map[key]||key;if(!fig.querySelector("figcaption")){var cap=document.createElement("figcaption");cap.textContent=label;fig.appendChild(cap)}})});})();</script>'
            ),
            array(
                'id'    => 'dmy_link_style',
                'type'  => 'image_select',
                'title' => '提示页面样式',
                'desc'  => '上方切换样式（点击图片进行选择），下方显示对应的预览图',
                'options' => array(
                    'dmylink-default'  => $plugin_url . 'assets/img/default-min.png',
                    'dmylink-bilibili' => $plugin_url . 'assets/img/bilibili-min.png',
                    'dmylink-tencent'  => $plugin_url . 'assets/img/tencent-min.png',
                    'dmylink-csdn'     => $plugin_url . 'assets/img/csdn-min.png',
                    'dmylink-zhihu'    => $plugin_url . 'assets/img/zhihu-min.png',
                    'dmylink-jump'     => $plugin_url . 'assets/img/jump-min.png',
                    'dmylink-moxing'   => $plugin_url . 'assets/img/moxingbk-min.png',
                    'dmylink-tiktok'   => $plugin_url . 'assets/img/tiktok-min.png',
                ),
                'default' => 'dmylink-default',
                'inline'  => true
            )
        ),
    ));

    // 圈子/社区功能设置
    CSF::createSection($prefix, array(
        'title'  => '主题社区功能',
        'icon' => 'fa fa-comments',
        'fields' => array(
            array(
                'id'    => 'dmy_link_function_type',
                'type'  => 'radio',
                'title' => '选择社区功能类型',
                'desc'  => '选择您要启用的社区功能类型，只能选择一项',
                'options' => array(
                    'none' => '不启用任何社区功能',
                    'circle' => '7b2主题圈子功能',
                    'forums' => '子比主题社区帖子功能'
                ),
                'default' => 'none',
                'inline' => true
            ),
            array(
                'id'    => 'dmy_link_circle_selector',
                'type'  => 'text',
                'title' => '圈子内容选择器',
                'desc'  => '用于识别圈子内容的CSS选择器，默认为.topic-content<br/>如果您的主题结构不同，可以修改此选择器',
                'default' => '.topic-content',
                'dependency' => array('dmy_link_function_type', '==', 'circle'),
            ),
            array(
                'id'    => 'dmy_link_forums_selector',
                'type'  => 'text',
                'title' => '社区帖子选择器',
                'desc'  => '用于识别社区帖子内容的CSS选择器，默认为.forum-article<br/>如果您的主题结构不同，可以修改此选择器',
                'default' => '.forum-article',
                'dependency' => array('dmy_link_function_type', '==', 'forums'),
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => 'Logo 设置',
        'icon' => 'fa fa-image',
        'fields' => array(
            array(
                'id'    => 'dmy_link_logo',
                'type'  => 'upload',
                'title' => 'Logo 图片',
                'desc'  => '上传一个图片作为 logo,如果您不设置，插件并不会自动获取您网站的logo',
                'default' => '',
            ),
        ),
    ));
    
    // 安全设置
    CSF::createSection($prefix, array(
        'title'  => '安全设置',
        'icon' => 'fa fa-lock',
        'fields' => array(
            array(
                'id'    => 'dmy_link_verification_method',
                'type'  => 'radio',
                'title' => '链接验证方式',
                'options' => array(
                    'random_string' => '随机字符串 + 过期机制',
                    'aes_encryption' => 'AES加密 + 后端验证',
                ),
                'default' => 'random_string',
                'desc' => '选择链接验证的安全机制'
            ),
            array(
                'id'    => 'dmy_link_expiration',
                'type'  => 'number',
                'title' => '过期时间（分钟）',
                'desc'  => '设置外链跳转链接的过期时间，单位为分钟<br/>默认为5分钟',
                'default' => 5,
                'min' => 1,
                'max' => 1440,
                'dependency' => array('dmy_link_verification_method', '==', 'random_string'),
            ),
            array(
                'id'    => 'dmy_link_aes_key',
                'type'  => 'text',
                'title' => 'AES加密密钥',
                'desc'  => '请输入32个字符的密钥（用于加密跳转链接）',
                'default' => bin2hex(openssl_random_pseudo_bytes(16)), // 生成32字符随机密钥
                'dependency' => array('dmy_link_verification_method', '==', 'aes_encryption'),
            ),
            array(
                'id'    => 'dmy_link_referer_protect',
                'type'  => 'switcher',
                'title' => '启用 Referer 防护',
                'desc'  => '开启后，禁止非本站 Referer 直接访问跳转页（例如 /dinterception 或自定义）',
                'default' => false,
            ),
            array(
                'id'    => 'dmy_link_referer_allow_empty',
                'type'  => 'switcher',
                'title' => '允许空 Referer',
                'desc'  => '某些浏览器/场景可能不发送 Referer，可选择放行空 Referer',
                'default' => true,
                'dependency' => array('dmy_link_referer_protect', '==', 'true'),
            ),
            array(
                'id'    => 'dmy_link_referer_whitelist',
                'type'  => 'textarea',
                'title' => 'Referer 白名单（可选）',
                'desc'  => '每行一个域名或URL（例如 example.com 或 https://sub.example.com）。在启用 Referer 防护时，允许这些来源访问跳转页。',
                'default' => '',
                'dependency' => array('dmy_link_referer_protect', '==', 'true'),
            ),
        ),
    ));
    // 关于插件
    CSF::createSection(
        $prefix,
        array(
            'title' => '关于插件',
            'icon' => 'fa fa-users',
            'fields' => array(
                array(
                    'type' => 'notice',
                    'style' => 'warning', 
                    'content' => '作者：大绵羊&天无神话<br/>
                    共同开发:天无神话(<a href="https://wxsnote.cn">王先生笔记</a>)<br/>
                    作者网站: <a href="https://dmyblog.cn">https://dmyblog.cn</a><br/>'
                ),
                array(
                    'type' => 'notice',
                    'style' => 'info', 
                    'content' => '鸣谢(使用平台素材&或者样式)<br/> 1.哔哩哔哩<br/>2.腾讯云社区<br/>3.知乎<br/>4.CSDN<br/>5.tiktok<br/>6.墨星博客<br/>7.茉莉小栈<br/>'
                ),
                array(
                    'type' => 'notice',
                    'style' => 'success', 
                    'content' => '欢迎加入QQ群947328468 <br/> 我的网站: <a href="https://dmyblog.cn">https://dmyblog.cn</a>'
                ),
                array(
                    'type' => 'notice',
                    'style' => 'success', 
                    'content' => '插件赞助:泉州市鲤城区柠萌科技有限公司</a>'
                ),
                
                array(
                  'type' => 'notice',
                  'style' => 'success', 
                  'content' => '关于开源协议<br/>本插件采用GPLv3协议开源<br/>CC BY-NC-SA 4.0'
              ),
                array(
                    'type' => 'notice',
                    'style' => 'danger', 
                    'content' => '
                    
                    使用的框架: Codestar Framework<br/>
                    框架参考链接: <a href="https://codestarframework.com">https://codestarframework.com</a><br/>
                    你可以任意修改插件作为自己使用，但请不要删除作者信息谢谢！<br/>
                    你不可以抹除这个插件作者的信息，不得说这个插件原创作者为你，你不得将这个插件作为商业使用，因为它本身就作为免费插件发布！<br/>
                      若您执意这样做了，我们会保留证据，您将会收到网友的谴责，必要时，我们保留诉讼的权力。'
                )
            )
        )
    );
}
