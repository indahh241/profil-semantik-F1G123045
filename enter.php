<?php
require_once 'config/db.php';

// Set flag bahwa user sudah lewat welcome page
$_SESSION['entered'] = true;

// Redirect ke dashboard
redirect(APP_URL . '/index.php');