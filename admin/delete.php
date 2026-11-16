<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id=?');
    $stmt->execute([$id]);
}
header("Location: manage.php");
exit;