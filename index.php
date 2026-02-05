<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Redirect to quotation list
redirect('quotation_list.php');
?>