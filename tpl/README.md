# 博客模板制作说明

本博客系统前端模板基于 [Twig](https://twig.symfony.com/) 模板引擎，支持多套模板，并可在后台管理页面切换前台主题。

---

## 📁 目录结构

```
/tpl
  /default          # 默认主题
    index.html      # 首页模板
    view.html       # 文章详情模板
    archive.html    # 归档页模板
    404.html        # 404页面
  /yourtheme        # 你的自定义主题
    index.html
    view.html
    archive.html
    404.html
  README.md         # 本文档
```

---

## 🎨 伪静态链接

本系统已支持伪静态，模板中请使用以下格式：

| 页面 | 伪静态 URL | 原 URL |
|------|------------|---------|
| 首页 | `/` | `/index.php` |
| 分页 | `/page/2` | `/index.php?page=2` |
| 文章详情 | `/article/34` | `/view.php?id=34` |
| 归档 | `/archive` | `/archive.php` |

---

## 📄 模板文件说明

### 1. 首页模板：`index.html`

**可用变量：**
- `site` - 站点信息
  - `site.title` - 网站标题
  - `site.subtitle` - 网站副标题
  - `site.footer` - 页脚内容
  - `site.theme` - 当前主题名
- `articles` - 文章列表
  - `art.id` - 文章ID
  - `art.title` - 文章标题
  - `art.date` - 发布日期
  - `art.summary` - 文章摘要
- `page` - 当前页码
- `pages` - 总页数

**示例：**
```html
<h1>{{ site.title|e }}</h1>

{% for art in articles %}
  <a href="/article/{{ art.id }}">{{ art.title|e }}</a>
  <span>{{ art.date|date('Y年m月d日') }}</span>
{% endfor %}

<div class="pagination">
  {% if page > 1 %}
    {% if page == 2 %}
      <a href="/">← 上一页</a>
    {% else %}
      <a href="/page/{{ page - 1 }}">← 上一页</a>
    {% endif %}
  {% endif %}
  
  {% for i in 1..pages %}
    {% if i == page %}
      <span class="current">{{ i }}</span>
    {% else %}
      {% if i == 1 %}
        <a href="/">{{ i }}</a>
      {% else %}
        <a href="/page/{{ i }}">{{ i }}</a>
      {% endif %}
    {% endif %}
  {% endfor %}
  
  {% if page < pages %}
    <a href="/page/{{ page + 1 }}">下一页 →</a>
  {% endif %}
</div>
```

---

### 2. 文章详情页：`view.html`

**可用变量：**
- `site` - 站点信息（同上）
- `article` - 当前文章
  - `article.id` - 文章ID
  - `article.title` - 标题
  - `article.date` - 发布日期
  - `article.content_html` - 已解析的 HTML 内容

**示例：**
```html
<h1>{{ article.title|e }}</h1>
<p>{{ article.date|date('Y年m月d日') }}</p>
<div class="content">{{ article.content_html|raw }}</div>
```

---

### 3. 归档页：`archive.html`

**可用变量：**
- `site` - 站点信息
- `archives` - 按年月分组的文章
  - `year_month` - 年月（格式：2025-06）
  - `articles` - 该月的文章列表

**示例：**
```html
{% for year_month, articles in archives %}
  <h2>{{ year_month|replace({'-':'年'}) }}月</h2>
  <ul>
    {% for article in articles %}
      <li>
        <a href="/article/{{ article.id }}">{{ article.title|e }}</a>
        <span>{{ article.date|date('Y-m-d') }}</span>
      </li>
    {% endfor %}
  </ul>
{% endfor %}
```

---

### 4. 404 页面：`404.html`

**可用变量：**
- `site` - 站点信息

**示例：**
```html
<h1>404</h1>
<p>页面未找到</p>
<a href="/">返回首页</a>
```

---

## ✨ Twig 常用语法

| 功能 | 语法 | 说明 |
|------|------|------|
| 输出变量 | `{{ var }}` | 自动转义 |
| 转义输出 | `{{ var|e }}` | HTML 安全输出 |
| 原样输出 | `{{ var|raw }}` | 不转义（用于 HTML）|
| 日期格式化 | `{{ var|date('Y年m月d日') }}` | 日期格式化 |
| For 循环 | `{% for item in list %}...{% endfor %}` | 循环 |
| If 判断 | `{% if cond %} ... {% else %} ... {% endif %}` | 条件判断 |
| 注释 | `{# 这是注释 #}` | 模板注释 |

更多语法：[Twig 官方文档](https://twig.symfony.com/doc/3.x/templates.html)

---

## 🎯 如何创建自定义主题

1. 在 `/tpl/` 目录下创建新文件夹，例如 `mytheme`
2. 复制 `default/` 目录下的所有 `.html` 文件到新文件夹
3. 修改 HTML 和样式（推荐在 `/assets/style.css` 中统一管理样式）
4. 在后台「站点设置」中切换到你的新主题

---

## 📝 注意事项

- 所有模板必须放在 `/tpl/主题名/` 目录下
- 每个主题至少需要：`index.html`、`view.html`、`archive.html`、`404.html`
- 建议使用 `/assets/style.css` 统一管理样式
- 伪静态链接格式必须正确，否则链接无法访问
