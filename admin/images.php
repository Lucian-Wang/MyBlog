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
$total = $pdo->query('SELECT COUNT(*) FROM images')->fetchColumn();
$totalPages = ceil($total / $pageSize);

$stmt = $pdo->prepare('SELECT * FROM images ORDER BY upload_date DESC LIMIT ?, ?');
$stmt->bindValue(1, ($page-1)*$pageSize, PDO::PARAM_INT);
$stmt->bindValue(2, $pageSize, PDO::PARAM_INT);
$stmt->execute();
$imgs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>图片管理</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="admin-header">图片管理</div>
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
            <button type="button" class="upload-btn" id="custom-image-btn">
                <span class="icon">📤</span>
                <span>上传图片</span>
            </button>
            <input type="file" id="custom-image-input" accept="image/*" multiple style="display:none;">
        </div>
        <div class="images-grid">
            <?php if (count($imgs) === 0): ?>
                <div class="empty-state">
                    <div class="icon">🖼️</div>
                    <div class="text">暂无图片</div>
                </div>
            <?php else: ?>
                <?php foreach($imgs as $img): ?>
                <div class="image-card">
                    <div class="image-preview" data-src="/uploads/<?=htmlspecialchars($img['savename'])?>">
                        <img src="/uploads/<?=htmlspecialchars($img['savename'])?>" alt="<?=htmlspecialchars($img['originname'])?>">
                    </div>
                    <div class="image-info">
                        <div class="image-name" title="<?=htmlspecialchars($img['originname'])?>"><?=htmlspecialchars($img['originname'])?></div>
                        <div class="image-actions">
                            <button class="action-btn view" data-src="/uploads/<?=htmlspecialchars($img['savename'])?>">
                                <span>👁️</span>
                                <span>查看</span>
                            </button>
                            <form action="delete_image.php" method="post" onsubmit="return confirm('确定要删除这张图片吗？');" style="flex: 1; margin: 0;">
                                <input type="hidden" name="img" value="<?=htmlspecialchars($img['savename'])?>">
                                <button type="submit" class="action-btn delete" style="width: 100%;">
                                    <span>🗑️</span>
                                    <span>删除</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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

<div class="modal" id="imageModal">
    <span class="modal-close">&times;</span>
    <div class="modal-content">
        <img class="modal-image" src="" alt="">
    </div>
</div>

<script src="/assets/editor.md/lib/jquery.min.js"></script>
<script>
$('#custom-image-btn').on('click', function() {
    $('#custom-image-input').val('').click();
});
$('#custom-image-input').on('change', function() {
    var files = Array.from(this.files);
    if (!files.length) return;
    var $btn = $('#custom-image-btn');
    $btn.prop('disabled', true).html('<span class="icon">⏳</span><span>上传中...</span>');
    let completed = 0;
    files.forEach(function(file) {
        var fd = new FormData();
        fd.append('editormd-image-file', file);
        fetch('/upload.php', {
            method: 'POST',
            body: fd
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success && res.url){
                location.reload();
            }else{
                alert(res.message || '上传失败');
            }
        })
        .catch(()=>alert('上传出错'))
        .finally(()=>{
            completed++;
            if(completed === files.length){
                $btn.prop('disabled', false).html('<span class="icon">📤</span><span>上传图片</span>');
            }
        });
    });
});

$('.image-preview, .action-btn.view').on('click', function(){
    var src = $(this).attr('data-src') || $(this).closest('.image-card').find('.image-preview').attr('data-src');
    $('#imageModal .modal-image').attr('src', src);
    $('#imageModal').show();
});

$('#imageModal, #imageModal .modal-close').on('click', function(e){
    if(e.target.id === 'imageModal' || $(e.target).hasClass('modal-close')) {
        $('#imageModal').hide();
        $('#imageModal .modal-image').attr('src', '');
    }
});
</script>
</body>
</html>
