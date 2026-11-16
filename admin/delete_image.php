<?php
session_start();
require_once '../config.php';
if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
$pdo = getPDO();
$img = $_POST['img'] ?? '';
if ($img) {
    $file = realpath(__DIR__ . '/../uploads/' . $img);
    $uploads_dir = realpath(__DIR__ . '/../uploads/');
    if ($file && strpos($file, $uploads_dir) === 0 && is_file($file)) {
        unlink($file);
        $stmt = $pdo->prepare('DELETE FROM images WHERE savename=?');
        $stmt->execute([$img]);
    }
}
header("Location: images.php");
exit;