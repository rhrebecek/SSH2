<?php

/**
 * SSH2 libs
 * 
 * @author Radek Hřebeček <rhrebecek@gmail.com>
 * @copyright  Copyright (c) 2012 Radek Hřebeček (https://github.com/rhrebecek)
 * @license New BSD License
 * @link https://github.com/rhrebecek/SSH2
 *
 * @example
 * 	Working with an object is very simple
 * 		
 * ////////////////////////////////////////////////
 * 
 * 		include 'SSH2.php';
 * 
 * 		$ssh = new \SSH2;
 * 		$ssh->login('root', 'password', 'example.com');
 * 		$ssh->exec('ls --all');
 * 
 * 		echo $ssh->getOutput();
 * 
 * /////////////////////////////////////////////////
 */

include "libs/SSH2.php";


$ssh = new SSH2;
$ssh->login('root', 'password', 'example.com')
	->exec('ls -all')
	->getOutputBlackScreen();
