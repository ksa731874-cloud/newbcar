<?php

//Functions file


//Application Name
$app_name = 'airlines';
//----------------------------------------------
//Database connection data
$host_name = 'localhost';
$username = 'root';
$password = '';


$db_name = 'dalatew';

//----------------------------------------------
//Connect to database
$db_connection = mysqli_connect($host_name, $username, $password);
//----------------------------------------------
//Use Database
$use_db = 'USE ' . $db_name;
if (!mysqli_query($db_connection, $use_db)) {
    echo "يرجى تعديل معلومات الاتصال بقاعدة البيانات";
    die();
}

//----------------------------------------------
//Create Tables If Not Exist Any Table
$count = 'SELECT count(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "' . $db_name . '"';
$result = mysqli_query($db_connection, $count);
$r = @mysqli_fetch_assoc($result);
if ($r['total'] < 1) {


    $create_tbl_users = "CREATE TABLE users(

    id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY ,

    user_name text NOT NULL ,
    code text NOT NULL ,
    approve text NOT NULL ,
    password text NOT NULL

    )";


    $services = "CREATE TABLE services(

    id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY ,

    region text NOT NULL ,
    services text NOT NULL ,
    player text NOT NULL ,
    duration text NOT NULL ,
    gender text NOT NULL ,
    payment text NOT NULL ,
    the_date text NOT NULL

    )";

    mysqli_query($db_connection, $create_tbl_users); //Create users Table
    mysqli_query($db_connection, $services); //Create services Table


}
//----------------------------------------------
