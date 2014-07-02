<?php
define('ROOT', '/srv/www/eventdoer/');
define('WEBROOT', 'http://event.do/er/');
define('LANGUAGE', 'de');

require ROOT.'Language/Main.php';

$hostname = 'localhost';
$database = '<database>';
$username = '<username>';
$password = '<password>';

$db = new PDO('mysql:host='.$hostname.';dbname='.$database, $username,$password);

// load unloaded classes
spl_autoload_register(function ($class) {
    include ROOT.'Classes/'.$class.'.class.php';
});

$daemon = new Daemon($db);
$daemon->run();
