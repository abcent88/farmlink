<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once
__DIR__
.'/../vendor/autoload.php';

require_once
__DIR__
.'/env.php';

loadEnv(
__DIR__
.'/../.env'
);


function sendMail(
$to,
$subject,
$body
){

$mail=
new PHPMailer(true);

try{

$mail->isSMTP();

$mail->Host=
$_ENV['MAIL_HOST'];

$mail->SMTPAuth=
true;

$mail->Username=
$_ENV['MAIL_USER'];

$mail->Password=
$_ENV['MAIL_PASS'];

$mail->SMTPSecure=
PHPMailer::ENCRYPTION_SMTPS;

$mail->Port=
465;

(int)$_ENV['MAIL_PORT'];

$mail->setFrom(

$_ENV['MAIL_USER'],

$_ENV['MAIL_FROM']

);

$mail->addAddress(
$to
);

$mail->isHTML(
true
);

$mail->Subject=
$subject;

$mail->Body=
$body;

$mail->send();

return true;

}catch(
Exception $e
){

echo $mail->ErrorInfo;

return false;

}

}
?>
