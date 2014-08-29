really-simple-templates
=======================

A really simple template class for php that supports functions and static methods as filters

### Usage:

```php
<?php

require('rst.php');

$template = '
	replace a variable {{foo}} in the template
	replace a variable with a function filter {{bar:strtoupper}}
	replace a variable with a static class method {{baz:myclass::mystaticfunction}}
';

$template_with_different_syntax = '
	replace a variable [[foo]] in the template using user defined syntax
	replace a variable with a function filter [[bar:strtoupper]]
	replace a variable with a static class method [[baz:myclass::mystaticfunction]]
';

$binds = array(
	'foo' => 'foo variable',
	'bar' => 'bar var',
	'baz' => 'and this is baz',
);

class myclass{
	public static function mystaticfunction($text){
		return '--'.$text.'--';
	}
}

echo RST::render($template, $binds)."\n";

RST::interpolate_start('[[');
RST::interpolate_end(']]');

echo RST::render($template_with_different_syntax, $binds)."\n";

```