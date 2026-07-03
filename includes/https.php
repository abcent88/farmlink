<?php

if(

empty(
$_SERVER['HTTPS']
)

&&

$_SERVER['HTTP_HOST']
!='localhost'

){

header(

'Location: https://'

.$_SERVER['HTTP_HOST']

.$_SERVER['REQUEST_URI']

);

exit;

}
?>
