<?php
// 引用 Codestar Framework
require_once plugin_dir_path(__FILE__) . '../../codestar-framework/codestar-framework.php';

// 引用设置页面文件
// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {

    // Set a unique slug-like ID
    $prefix = 'dmy_link_settings';

    // Create options
    CSF::createOptions( $prefix, array(
        'menu_title' => '外链跳转插件',
        'framework_title' => '大绵羊外链跳转插件',
        'menu_slug'  => 'dmy_link_settings',
        'footer_text' => '作者：大绵羊 | 作者网站<a href="https://dmyblog.cn">大绵羊博客</a> | 版本:V1.2.0 | <i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> 感谢您使用大绵羊外链跳转插件。',
        // 'footer_credit' => '柠萌主题Pro',
        // 'theme' => 'light'
    ) );

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
                'id'    => 'dmy_link_style',
                'type'  => 'select',
                'title' => '提示页面样式',
                'desc'  => '选择提示页面的样式',
                'options' => array(
                    'dmylink-default' => '默认样式',
                    'dmylink-bilibili' => 'bilibili风格',
                    'dmylink-tencent' => 'tencent风格',
                    'dmylink-csdn' => 'csdn风格',
                    'dmylink-zhihu' => 'zhihu风格',
                    'dmylink-jump' => 'jump风格',
                    'dmylink-moxing' => 'moxing风格',
                    'dmylink-tiktok' => 'tiktok风格',

                ),
                'default' => 'dmylibk-default', // 修正默认值
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
                    'content' => '大绵羊外链跳转插件作者：大绵羊<br/><br/>
                    你可以任意修改插件作为自己使用，但请不要删除作者信息谢谢！<br/>
                    作者网站: <a href="https://dmyblog.cn">https://dmyblog.cn</a><br/>
                    使用的框架: Codestar Framework<br/>
                    框架参考链接: <a href="https://codestarframework.com">https://codestarframework.com</a>'
                ),
                array(
                    'type' => 'notice',
                    'style' => 'info', 
                    'content' => '鸣谢(使用平台素材&或者样式)<br/> 1.哔哩哔哩<br/>2.腾讯云社区<br/>3.知乎<br/>4.CSDN<br/>'
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
                    'content' => '你不可以抹除这个插件作者的信息，不得说这个插件原创作者为你，你不得将这个插件作为商业使用，因为它本身就作为免费插件发布！<br/>
                      若您执意这样做了，我们会保留证据，您将会收到网友的谴责，必要时，我们保留诉讼的权力。'
                )
            )
        )
    );
}
