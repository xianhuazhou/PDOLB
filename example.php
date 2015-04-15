<?php

require_once __DIR__ . '/pdolb.php';

$pdo = PDOLB::getInstance();
$stmt = $pdo->prepare('show tables');
$stmt->execute(array());
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
$stmt->closeCursor();
$stmt = null;
$pdo = null;
?>
