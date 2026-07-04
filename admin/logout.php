<?php
require_once '../config/db.php';
$_SESSION = [];
session_destroy();
redirect(APP_URL . '/admin/login.php');