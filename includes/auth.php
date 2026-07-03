<?php


ini_set(
'session.cookie_httponly',
1
);

ini_set(
'session.use_only_cookies',
1
);


session_set_cookie_params([

'secure'=>false,

'httponly'=>true,

'samesite'=>'Strict'

]);


if(
session_status()
===PHP_SESSION_NONE
){

session_start();

}
