<?php
define('TM_ADMIN', true);
require_once __DIR__ . '/../includes/db.php';
log_activity('logout', 'user', $_SESSION['admin_id'] ?? null, 'Yönetici çıkışı');
$_SESSION = [];
session_destroy();
redirect('admin/login.php');
