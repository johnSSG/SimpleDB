<?php
class Simple_DB {
    /*Copyright (c) 2013 Simplicity Solutions Group http://simplicitysolutionsgroup.com

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE. */
	
	function __construct($type) {
		$path       = DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'Simple_DB.php';
		$this->dir  = str_replace($path, '', realpath(__FILE__)).DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR;
		$this->type = $type.DIRECTORY_SEPARATOR;
		if(file_exists($this->dir.$type) === false) mkdir($this->dir.$type);
	}
	
	private function check($file) {
		if(file_exists($this->dir.$this->type.$file.'.json') === false) :
			return $file;
		else :
			return $this->check(uniqid());
		endif;		
	}
	
	public function delete($id) {
		$file = $id;
		if(strpos($id, '.json') === false) $file = $id.'.json';
		$del = unlink($this->dir.$this->type.$file);
		return ($del ? true : false);
	}
	
	public function get($file = '', $json = false) {
		$output = array();
		if($file) :
			if(gettype($file) == 'string') :
				$file = str_replace('.json', '', $file).'.json';
				if($file !== '.json') :
					if(file_exists($this->dir.$this->type.$file)) :
						return ($json ? file_get_contents($this->dir.$this->type.$file) : json_decode(file_get_contents($this->dir.$this->type.$file)));
					endif;
				endif;
			endif;
		else :
			$scan = $this->getFiles();
			foreach($scan as &$file) :
				$id   = str_replace('.json', '', $file);
				$file = $id.'.json';
				$output[$id] = ($json ? file_get_contents($this->dir.$this->type.$file) : json_decode(file_get_contents($this->dir.$this->type.$file)));
			endforeach;			
		endif;
		return (empty($output) ? false : $output);
	}
	
	public function getJSON($file = '') {
		$get = $this->get($file, true);
	}
	
	public function getFiles() {
		$output = array();
		$scan   = scandir($this->dir.$this->type);
		$removed = array(
			'error_log',
			'_notes',
			'.htaccess',
			'.htpasswd'
		);
		foreach($scan as &$file) :
			if(is_dir($this->dir.$this->type.$file) === false) :
				if(in_array($file, $removed) === false) $output[] = $file;
			endif;
		endforeach;
		return $output;
	}
	
	private function indent($json) {
	    $result      = '';
	    $pos         = 0;
	    $strLen      = strlen($json);
    	$indentStr   = '  ';
	    $newLine     = "\n";
	    $prevChar    = '';
	    $outOfQuotes = true;
	    for ($i=0; $i<=$strLen; $i++) {
	        $char = substr($json, $i, 1);
	        if ($char == '"' && $prevChar != '\\') {
	            $outOfQuotes = !$outOfQuotes;
	        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
	            $result .= $newLine;
	            $pos --;
	            for ($j=0; $j<$pos; $j++) {
	                $result .= $indentStr;
	            }
	        }
	        $result .= $char;
	        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
	            $result .= $newLine;
	            if ($char == '{' || $char == '[') {
	                $pos ++;
    	       }
	           for ($j = 0; $j < $pos; $j++) {
    	           $result .= $indentStr;
        	   }
	        }
    	    $prevChar = $char;
	    }
		return $result;		
	}
	
	public function post($c) {
		$file = $this->check(uniqid());
		$put  = file_put_contents($this->dir.$this->type.$file.'.json', $this->indent(json_encode((object) $c)));
		return ($put ? $file : false);
	}
	
	public function put($id, $c) {
		$content = $this->indent(json_encode((object) $c));
		$file    = str_replace('.json', '', $id).'.json';
		$put     = file_put_contents($this->dir.$this->type.$file, $content);
		return ($put ? (object) $c : false);
	}
	
	public function query($q) {
		$arr    = array();
		parse_str($q, $arr);
		$get    = $this->get();
		$output = array();
		if($get) :
			foreach($get as $key => &$val) :
				if($this->testQueryArray($arr, urldecode(http_build_query($val)))) $output[$key] = $val;
			endforeach;
		endif;
		return (empty($output) ? false : $output);
	}
	
	public function returnSingleId($a) {
		return ($a ? array_shift(@array_keys($a)) : false);
	}
	
	private function shiftArray($arr) {
		if(count($arr) == 1) :
			return array_shift($arr);
		else :
			return $arr;
		endif;
	}
	
	private function testQueryArray($array, $objectString) {
		$testArray = array();
		foreach($array as $key => $value) :
			$testArray[] = $key.'='.$value;
		endforeach;
		$count = count($testArray);
		$testCount = 0;
		foreach($testArray as $value) :
			if(preg_match('~\b' . preg_quote($value) . '\b~i', $objectString)) $testCount++;
		endforeach;
		return ($count == $testCount ? true : false);
	}
	
	public function timestamp($id) {
		return @filemtime($this->dir.$this->type.$id.'.json');
	}
}
?>
