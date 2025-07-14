<?php
session_start();
session_unset();
session_destroy();
header("Location: /easy-rake/public/index.php");
exit;
?> 