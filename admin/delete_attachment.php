<?php
session_start();
require_once '../config.php';
if (empty($_SESSION['admin_login'])) {
    echo json_encode(['success'=>0,'message'=>'无权限']);
    exit;
}
$pdo = getPDO();
$file = $_POST['file'] ?? '';
$filePath = realpath(__DIR__ . '/../uploads/' . $file);
$uploads = realpath(__DIR__ . '/../uploads/');
if ($file && $filePath && strpos($filePath, $uploads)===0 && is_file($filePath)) {
    unlink($filePath);
    $stmt = $pdo->prepare('DELETE FROM attachments WHERE savename=?');
    $stmt->execute([$file]);
    echo json_encode(['success'=>1]);
} else {
    echo json_encode(['success'=>0,'message'=>'删除失败']);
}