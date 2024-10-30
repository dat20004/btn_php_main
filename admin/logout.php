<?php
session_start();
unset($_SESSION['cur_login']);
header('location: ../login.php');
?>