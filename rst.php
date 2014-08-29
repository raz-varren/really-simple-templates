<?php

/*
	//Example Usage:
	
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

*/

//ReallySimpleTemplates
class RST_Exception extends \Exception{ }
class RST{
	private static $interpolate_start = '{{';
	private static $interpolate_end   = '}}';
	
	public static function render($template, $binds = array()){
		self::parse($template, $binds);
		$rendered_text = str_replace(array_keys($binds), array_values($binds), $template);
		return $rendered_text;
	}
	
	private static function parse($template, &$binds){
		$regex = '/'.preg_quote(self::$interpolate_start).'(.*)(\:.*)?'.preg_quote(self::$interpolate_end).'/U';
		$matches = array();
		preg_match_all($regex, $template, $matches);
		
		list($_not_used, $keys, $filters) = $matches;
		
		foreach($filters as $idx => $filter){
			if(!isset($binds[$keys[$idx]])) 
				throw new RST_Exception('not all variables bound: '.$keys[$idx]);
			
			if(!$filter) continue;
			
			$filter = substr($filter, 1);
			$is_static = (strpos($filter, '::') !== false);
			
			if($is_static){
				list($class, $method) = explode('::', $filter);
				if(!method_exists($class, $method)) throw new RST_Exception('method ('.$method.') does not exist for class ('.$class.')');
				$binds[$keys[$idx].$filters[$idx]] = call_user_func_array(array($class, $method), array($binds[$keys[$idx]]));
			}else{
				if(!function_exists($filter)) throw new RST_Exception('function ('.$filter.') does not exist');
				$binds[$keys[$idx].$filters[$idx]] = call_user_func_array($filter, array($binds[$keys[$idx]]));
			}
		}
		
		self::parse_binds($binds);
	}
	
	private static function parse_binds(&$binds){
		$new_array = array();
		foreach($binds as $key => $val){
			$new_key = self::$interpolate_start.(string)$key.self::$interpolate_end;
			$new_array[$new_key] = $val;
		}
		$binds = $new_array;
	}
	
	public static function interpolate_start($start_with = '{{'){
		self::$interpolate_start = $start_with;
	}
	
	public static function interpolate_end($end_with = '}}'){
		self::$interpolate_end = $end_with;
	}
}