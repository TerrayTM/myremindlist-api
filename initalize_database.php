<?php

//Connect to database
include "./database_connect.php";

//Create a table for 'users'
$database_connection->query("CREATE TABLE users (
    id int(10) not null primary key auto_increment,
    email varchar(255) not null,
    phone varchar(16) not null,
    password varchar(255) not null,
    firstName varchar(255) not null,
    lastName varchar(255) not null,
    verifyEmail boolean default 0 not null,
    verifyPhone boolean default 0 not null
);");

//Create a table for 'reminds'
$database_connection->query("CREATE TABLE reminds (
    id int(10) not null primary key auto_increment,
    message varchar(255) not null,
    time int(10) not null,
    userID int(10) not null,
    params varchar(255) not null
);");

//Create a table for 'tokens'
$database_connection->query("CREATE TABLE tokens (
    id int(10) not null primary key auto_increment,
    token varchar(255) not null,
    userID int(10) not null,
    expiry int(10) not null
);");

//Create a table for 'verification'
$database_connection->query("CREATE TABLE verification (
    id int(10) not null primary key auto_increment,
    code varchar(225) not null,
    userID int(10) not null,
    type varchar(255) not null
);");

?>