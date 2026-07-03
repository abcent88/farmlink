<?php


function notify(

$pdo,

$userId,

$title,

$message

){

try{

$stmt=
$pdo->prepare(

"

INSERT INTO notifications
(

user_id,

title,

message

)

VALUES
(

?,
?,
?

)

"

);


$stmt->execute([

$userId,

$title,

$message

]);

}catch(Exception $e){

error_log(

$e->getMessage()

);

}

}
?>
