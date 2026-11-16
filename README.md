<div align="center">

# 🚀 MyBlog · Lucian 博客系统

一个简洁、现代、优雅的 PHP Markdown 博客系统

![PHP](https://img.shields.io/badge/PHP-8.0.2%2B-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)
![Twig](https://img.shields.io/badge/Template-Twig-56a5f7?style=flat)

</div>

---

## ✨ 功能特性

### 📝 文章管理
- ✅ Markdown 编辑器（Editor.md）
- ✅ 支持文章发布、编辑、删除
- ✅ 文章隐藏/显示
- ✅ 文章列表分组显示
- ✅ 分页显示

### 🖼️ 媒体管理
- ✅ 图片批量上传
- ✅ 图片管理（预览、删除）
- ✅ 附件管理（下载、删除、PDF预览）
- ✅ 拖拽上传
- ✅ 上传进度提示

### 🎨 主题系统
- ✅ 多主题支持
- ✅ 后台一键切换主题
- ✅ 基于 Twig 模板引擎
- ✅ 自定义主题开发

### 🌐 SEO 友好
- ✅ 伪静态支持
- ✅ 优雅的 URL
- ✅ 优雅的 404 页面
- ✅ 响应式设计
- ✅ 移动端适配

### 🔧 现代化 UI
- ✅ 现代化后台界面
- ✅ 卡片式设计
- ✅ 渐变色主题
- ✅ Toast 通知提示
- ✅ 动画效果

---

## 🚀 快速开始

### 环境要求

- PHP 8.0.2+（当前 composer.lock 中 Twig 3.19.0 的最低要求）
- MySQL 5.7+
- Apache（推荐）或 Nginx
- Composer（PHP 依赖管理）

### 安装步骤

#### 1. 克隆或下载项目

```bash
git clone https://github.com/Lucian-Wang/MyBlog.git
cd MyBlog
```

#### 2. 安装依赖

```bash
composer install
```

#### 3. 创建数据库

在 MySQL 中创建数据库：

```sql
CREATE DATABASE blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 4. 运行安装向导

访问 `http://your-domain/install.php`，按照向导完成安装：

- 步骤 1：环境检测
- 步骤 2：数据库配置
- 步骤 3：导入数据库

默认管理员账号：
- 用户名：`admin`
- 密码：`admin123`

⚠️ **请登录后立即修改密码！**

#### 5. 配置伪静态（Apache）

项目已包含 `.htaccess` 文件，确保 Apache 启用了 `mod_rewrite`。

#### 配置伪静态（Nginx）

在 Nginx 配置中添加：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^/page/(\d+)$ /index.php?page=$1 last;
        rewrite ^/article/(\d+)$ /view.php?id=$1 last;
        rewrite ^/archive$ /archive.php last;
    }
}
```

---

## 📁 目录结构

```
blog/
├── admin/              # 后台管理目录
│   ├── index.php        # 后台入口
│   ├── manage.php     # 文章管理
│   ├── write.php      # 写文章
│   ├── images.php     # 图片管理
│   ├── attachments.php # 附件管理
│   ├── settings.php   # 站点设置
│   ├── login.php      # 登录页面
│   └── logout.php     # 退出登录
├── assets/             # 静态资源
│   ├── style.css      # 前台样式
│   ├── admin.css    # 后台样式
│   └── editor.md/    # Markdown 编辑器
├── tpl/                # 模板目录
│   ├── default/        # 默认主题
│   │   ├── index.html
│   │   ├── view.html
│   │   ├── archive.html
│   │   └── 404.html
│   └── README.md       # 模板开发文档
├── uploads/            # 上传文件目录
├── vendor/             # Composer 依赖
├── index.php           # 前台首页
├── view.php            # 文章详情
├── archive.php         # 归档页
├── 404.php            # 404 处理
├── config.php          # 配置文件（请复制 config.example.php）
├── install.php         # 安装向导
├── install.sql           # 数据库初始化
└── .htaccess         # Apache 伪静态
```

---

## 🎯 使用说明

### 后台管理

访问 `/admin/` 进入后台管理。

### 前台页面

- 首页：`/`
- 文章详情：`/article/{id}`
- 归档：`/archive`
- 分页：`/page/{page}`

### 模板开发

详见 [tpl/README.md](tpl/README.md)。

---

## 🎨 主题开发

1. 在 `/tpl/` 目录下创建新主题文件夹
2. 复制 `default/` 目录下的模板文件
3. 修改 HTML 和样式
4. 在后台「站点设置」中切换主题

详见 [tpl/README.md](tpl/README.md)。

---

## 🔒 安全建议

1. 安装完成后删除 `install.php` 和 `install.sql`
2. 修改默认管理员密码
3. 保护好 `config.php` 文件，不要提交到公开仓库
4. 定期备份数据库

---

## 📝 更新日志

### v2.0
- ✨ 全新现代化 UI 设计
- 🎨 卡片式布局
- 🖼️ 批量上传功能
- 📢 Toast 通知提示
- 🔗 伪静态支持
- 🎯 优化的用户体验

### v1.0
- 📝 基础文章管理
- 🖼️ 图片和附件管理
- 🎨 多主题支持
- 🔐 用户登录系统

---

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

---

## 📄 许可证

MIT License

---

## 👏 致谢

- [Twig](https://twig.symfony.com/) - 模板引擎
- [Editor.md](https://pandao.github.io/editor.md/) - Markdown 编辑器
- [Parsedown](https://parsedown.org/) - Markdown 解析器

---

<div align="center">

**如果这个项目对你有帮助，请给个 ⭐ Star！**

</div>

## 维护与反馈

维护者：[Lucian](https://lucian.wang/) · 邮箱：im@lucian.wang · [问题反馈](https://github.com/Lucian-Wang/MyBlog/issues)
