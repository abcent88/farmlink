<?php

function loadEnv(
$file
){

if(
!file_exists($file)
){

return;

}

$lines=
file(
$file,
FILE_IGNORE_NEW_LINES
|
FILE_SKIP_EMPTY_LINES
);

foreach(
$lines
as
$line
){

$line=
trim(
$line
);

if(
$line===''
||
str_starts_with(
$line,
'#'
)
){

continue;

}

if(
strpos(
$line,
'='
)
===false
){

continue;

}

list(
$key,
$value
)=
explode(
'=',
$line,
2
);

$_ENV[
trim($key)
]
=
trim($value);

}

}
?>
