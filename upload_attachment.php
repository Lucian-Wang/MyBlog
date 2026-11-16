<?php
require_once __DIR__ . '/config.php';
$pdo = getPDO();

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_FILES && isset($_FILES['attachment-file'])) {
    $file = $_FILES['attachment-file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allow = ['pdf','doc','docx','xls','xlsx','zip','rar','txt','ppt','pptx','md'];
        if (in_array($ext, $allow)) {
            $name = date('YmdHis') . '_' . mt_rand(1000,9999) . '.' . $ext;
            $path = $uploadDir . $name;
            move_uploaded_file($file['tmp_name'], $path);

            // 插入数据库
            $stmt = $pdo->prepare('INSERT INTO attachments (savename, originname) VALUES (?, ?)');
            $stmt->execute([$name, $file['name']]);

            $url = '/uploads/' . $name;
            echo json_encode([
                'success' => 1,
                'message' => '上传成功!',
                'url' => $url,
                'filename' => $file['name'],
                'savename' => $name
            ]);
            exit;
        }
    }
}
echo json_encode([
    'success' => 0,
    'message' => '上传失败！'
]);