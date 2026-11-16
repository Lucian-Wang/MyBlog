<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}

$pdo = getPDO();
$edit_id = $_GET['edit'] ?? '';
$title = $content = '';
$edit_mode = false;

if ($edit_id) {
    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id=?');
    $stmt->execute([$edit_id]);
    if ($row = $stmt->fetch()) {
        $title = $row['title'];
        $content = $row['content'];
        $edit_mode = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        if (!empty($_POST['edit_id'])) {
            $stmt = $pdo->prepare('UPDATE articles SET title=?, content=? WHERE id=?');
            $stmt->execute([$title, $content, intval($_POST['edit_id'])]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO articles (title, date, content) VALUES (?, NOW(), ?)');
            $stmt->execute([$title, $content]);
        }
        header("Location: manage.php");
        exit;
    } else {
        $error = "标题和内容不能为空";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$edit_mode?'编辑':'写'?>文章</title>
    <link rel="stylesheet" href="/assets/admin.css">
    <link rel="stylesheet" href="/assets/editor.md/css/editormd.min.css" />
    <style>
        .write-section {
            padding: 0 32px 24px 32px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 1.5em;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title .icon {
            font-size: 1.5em;
        }

        .form-actions-top {
            display: flex;
            gap: 10px;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px !important;
            min-width: 160px !important;
            width: 160px !important;
            height: 52px !important;
            max-height: 52px !important;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1em !important;
            font-weight: 600 !important;
            text-decoration: none;
            transition: all 0.2s ease;
            box-sizing: border-box !important;
            line-height: 1 !important;
        }

        .btn-secondary:hover {
            background: var(--bg-tertiary);
            border-color: #4f46e5;
            color: #4f46e5;
            transform: translateY(-2px);
        }

        .upload-area {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .upload-zone {
            flex: 1;
            min-width: 280px;
            padding: 20px;
            background: var(--bg-secondary);
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.05);
        }

        .upload-zone .icon {
            font-size: 2em;
            margin-bottom: 6px;
        }

        .upload-zone .text {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .upload-zone .subtext {
            font-size: 0.85em;
            color: var(--text-secondary);
        }

        .upload-zone input {
            display: none;
        }

        .form-footer {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .form-footer .btn-primary,
        .form-footer .btn-secondary {
            margin: 0 !important;
        }

        .form-footer a.btn-secondary {
            margin-left: 0 !important;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px !important;
            min-width: 160px !important;
            width: 160px !important;
            height: 52px !important;
            max-height: 52px !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white !important;
            border: none !important;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1em !important;
            font-weight: 600 !important;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-md);
            box-sizing: border-box !important;
            line-height: 1 !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.3s ease;
            border-left: 4px solid #4f46e5;
        }

        .toast.success {
            border-left-color: #10b981;
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast .toast-icon {
            font-size: 1.4em;
            flex-shrink: 0;
        }

        .toast .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast .toast-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .toast .toast-message {
            font-size: 0.9em;
            color: #6b7280;
            word-break: break-all;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .editor-wrapper {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .editor-wrapper:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        @media (max-width: 768px) {
            .write-section {
                padding: 0 20px 20px 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .form-actions-top,
            .form-footer {
                flex-direction: column;
            }

            .btn-secondary,
            .btn-primary,
            .upload-zone {
                width: 100%;
                min-width: auto;
            }

            .upload-area {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">后台管理</div>
    <div class="admin-navbar">
        <a href="/" target="_blank">前台首页</a>
        <a href="manage.php">文章管理</a>
        <a href="write.php">写文章</a>
        <a href="images.php">图片管理</a>
        <a href="attachments.php">文件管理</a>
        <a href="settings.php">站点设置</a>
        <a href="logout.php">退出</a>
    </div>
    <div class="write-section">
        <div class="page-header">
            <div class="page-title">
                <span class="icon"><?=$edit_mode ? '✏️' : '📝'?></span>
                <span><?=$edit_mode ? '编辑文章' : '写新文章'?></span>
            </div>
            <div class="form-actions-top">
                <a href="manage.php" class="btn-secondary">
                    <span>📋</span>
                    <span>文章列表</span>
                </a>
            </div>
        </div>
        <?php if (!empty($error)) echo "<div class='error-msg'>{$error}</div>"; ?>
        <form method="post" class="admin-form" id="articleForm">
            <input type="hidden" name="edit_id" value="<?=$edit_mode ? htmlspecialchars($edit_id) : ''?>">
            <label for="title">文章标题</label>
            <input type="text" name="title" id="title" value="<?=htmlspecialchars($title)?>" placeholder="请输入文章标题..." required>
            <label>内容（支持 Markdown）</label>
            <div class="upload-area">
                <div class="upload-zone" id="imageUploadZone">
                    <div class="icon">🖼️</div>
                    <div class="text">点击或拖拽上传图片</div>
                    <div class="subtext">支持 jpg、png、gif、webp</div>
                    <input type="file" id="imageUploadInput" accept="image/*" multiple>
                </div>
                <div class="upload-zone" id="attachmentUploadZone">
                    <div class="icon">📎</div>
                    <div class="text">点击或拖拽上传附件</div>
                    <div class="subtext">支持任意文件格式</div>
                    <input type="file" id="attachmentUploadInput" multiple>
                </div>
            </div>
            <div class="toast-container" id="toastContainer"></div>
            <div class="editor-wrapper">
                <div id="md-editor">
                    <textarea style="display:none;" name="content" id="content"><?=htmlspecialchars($content)?></textarea>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn-primary">
                    <span><?=$edit_mode ? '💾' : '🚀'?></span>
                    <span><?=$edit_mode ? '保存修改' : '发布文章'?></span>
                </button>
                <a href="manage.php" class="btn-secondary">
                    <span>❌</span>
                    <span>取消</span>
                </a>
            </div>
        </form>
    </div>
    <div class="admin-footer">
        &copy; <?=date('Y')?> 博客后台
    </div>
</div>

<script src="/assets/editor.md/lib/jquery.min.js"></script>
<script src="/assets/editor.md/editormd.min.js"></script>
<script>
window.mdEditor = editormd("md-editor", {
    width: "100%",
    height: 600,
    path : "/assets/editor.md/lib/",
    imageUpload : false,
    theme: "default",
    previewTheme: "default",
    editorTheme: "default",
    codeFold: true,
    searchReplace: true,
    saveHTMLToTextarea: true,
    htmlDecode: "style,script,iframe|on*",
    emoji: true,
    taskList: true,
    tocm: true,
    tex: true,
    flowChart: true,
    sequenceDiagram: true
});

function showToast(title, message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        info: '⏳',
        success: '✅',
        error: '❌'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, type === 'success' ? 4000 : 6000);
}

function setupUpload(zoneId, inputId, uploadUrl, insertCallback) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    
    zone.addEventListener('click', () => input.click());
    
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.style.borderColor = '#4f46e5';
        zone.style.background = 'rgba(79, 70, 229, 0.1)';
    });
    
    zone.addEventListener('dragleave', () => {
        zone.style.borderColor = '';
        zone.style.background = '';
    });
    
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.style.borderColor = '';
        zone.style.background = '';
        handleFiles(e.dataTransfer.files, uploadUrl, insertCallback);
    });
    
    input.addEventListener('change', (e) => {
        handleFiles(e.target.files, uploadUrl, insertCallback);
    });
}

function handleFiles(files, uploadUrl, insertCallback) {
    if (!files.length) return;
    
    let completed = 0;
    const total = files.length;
    
    Array.from(files).forEach(file => {
        showToast('正在上传', file.name, 'info');
        
        const fd = new FormData();
        fd.append(uploadUrl.includes('upload_attachment') ? 'attachment-file' : 'editormd-image-file', file);
        
        fetch(uploadUrl, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success && (res.url || (res.url && res.filename))) {
                insertCallback(res, file.name);
                showToast('上传成功', file.name, 'success');
            } else {
                showToast('上传失败', file.name + ' - ' + (res.message || '未知错误'), 'error');
            }
        })
        .catch(() => showToast('上传出错', file.name, 'error'))
        .finally(() => {
            completed++;
            if (completed === total) {
                document.getElementById('imageUploadInput').value = '';
                document.getElementById('attachmentUploadInput').value = '';
            }
        });
    });
}

setupUpload('imageUploadZone', 'imageUploadInput', '/upload.php', (res, fileName) => {
    window.mdEditor.insertValue(`![${fileName}](${res.url})\n`);
});

setupUpload('attachmentUploadZone', 'attachmentUploadInput', '/upload_attachment.php', (res, fileName) => {
    window.mdEditor.insertValue(`[${res.filename || fileName}](${res.url})\n`);
});
</script>
</body>
</html>
