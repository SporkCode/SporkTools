#!/usr/bin/php
<?php
require_once 'PHPUnit/Autoload.php';

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

PHPUnit_TextUI_Command::main();
