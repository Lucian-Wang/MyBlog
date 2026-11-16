<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

http_response_code(404);

$pdo = getPDO();
$site = $pdo->query('SELECT * FROM site_info WHERE id=1')->fetch();
$theme = $site['theme'] ?? 'default';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/tpl/{$theme}");
$twig = new \Twig\Environment($loader, ['cache' => false]);
echo $twig->render('404.html', ['site' => $site]);
