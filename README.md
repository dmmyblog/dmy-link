# 大绵羊外链跳转插件

  

**插件版本**：1.3.5

**作者**：大绵羊  
**参与**：天无神话

## 插件描述

大绵羊外链跳转插件是专为 WordPress 站点开发的外部链接管理插件。插件的主要功能是拦截文章中的外部链接，在跳转前提醒用户即将离开本站，提升用户安全意识并优化用户体验。用户可以自由选择提示页面的样式，还可以通过白名单设置来避免对信任的外部链接进行干扰。

## 插件功能

### 1. 自动拦截外部链接  

插件会自动检查并拦截文章中的外部链接，所有非本站链接都会跳转到提示页面，提醒用户即将跳转到外部网站。

  

![自动拦截外部链接](https://github.com/user-attachments/assets/03539765-af6f-488b-8203-a7d9fdc5529d)

  

### 2. 白名单功能  

用户可以在插件设置中添加白名单链接，指定哪些外部链接不受拦截，直接访问。白名单链接无需加 `http://` 或 `https://`，每行一个即可。

  

<img width="1118" height="508" alt="image" src="https://github.com/user-attachments/assets/463a2aab-b98f-4904-9210-55b4f4f04369" />


  

### 3. 多种提示页面样式  

插件提供五种风格的提示页面，用户可以根据需求选择合适的样式：  

- **默认样式**  
- **哔哩哔哩风格**  
- **腾讯风格**  
- **CSDN风格**  
- **知乎风格**  
- **jump风格[1.3.5]**  
- **tiktok风格[1.3.5]**  
- **moxing风格[1.3.5]**  
  
<img width="1068" height="521" alt="image-13" src="https://github.com/user-attachments/assets/dbbe3b21-78e7-4861-b3e8-722c39a740bf" />


### 4. 自定义 Logo  

用户可以上传自定义 Logo 图片，替换插件的默认 Logo。如果不设置，插件不会默认使用网站的 Logo。
  

<img width="1393" height="510" alt="image" src="https://github.com/user-attachments/assets/b51a41b9-c7d5-4624-a7c3-e00f422ebd54" />

### 5. 外部链接重定向  

点击外部链接时，插件会首先跳转到提示页面，再通过重定向跳转到目标链接。

### 6. 易用的后台设置界面  

插件基于 Codestar Framework 提供了直观、易用的后台设置界面，用户可以轻松进行各项配置。
### 7.当跳转链接过期时，插件会引导用户到错误页面

![image](https://github.com/user-attachments/assets/5cefba03-736e-4e5f-af4e-b8e0045106c5)


## 使用方法

  
1. 安装并激活插件。
2. 进入 WordPress 后台，点击菜单中的“外链跳转插件”。
3. 在插件设置中，您可以：  
   - 配置外部链接白名单。  
   - 选择提示页面样式。  
   - 上传自定义 Logo。  
4. 保存设置后，插件将自动生效，所有文章中的外部链接都会进行拦截和跳转提示。

---

  

## 插件设置

- **基本设置**：提供插件总开关，可以随时启用或关闭插件功能。
- **白名单设置**：用户可以输入信任的外部链接，插件将跳过这些链接，不进行跳转提示。
- **样式设置**：提供八种不同的页面样式，包括默认、哔哩哔哩、腾讯、CSDN、知乎风格，以及1.3.5版本新增的jump、tiktok、moxing风格。
- **主题社区功能**：支持7b2主题圈子功能和子比主题社区帖子功能，可自定义CSS选择器。[1.3.5新增]
- **Logo 设置**：可以上传一个 Logo 图片，替换默认的 Logo，提升网站个性化。
- **安全设置**：提供两种验证方式（随机字符串+过期机制、AES加密+后端验证），可自定义过期时间和加密密钥。[1.3.5新增]
  
## 插件优势

- **提高用户安全**：提醒用户即将跳转到外部网站，防止潜在的钓鱼网站和恶意网站。
- **增强用户体验**：通过多样化的页面风格，提升提示页面的视觉效果和用户体验。
- **灵活配置**：支持白名单功能，避免不必要的干扰，同时提供多个风格，满足不同用户需求。


## 插件更新日志
###  v1.0.0初始版本  
✅样式增加：  默认样式,腾讯样式,CSDN样式,哔哩哔哩样式,知乎样式
### v1.1.1 
✅优化网站 HTML 结构  
✅适配手机端
### v1.2.0
✅防止恶意链接攻击  
✅ Token过期机制（原先通过Base64加密的方式，现改为Token过期机制，避免外链拦截被盗用或滥用）  
✅ 优化`transient`逻辑，提高性能和稳定性  
✅ 当跳转链接过期时，插件会引导用户到错误页面，并提供返回首页的按钮，避免用户困惑  
✅ 新增后台过期时间设置（默认5分钟，您可以自定义Token过期时间，进一步防止外链被盗用）  
✅ 更安全的URL处理机制，防止XSS攻击（采用随机字符串与时间戳结合）  
✅ 跳转后的Token不会立即失效，后台设置的过期时间到期后才会删除，避免刷新时链接失效  
✅ 无损链接权重：拦截仅作用于前端展示，原始外链依然保留在HTML源码中，不影响搜索引擎抓取与权重计算
### v1.3.5
✅AES加密模式: 使用AES-256-CBC加密，永久有效<br>
✅新增样式风格：jump风格、tiktok风格、moxing风格<br>
✅动态内容支持: JavaScript实时转换动态加载的链接<br>
✅输入验证: 所有用户输入经过WordPress安全函数处理<br>
✅URL验证: 使用`esc_url()`确保输出安全<br>
✅XSS防护: 模板输出使用安全转义<br>
✅访问控制: 开关控制和权限管理<br>
✅ WordPress transient存储机制<br>
✅适配子比主题以及7b2主题的圈子<br>
💻更新了共同开发 天无神话<br>

## 插件免责声明

- 本插件是开源免费使用的，但禁止修改插件的版权信息或将插件用于商业目的。插件作者保留所有版权。
## 插件支持
  
- **使用框架**：Codestar Framework  
- **社区支持**：欢迎加入插件使用qq群，我们将定期更新并提供支持。

---

  

## 作者联系方式

  

- **作者网站**：[大绵羊博客](https://dmyblog.cn)  

- **QQ群**：947328468
