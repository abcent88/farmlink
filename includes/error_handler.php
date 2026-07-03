<?php

function appError(
$message
){

error_log(
$message
);

}


function appFail(
$message='Something went wrong.'
){

http_response_code(
500
);

exit(
$message
);

}
?>
