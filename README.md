# README #

### Find files via RecursiveIteratorIterator ###

* Search files, filter by types, sort items, ignore or not hidden files
* 2.0

### Usage ###

```
#!php
$finder = Finder::factory('path/to/dir')
			->types', ['mp4', 'mov']
			->exclude ('(foo|bar)')
			->exclude_paths('path')
			->hidden(FALSE)
			->find()
			->sort('size', TRUE); //name, date, type

$files = get_files(['mtime', 'size', 'owner', 'perms']);			

var_dump($files);
```