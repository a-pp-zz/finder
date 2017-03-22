# README #

### Find files via PHP ###

* Search files, filter by types, sort items, ignore or not hidden files
* 1.0

### Usage ###

```
#!php
$files = Finder::factory('/Users/coolswitcher/Sites/photo/storage')
								->filter ('types', 'mov, avi')
								->find('name');

var_dump($files);
```