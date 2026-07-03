<?php

if(
!isset(
$_SESSION['attempts']
)
){

$_SESSION['attempts']=0;

}

if(
$_SESSION['attempts']>5
){

die(
'Too many attempts.'
);

}
?>
