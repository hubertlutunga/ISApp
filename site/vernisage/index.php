<?php
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$target = '../vernissage/index.php' . ($queryString !== '' ? '?' . $queryString : '');

header('Location: ' . $target, true, 302);
exit;
