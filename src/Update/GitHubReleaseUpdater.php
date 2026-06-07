<?php
/**
 * GitHub Releases 自动更新
 *
 * @package DmyLink\Update
 */

if (!defined('ABSPATH')) {
    return;
}

final class DmyLink_GitHubReleaseUpdater
{
    private const GITHUB_OWNER  = 'dmmyblog';
    private const GITHUB_REPO   = 'dmy-link';
    private const PLUGIN_NAME   = '大绵羊外链跳转插件';
    private const PLUGIN_AUTHOR = '大绵羊';
    private const PLUGIN_DESC   = '大绵羊外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。';
    private const WP_TESTED     = '7.0';
    private const WP_REQUIRES   = '6.4';
    private const PHP_REQUIRES  = '7.4';

    private const API_URL      = 'https://api.github.com/repos/dmmyblog/dmy-link/releases/latest';
    private const RELEASES_URL = 'https://github.com/dmmyblog/dmy-link/releases';
    private const CACHE_KEY    = 'dmy_link_latest_release';
    private const CACHE_TTL    = 1800; // 30 分钟

    private static $plugin_file    = '';
    private static $plugin_version = '';

    /**
     * 注册更新检测钩子
     */
    public static function init($plugin_file, $plugin_version)
    {
        self::$plugin_file    = $plugin_file;
        self::$plugin_version = $plugin_version;

        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'filterUpdateTransient'));
        add_filter('plugins_api', array(__CLASS__, 'filterPluginInfo'), 10, 3);
    }

    /**
     * 注入更新信息到 WP 更新 transient
     */
    public static function filterUpdateTransient($transient)
    {
        if (!is_object($transient)) {
            return $transient;
        }

        $release    = self::getRelease();
        $pluginFile = plugin_basename(self::$plugin_file);

        if (!$release || version_compare($release['version'], self::$plugin_version, '<=')) {
            if (!isset($transient->no_update) || !is_array($transient->no_update)) {
                $transient->no_update = array();
            }
            $transient->no_update[$pluginFile] = self::buildUpdateObject(
                $release ?: self::currentRelease()
            );
            return $transient;
        }

        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = array();
        }
        $transient->response[$pluginFile] = self::buildUpdateObject($release);

        return $transient;
    }

    /**
     * 提供「查看详情」弹窗内容
     */
    public static function filterPluginInfo($result, $action, $args)
    {
        if ($action !== 'plugin_information' || !is_object($args) || ($args->slug ?? '') !== self::slug()) {
            return $result;
        }

        $release = self::getRelease() ?: self::currentRelease();
        $info    = self::buildUpdateObject($release);

        $info->sections = array(
            'description' => self::PLUGIN_DESC,
            'changelog'   => self::formatChangelog($release['body'] ?? ''),
        );
        $info->banners = array();
        $info->icons   = array();

        return $info;
    }
    /**
     * 请求 GitHub API 并缓存结果
     */
    private static function getRelease()
    {
        $cached = get_site_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $response = wp_remote_get(self::API_URL, array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => self::slug() . '/' . self::$plugin_version,
            ),
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = (string) wp_remote_retrieve_body($response);
        if ($status < 200 || $status >= 300 || $body === '') {
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || !empty($data['draft']) || !empty($data['prerelease'])) {
            return null;
        }

        $version = self::normalizeVersion(
            isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : ''
        );
        if ($version === '') {
            return null;
        }

        $release = array(
            'version'      => $version,
            'tag'          => isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : $version,
            'name'         => isset($data['name']) && is_string($data['name']) && $data['name'] !== '' ? $data['name'] : 'v' . $version,
            'body'         => isset($data['body']) && is_string($data['body']) ? $data['body'] : '',
            'published_at' => isset($data['published_at']) && is_string($data['published_at']) ? $data['published_at'] : '',
            'package'      => self::resolvePackageUrl($data),
            'homepage'     => isset($data['html_url']) && is_string($data['html_url']) ? esc_url_raw($data['html_url']) : self::RELEASES_URL,
        );

        set_site_transient(self::CACHE_KEY, $release, self::CACHE_TTL);
        return $release;
    }
    /**
     * 构造 WP 更新对象
     */
    private static function buildUpdateObject($release)
    {
        $object = new \stdClass();
        $object->id           = self::RELEASES_URL;
        $object->slug         = self::slug();
        $object->plugin       = plugin_basename(self::$plugin_file);
        $object->new_version  = (string) ($release['version'] ?? self::$plugin_version);
        $object->url          = (string) ($release['homepage'] ?? self::RELEASES_URL);
        $object->package      = (string) ($release['package'] ?? '');
        $object->tested       = self::WP_TESTED;
        $object->requires     = self::WP_REQUIRES;
        $object->requires_php = self::PHP_REQUIRES;
        $object->last_updated = (string) ($release['published_at'] ?? '');
        $object->name         = self::PLUGIN_NAME;
        $object->author       = self::PLUGIN_AUTHOR;
        $object->homepage     = self::RELEASES_URL;
        $object->icons        = array();

        return $object;
    }

    /**
     * 本地版本信息（无更新时使用）
     */
    private static function currentRelease()
    {
        return array(
            'version'      => self::$plugin_version,
            'homepage'     => self::RELEASES_URL,
            'package'      => '',
            'body'         => '',
            'published_at' => '',
        );
    }

    /**
     * 从 Release 附件中解析下载链接，优先 .zip 附件
     */
    private static function resolvePackageUrl($data)
    {
        $assets = isset($data['assets']) && is_array($data['assets']) ? $data['assets'] : array();
        foreach ($assets as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $name = isset($asset['name']) && is_string($asset['name']) ? strtolower($asset['name']) : '';
            $url  = isset($asset['browser_download_url']) && is_string($asset['browser_download_url']) ? $asset['browser_download_url'] : '';
            if ($url !== '' && substr($name, -4) === '.zip') {
                return esc_url_raw($url);
            }
        }
        return isset($data['zipball_url']) && is_string($data['zipball_url']) ? esc_url_raw($data['zipball_url']) : '';
    }
    /**
     * 去掉 tag 的 v 前缀，校验 semver 格式
     */
    private static function normalizeVersion($version)
    {
        $version = trim($version);
        $version = preg_replace('/^v/i', '', $version);
        return is_string($version) && preg_match('/^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/', $version)
            ? $version
            : '';
    }

    /**
     * 格式化 Release Body 为 HTML changelog
     */
    private static function formatChangelog($body)
    {
        $body = trim($body);
        if ($body === '') {
            return '暂无更新说明。';
        }
        return nl2br(esc_html($body));
    }

    /**
     * 返回插件 slug（目录名）
     */
    private static function slug()
    {
        return dirname(plugin_basename(self::$plugin_file));
    }
}
