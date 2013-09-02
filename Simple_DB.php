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
			$this->check(uniqid());
		endif;		
	}
	
	public function delete($id) {
		$file = $id;
		if(strpos($id, '.json') === false) $file = $id.'.json';
		$del = unlink($this->dir.$this->type.$file);
		if($del) :
			return true;
		else :
			return false;
		endif;
	}
	
	public function get($file = '') {
		$file = str_replace('.json', '', $file).'.json';
		if($file !== '.json') :
			if(file_exists($this->dir.$this->type.$file)) :
				return json_decode(file_get_contents($this->dir.$this->type.$file));
			else :
				return false;
			endif;
		endif;
		$output = array();
		$scan   = $this->getFiles();
		foreach($scan as $file) :
			$id   = str_replace('.json', '', $file);
			$file = str_replace('.json', '', $file).'.json';
			$output["$id"] = json_decode(file_get_contents($this->dir.$this->type.$file));
		endforeach;
		if(empty($output)) :
			return false;
		else :
			return $output;
		endif;
	}
	
	public function getJSON($file = '') {
		$file = str_replace('.json', '', $file).'.json';
		if($file !== '.json') :
			if(file_exists($this->dir.$this->type.$file)) :
				return file_get_contents($this->dir.$this->type.$file);
			else :
				return false;
			endif;
		endif;
		$output = array();
		$scan   = $this->getFiles();
		foreach($scan as $file) :
			$id   = str_replace('.json', '', $file);
			$file = str_replace('.json', '', $file).'.json';
			$output["$id"] = file_get_contents($this->dir.$this->type.$file);
		endforeach;
		if(empty($output)) :
			return false;
		else :
			return $this->shiftArray($output);
		endif;		
	}
	
	private function getFiles() {
		$output = array();
		$scan   = scandir($this->dir.$this->type);
		foreach($scan as $file) :
			if(is_dir($this->dir.$this->type.$file) === false) :
				if($file !== 'error_log' && $file !== '_notes' && $file !== '.htaccess') :
					$output[] = $file;
				endif;
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
	
	private function objectToQueryString($obj) {
		$output = '';
		$count  = 0;
		if(is_object($obj) || is_array($obj)) :
			foreach($obj as $key => $val) :
				if(is_string($val)) :
					if($count) $output .= '&';
					$output .= $key.'='.$val;
					$count = $count + 1;
				endif;
			endforeach;
		endif;
		return $output;
	}
	
	public function post($c) {
		if(is_object($c) === false) :
			$con = (object) $c;
		else :
			$con = $c;
		endif;
		$content = $this->indent(json_encode($con));
		$file    = $this->check(uniqid());
		$put     = file_put_contents($this->dir.$this->type.$file.'.json', $content);
		if($put) :
			return str_replace('.json', '', $file);
		else :
			return false;
		endif;
	}
	
	public function put($id, $c) {
		if(is_object($c) === false) :
			$con = (object) $c;
		else :
			$con = $c;
		endif;
		$content = $this->indent(json_encode($con));
		$file    = str_replace('.json', '', $id).'.json';
		$put     = file_put_contents($this->dir.$this->type.$file, $content);
		if($put) :
			return str_replace('.json', '', $file);
		else :
			return json_decode(file_get_contents($this->dir.$this->type.$file.'.json'));
		endif;
	}
	
	public function query($q) {
		$arr    = array();
		parse_str($q, $arr);
		$get    = $this->get();
		$output = array();
		if($get) :
			foreach($get as $key => $val) :
				$objectString = $this->objectToQueryString($val);
				if($this->testQueryArray($arr, $objectString)) $output["$key"] = $val;
			endforeach;
		endif;
		if(empty($output)) :
			return false;
		else :
			return $output;
		endif;
	}
	
	public function returnSingleId($a) {
		if($a) :
			$arr = array_keys($a);
			return array_shift($arr);
		else :
			return false;
		endif;
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
			if(preg_match('~\b' . $value . '\b~i', $objectString)) $testCount++;
		endforeach;
		if($count == $testCount) :
			return true;
		else :
			return false;
		endif;
	}
	
	public function timestamp($id) {
		return @filemtime($this->dir.$this->type.$id.'.json');
	}
}
?>
