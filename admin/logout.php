<?php
require '../config.php';
session_destroy();
redirect('../admin/login.php');
