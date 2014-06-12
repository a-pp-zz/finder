<?php
chdir(realpath(dirname(__FILE__)));
include 'finder.class.php';

$files = Finder::factory('/Users/coolswitcher/Sites/photo/storage')
								->setTypes ('mov')
								->Find()
								->getAll();

var_dump($files);										 

$files = Finder::factory('/Users/coolswitcher/Sites/photo/storage')
								->setTypes ( array ('mp4', 'mov') )
								->setIgnores ('Валенсия')
								->Find()
								->getAll('mtime,filename,owner');

var_dump($files);	