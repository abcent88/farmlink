<?php

require_once
__DIR__
.'/../includes/env.php';

require_once
__DIR__
.'/../includes/error_handler.php';


loadEnv(
__DIR__
.'/../.env'
);


$host=
$_ENV['DB_HOST']
?? 'localhost';

$dbname=
$_ENV['DB_NAME']
?? '';

$user=
$_ENV['DB_USER']
?? '';

$password=
$_ENV['DB_PASS']
?? '';


try{

$pdo=
new PDO(

"mysql:host=$host;dbname=$dbname;charset=utf8mb4",

$user,

$password

);

$pdo->setAttribute(

PDO::ATTR_ERRMODE,

PDO::ERRMODE_EXCEPTION

);

$pdo->setAttribute(

PDO::ATTR_DEFAULT_FETCH_MODE,

PDO::FETCH_ASSOC

);

}catch(
PDOException $e
){

appError(
$e->getMessage()
);

appFail();

}
?>
