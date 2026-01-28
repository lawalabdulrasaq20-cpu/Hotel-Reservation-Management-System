<?php
session_start();

unset($_SESSION['reservation_success'], $_SESSION['reservation_data']);

header('Location: index.php');
exit;
