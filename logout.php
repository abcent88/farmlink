<?php

session_start();

session_unset();

session_destroy();

header("Location: /projects/farmlink/index.php");
exit;