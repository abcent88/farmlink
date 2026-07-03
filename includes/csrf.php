<?php

if(
session_status()
===
PHP_SESSION_NONE
){



}


require_once
__DIR__
.'/error_handler.php';
require_once __DIR__.'/auth.php';


if(
empty(
$_SESSION['csrf_token']
)
){

$_SESSION['csrf_token']
=
bin2hex(
random_bytes(32)
);

}


function csrf_token()
{

return
$_SESSION['csrf_token'];

}


function verify_csrf()
{

if(

!isset(
$_POST['csrf_token']
)

||

!hash_equals(

$_SESSION['csrf_token'],

$_POST['csrf_token']

)

){

appError(
'Invalid CSRF Token'
);

appFail(
'Your session expired or request is invalid. Please try again.'
);

}

}
?>
