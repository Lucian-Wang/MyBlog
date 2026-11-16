<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
$pdo = getPDO();

$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 15;
$total = $pdo->query('SELECT COUNT(*) FROM attachments')->fetchColumn();
$totalPages = ceil($total / $pageSize);

$stmt = $pdo->prepare('SELECT * FROM attachments ORDER BY upload_date DESC LIMIT ?, ?');
$stmt->bindValue(1, ($page-1)*$pageSize, PDO::PARAM_INT);
$stmt->bindValue(2, $pageSize, PDO::PARAM_INT);
$stmt->execute();
$attachment_files = $stmt->fetchAll();

function formatSize($size) {
    if ($size >= 1073741824) return round($size / 1073741824,2).' GB';
    if ($size >= 1048576) return round($size / 1048576,2).' MB';
    if ($size >= 1024) return round($size / 1024,2).' KB';
    return $size . ' B';
}

function getFileIcon($ext) {
    $icons = [
        'pdf' => '📄',
        'doc' => '📝', 'docx' => '📝',
        'xls' => '📊', 'xlsx' => '📊',
        'ppt' => '📽️', 'pptx' => '📽️',
        'zip' => '📦', 'rar' => '📦', '7z' => '📦',
        'txt' => '📃',
        'md' => '📋',
        'json' => '🔧',
        'xml' => '🔤',
        'html' => '🌐', 'htm' => '🌐',
        'css' => '🎨',
        'js' => '⚡', 'ts' => '⚡',
        'php' => '🐘',
        'py' => '🐍',
        'sql' => '🗄️',
        'default' => '📁'
    ];
    return $icons[strtolower($ext)] ?? $icons['default'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>附件管理</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="admin-header">文件管理</div>
    <div class="admin-navbar">
        <a href="/" target="_blank">前台首页</a>
        <a href="manage.php">文章管理</a>
        <a href="write.php">写文章</a>
        <a href="images.php">图片管理</a>
        <a href="attachments.php">文件管理</a>
        <a href="settings.php">站点设置</a>
        <a href="logout.php">退出</a>
    </div>
    <div class="section-padding">
        <div class="upload-area">
            <button type="button" class="upload-btn" id="custom-attachment-btn">
                <span class="icon">📤</span>
                <span>上传附件</span>
            </button>
            <input type="file" id="custom-attachment-input" multiple style="display:none;">
        </div>
        <div class="attach-container">
            <?php
            $show_files = 0;
            foreach ($attachment_files as $file):
                $ext = strtolower(pathinfo($file['savename'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','bmp'])) continue;
                $filePath = __DIR__ . '/../uploads/' . $file['savename'];
                $size = file_exists($filePath) ? filesize($filePath) : 0;
                $show_files++;
            ?>
            <div class="attach-card">
                <div class="attach-header">
                    <div class="attach-icon"><?= getFileIcon($ext) ?></div>
                    <div class="attach-info">
                        <div class="attach-name" title="<?=htmlspecialchars($file['originname'])?>"><?=htmlspecialchars($file['originname'])?></div>
                        <div class="attach-meta">
                            <span class="meta-tag">📦 <?=formatSize($size)?></span>
                            <span class="meta-tag">📅 <?=date('Y-m-d H:i', strtotime($file['upload_date']))?></span>
                            <span class="meta-tag">🏷️ <?=strtoupper($ext)?></span>
                        </div>
                    </div>
                </div>
                <div class="attach-actions">
                    <a class="action-btn download" href="/uploads/<?=htmlspecialchars($file['savename'])?>" download="<?=htmlspecialchars($file['originname'])?>">
                        <span>⬇️</span>
                        <span>下载</span>
                    </a>
                    <?php if ($ext === 'pdf'): ?>
                        <button class="action-btn preview" data-url="/uploads/<?=htmlspecialchars($file['savename'])?>">
                            <span>👁️</span>
                            <span>预览</span>
                        </button>
                    <?php endif;?>
                    <button class="action-btn delete" data-file="<?=htmlspecialchars($file['savename'])?>">
                        <span>🗑️</span>
                        <span>删除</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if ($show_files === 0): ?>
                <div class="empty-state">
                    <div class="icon">📁</div>
                    <div class="text">暂无附件</div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?=($page-1)?>">← 上一页</a>
            <?php endif; ?>
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <strong><?=$i?></strong>
                <?php else: ?>
                    <a href="?page=<?=$i?>"><?=$i?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
                <a href="?page=<?=($page+1)?>">下一页 →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="admin-footer">
        &copy; <?=date('Y')?> 博客后台
    </div>
</div>
<div class="pdf-modal" id="pdfDialog">
    <div class="pdf-content">
        <div class="pdf-header">
            <span class="pdf-title">PDF 预览</span>
            <span class="pdf-close">&times;</span>
        </div>
        <iframe class="pdf-iframe" src="" frameborder="0"></iframe>
    </div>
</div>
<script src="/assets/editor.md/lib/jquery.min.js"></script>
<script>
$('#custom-attachment-btn').on('click', function() {
    $('#custom-attachment-input').val('').click();
});
$('#custom-attachment-input').on('change', function() {
    var files = Array.from(this.files);
    if (!files.length) return;
    var $btn = $('#custom-attachment-btn');
    $btn.prop('disabled', true).html('<span class="icon">⏳</span><span>上传中...</span>');
    let completed = 0;
    files.forEach(function(file) {
        var fd = new FormData();
        fd.append('attachment-file', file);
        fetch('/upload_attachment.php', {
            method: 'POST',
            body: fd
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success && res.url && res.filename){
                location.reload();
            }else{
                alert(res.message || '上传失败');
            }
        })
        .catch(()=>alert('上传出错'))
        .finally(()=>{
            completed++;
            if(completed === files.length){
                $btn.prop('disabled', false).html('<span class="icon">📤</span><span>上传附件</span>');
            }
        });
    });
});

$('.action-btn.preview').on('click', function(){
    var url = $(this).attr('data-url');
    $('#pdfDialog .pdf-iframe').attr('src', url);
    $('#pdfDialog').show();
});
$('#pdfDialog').on('click', function(e){
    if(e.target.id === 'pdfDialog' || $(e.target).hasClass('pdf-close')) {
        $('#pdfDialog').hide();
        $('#pdfDialog .pdf-iframe').attr('src', '');
    }
});

$('.action-btn.delete').on('click', function(){
    if(!confirm('确定删除该附件吗？')) return;
    var $card = $(this).closest('.attach-card');
    var file = $(this).attr('data-file');
    $.post('delete_attachment.php', {file:file}, function(res){
        if(res.success){
            $card.css('transform', 'translateX(100%)').css('opacity', '0');
            setTimeout(function(){
                $card.remove();
                if ($('.attach-card').length === 0) {
                    $('.attach-container').html('<div class="empty-state"><div class="icon">📁</div><div class="text">暂无附件</div></div>');
                }
            }, 300);
        }else{
            alert(res.message || '删除失败');
        }
    }, 'json');
});
</script>
</body>
</html>
