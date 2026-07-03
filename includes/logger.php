<?php


function logActivity(

$pdo,

$userId,

$action,

$details=''

){

try{

$stmt=
$pdo->prepare(

"

INSERT INTO activity_logs
(

user_id,

action,

details,

ip_address,

user_agent

)

VALUES
(

?,
?,
?,
?,
?

)

"

);


$stmt->execute([

$userId,

$action,

$details,

$_SERVER['REMOTE_ADDR']
?? 'unknown',

substr(

$_SERVER['HTTP_USER_AGENT']
?? '',

0,

255

)

]);


}catch(Exception $e){

error_log(

$e->getMessage()

);

}

}
?>
