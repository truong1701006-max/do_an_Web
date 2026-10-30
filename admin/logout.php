<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin_id']);
flash('success', 'Đã đăng xuất admin.');
redirect('admin/login.php');
