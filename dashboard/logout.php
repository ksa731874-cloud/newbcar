<?php

session_start();


// Include config.php file
require_once 'config.php';

// Included classes
require_once 'classes/db.php';
require_once 'classes/user.php';

$User = new User();

// Check if user already logged
if($User->logOut()) {
    $connect='login.php';
    echo"<script> window.location.href=\"$connect\";</script>";
}

?>