<?php

namespace epgcore;

//---------------------------------------------------------------------------//
// author: pukomuko <salna@ktl.mii.lt> 
// date:   2001.03.15
// web:    http://pukomuko.esu.lt
// info:   template engine
//
// Warning: tags and some code were modified by Tomas Šivickas
//
//---------------------------------------------------------------------------//
// copyleft license
//
// this software is provided 'as-is', without any express or implied
// warranty. in no event will the authors be held liable for any damages
// arising from the use of this software.
//
// permission is granted to anyone to use this software for any purpose,
// including commercial applications, and to alter it and redistribute it
// freely, subject to the following restrictions: 
//
// 1. the origin of this software must not be misrepresented;
//	  you must not claim that you wrote the original software. 
//	  if you use this software in a product, an acknowledgment 
//	  in the product documentation would be appreciated but is not required.
//
// 2. altered source versions must be plainly marked as such, 
//	  and must not be misrepresented as being the original software.
//
// 3. mail about the fact of using this class in production 
//	  would be very appreciated.
//
// 4. this notice may not be removed or altered from any source distribution.
//
//---------------------------------------------------------------------------//
// changes:
//
//	2004.04.21
//		- warining on empty file
//		* v1.10.1
//
//	2004.03.04
//		+ TPL_LOOPOPT -> TPL_OPTLOOP
//		+ TPL_PARSEDLOOP -> TPL_PARSEDLOOP
//		* v1.10
//
//	2004.03.03
//		+ TPL_LOOP_INNER_PARSED
//		+ TPL_LOOP_INNER_OPTIONAL
//		* v1.10beta2
//
//	2004.03.02
//		+ TPL_STRIP_UTF_HEADER
//		+ TPL_PARSEDLOOP
//		+ TPL_LOOPOPT
//		* v1.10beta
//		+ utf header matching
//
//	2004.03.01
//		- process() bugfix
//		* v1.9.4
//
//	2003.10.20
//		- parse() bugfix
//		* v1.9.3
//
//	2003.08.12
//		- remove_nonjs bugfix
//		* v1.9.2
//
//	2003.06.29
//		* optional now works with 0 and '0'
//		* v1.9.1
//
//	2003.06.19
//		+ custom block syntax, thanks to G. van den Hoven
//		+ set_block_syntax()
//		+ tie_loop()
//		+ tie_var()
//		* v1.9
//
//	2003.06.17
//		* parse() works faster, thanks to Sergej Kurakin
//		- remove_nonjs bugfix
//		+ error handler support
//
//	2003.06.16
//		- set_root() bug
//
//	2003.03.14
//		+ process() now accepts parameters as bits, thanks to Audrius Karabanovas
//		+ set_params() set default parameters for process()
//		* v1.8.1
//		
//	2003.03.14
//		+ phpdoc comments
//		* v1.8
//
//	2002.11.13
//		- $block_names no more
//		- bug in extract blocks
//
//	2002.09.24
//		* parse now accpets string instead of handle
//		+ optional() <opt>
//		* root change
//
//	2002.03.23
//		- bug with templates caintaining { a { b }
//		* v1.7.1
//
//	2002.03.03
//		* fopen fread fclose instead of implode(file()), up to 3x faster
//		+ remove_nonjs - remove only variables that have no spaces in them
//		* speed improvements, got rid of list() = each()
//		+ license :]
//		* v1.7
//
//	2002.03.02
//		- deleted space before each line in the parsed template.
//		- error in method error() :]
//		* v1.6.2 note released :]
//
//	2001.12.06
//		- bug then text had only }
//		* v1.6.1
//
//	2001.10.31
//		+ error on unclosed block
//		+ nested blocks
//		* v1.6
//		
//	2001.09.17
//		- fixed bug with 'keep'
//
//	2001.09.02
//		+ get_var_silent()
//
//	2001.08.09
//		+ error handler
//
//	2001.07.04
//		+ one pass substitution
//
//	2001.05.28
//		+ block name recording
//		* v1.5.1
//
//	2001.05.10
//		- bug in documentation
//
//	2001.05.08
//		* first public release v1.5
//
//	2001.04.04
//		* changed blocks setup, constructor parameters change
//		- error bug
//		- some warnings

/**
*/
define('TPL_LOOP',		1);
define('TPL_NOLOOP',	2);
define('TPL_INCLUDE',	4);
define('TPL_APPEND',	8);
define('TPL_FINISH',	16);
define('TPL_OPTIONAL',	32);
define('TPL_LOOP_INNER_PARSED',64);
define('TPL_LOOP_INNER_OPTIONAL',	128);

define('TPL_PARSEDLOOP', TPL_LOOP | TPL_LOOP_INNER_PARSED);
define('TPL_OPTLOOP',	TPL_PARSEDLOOP | TPL_LOOP_INNER_OPTIONAL);


define('TPL_BLOCK',		1);
define('TPL_BLOCKREC',	2);
define('TPL_STRIP_UTF_HEADER',	4);

class phemplate
{

	/**
	* variables and blocks
	*/
	var $vars = array();

	/**
	* loops container
	*/
	var $loops = array();

	/**
	* dir of template files
	*/
	var $root = '';

	/**
	* what to do with unknown variables in template?
	*/
	var $unknowns = 'comment';

	/**
	* default parameters for process() loop, append, finish
	*/
	var $parameters = 0;
	

	/**
	* object having method report($level, $msg)
	* you can catch error messages from template.
	*/
	var $error_handler = null;
	

	
	/**
	* start tag for block
	*/
	var $block_start_string = '<!-- block name="|" -->';

	/**
	* end tag for block
	*/
	var $block_end_string = '<!-- /block name="|" -->';

	/**
	*	constructor
	*/
	function __construct( $root_dir = '', $unknowns = 'keep', $params = 0)
	{
		$this->set_root($root_dir);
		$this->set_unknowns($unknowns);
		$this->set_params($params);
	}

	/**
	*	check and set template root dir
	*/
	function set_root($root)
	{
		if (empty($root)) return;
		if (!is_dir($root))
		{
			$this->error("phemplate::set_root(): $root is not a directory.", 'warning');
			return false;
		}
		
		$this->root = $root;
		return true;
	}

	/**
	*	what to do with unknown variables in template?
	*	keep
	*	remove
	*	remove_nonjs
	*	comment
	*	space
	*/
	function set_unknowns($unk)
	{
		$this->unknowns = $unk;
	}

	/**
	* set default parameters for process()
	*/
	function set_params($params)
	{
		$this->parameters = $params;
	}

	/**
	*	read file template into $vars
	*	@param string $handle - name of handle for file contents
	*	@param int $blocks - 1 for <block> search, 2 for nested blocks
	*
	*/
	function set_file($handle, $filename = "", $blocks = false)
	{
			if ($filename == "") 
			{
				$this->error("phemplate::set_file(): filename for handle '$handle' is empty.", 'fatal');
				return false;
			}
			$this->vars[$handle] = $this->read_file($filename);
			if ($blocks & TPL_STRIP_UTF_HEADER)
			{
				$header = substr($this->vars[$handle], 0, 3);
				if ("\xEF\xBB\xBF" == $header) $this->vars[$handle] = substr($this->vars[$handle], 3);
			}
			if ($blocks) { $this->extract_blocks($handle, $blocks & TPL_BLOCKREC); }
			return true;

	}


	/**
	*	set handle and value,
	*	if value is array, all elements will be named: handle.key.subkey
	*/
	function set_var($var_name, $var_value)
	{
		if (is_array($var_value)) 
		{
			foreach($var_value as $key=>$value)
			{
				$this->set_var($var_name . '.' . $key, $value); // recursion for array branches
			}
		}
		else // normal variable
		{
			$this->vars[$var_name] = $var_value;
		}
	}
	
	//mano papildyta set var funkcija, uzsetinanti konstanta
	function set_const($const)
	{
		if (defined($const)) 
		{
			$this->set_var($const, constant($const));
		}
	}

	/**
	*	tie value with variable
	*	it's not possible to update array keys, just values
	*/
	function tie_var($var_name, &$var_value)
	{
		if (is_array($var_value)) 
		{
			$list = array_keys($var_value);
			foreach($list as $key)
			{
				$this->tie_var($var_name . '.' . $key, $var_value[$key]); // recursion for array branches
			}
		}
		else // normal variable
		{
			$this->vars[$var_name] =& $var_value;
		}
	}

	/**
	*	value of $handle
	*	raises error if $handle undefined
	*/
	function get_var($handle)
	{
		if (!isset($this->vars[$handle])) { $this->error("phemplate(): no such handle '$handle'", 'warning'); }
		return $this->vars[$handle];
	}

	/**
	*	value of $handle, no error if no handle
	*/
	function get_var_silent($handle)
	{
		if (!isset($this->vars[$handle])) { $this->vars[$handle] = ''; return ''; }
		return $this->vars[$handle];
	}

	/**
	*	assign array to loop handle
	*	@param string $loop_name - name for a loop
	*	@param int $loop - loop data
	*/
	function set_loop($loop_name, $loop)
	{
		if (!$loop) $loop = 0;
		$this->loops[$loop_name] = $loop;
	}

	/**
	*	tie array with loop
	*/
	function tie_loop($loop_name, &$loop)
	{
		if (!$loop) $loop = 0;
		$this->loops[$loop_name] =& $loop;
	}

	/**
	*	extracts blocks from handle, and returns cleaned up version
	*
	*	must be quite fast, because every found block is imediately taken out of string
	*
	*
	*	@since 2001.10.31 works with nested blocks [js]
	*	@since 2003.06.19 supports customized block syntax [js]
	*
	*/
	function extract_blocks($bl_handle, $recurse = false)
	{

		$str = $this->get_var($bl_handle);
		if (!$str) return $str;
		$bl_start = 0;

		list($bll, $blr) = explode('|', $this->block_start_string);
		$strlen = strlen($bll);


		// find them and clean from parent handle
		while(is_long($bl_start = strpos($str, $bll, $bl_start)))
		{
			$pos = $bl_start + $strlen;

			$endpos = strpos($str, $blr, $pos);
			$handle = substr($str, $pos, $endpos-$pos);

			$tag = $bll.$handle.$blr;
			$endtag = str_replace('|', $handle, $this->block_end_string);

			$start_pos = $bl_start + strlen($tag);
			$end_pos = strpos($str, $endtag, $bl_start);
			if (!$end_pos) { $this->error("phemplate(): block '$handle' has no ending tag", 'fatal'); }
			$bl_end = $end_pos + strlen($endtag);

			$block_code = substr($str, $start_pos, $end_pos-$start_pos);
			
			$this->set_var($handle, $block_code);

			$part1 = substr($str, 0, $bl_start);
			$part2 = substr($str, $bl_end, strlen($str));
			
			$str = $part1 . $part2;

			if ($recurse) { $this->extract_blocks($handle, 1); }

		}

		$this->set_var($bl_handle, $str);

	}


	/**
	*	search for <include> tags in $handle
	*	be carefull with paths
	*/
	function include_files($handle)
	{
		$str = $this->get_var($handle);

		while(is_long($pos = strpos($str, '<!-- include filename="')))
		{
			$pos += 19;
			$endpos = strpos($str, '" -->', $pos);
			$filename = substr($str, $pos, $endpos-$pos);
			$tag = '<!-- include filename="'.$filename.'" -->';

			
			$include = $this->read_file($filename);

			$str = str_replace($tag, $include, $str);
		}

		return $str;
	}


	/**
	*	searches for all set loops in $handle, returns text of $handle with parsed loops
	*	@param bool $noloop - must be true, if you want noloop statement
	*	@return string
	*/
	function parse_loops($handle, $noloop = false)
	{
		$str = $this->get_var($handle);
		
		reset($this->loops);
		
		//while ( list($loop_name, $loop_ar) = each($this->loops) ) 		{
		foreach ($this->loops as $loop_name=>$loop_ar)
		{
			$start_tag = strpos($str, '<!-- loop name="'.$loop_name.'" -->');

			$start_pos = $start_tag + strlen('<!-- loop name="'.$loop_name.'" -->');
			if (!$start_pos) continue;
			$end_pos = strpos($str, '<!-- /loop name="'.$loop_name.'" -->');

			$loop_code = substr($str, $start_pos, $end_pos-$start_pos);
			$org_loop_code = $loop_code;
			
			$start_tag = substr($str, $start_tag, strlen('<!-- loop name="'.$loop_name.'" -->'));
			$end_tag = substr($str, $end_pos, strlen('<!-- /loop name="'.$loop_name.'" -->'));

			if($loop_code != ''){
					
					$new_code = '';

					// clean <noloop... statement from loopcode
					if ($noloop & TPL_NOLOOP)
					{
						
						$nl_start_tag = strpos($loop_code, '<!-- noloop name="'.$loop_name.'" -->');
						$nl_start_pos = $nl_start_tag + strlen('<!-- noloop name="'.$loop_name.'" -->');

						if ($nl_start_pos)
						{
							
							$nl_end_pos = strpos($loop_code, '<!-- /noloop name="'.$loop_name.'" -->');

							$noloop_code = substr($loop_code, $nl_start_pos, $nl_end_pos - $nl_start_pos);
	 
							
							$nl_start_tag = substr($loop_code, $nl_start_tag, strlen('<!-- noloop name="'.$loop_name.'" -->'));
							$nl_end_tag = substr($loop_code, $nl_end_pos, strlen('<!-- /noloop name="'.$loop_name.'" -->'));
							$loop_code = str_replace($nl_start_tag.$noloop_code.$nl_end_tag, '', $loop_code);
						}

					}

					if (is_array($loop_ar))
					{

						if ($noloop & TPL_LOOP_INNER_PARSED)
						{
							for($i = 0; isset($loop_ar[$i]); $i++)

							{
								$temp_code = $loop_code;
								// rememeber loop variables

								$array_keys = array_keys($loop_ar[$i]);
								$this->set_var($loop_name, $loop_ar[$i]);
								if ($noloop & TPL_LOOP_INNER_OPTIONAL) $temp_code = $this->optional($loop_code);
								$temp_code = $this->parse($temp_code);
								$new_code .= $temp_code;

								// cleanup loop variables for next generation
								foreach ($array_keys as $key) unset($this->vars[$loop_name.'.'.$key]);

							} // for loop
						}
						else
						{
							// repeat for every row in array
							// for (reset($loop_ar); $row = current($loop_ar); next($loop_ar))
							$ar_keys = array_keys($loop_ar);
							$ar_size = count($ar_keys);
							for($i = 0; ($i< $ar_size); $i++)
							{
								$temp_code = $loop_code;

								foreach( $loop_ar[$ar_keys[$i]] as $k=>$v)
								{
									$temp_code = str_replace( '{'. $loop_name. '.' .$k. '}', $v, $temp_code);
								}
								$new_code .= $temp_code;
							}
						}
					} elseif ($noloop & TPL_NOLOOP)
					{
							$new_code = $noloop_code;
					}


					$str = str_replace($start_tag.$org_loop_code.$end_tag, $new_code, $str);
			} // if loop code
			
		} // repeat for loop names
	
		return $str;
	}
	

	/**
		substitute text and returns parsed string
		@author ZaZa (Sergej Kurakin) 2003.05.05 21:52
	*/
	function parse($string)
	{

		$str = explode('{', $string);

		$res = '';

		for ($i = 0; isset($str[$i]); $i++)
		{
			if ($i === 0)
			{
				$res .= $str[$i];
			}
			else
			{
				$line = explode('}', $str[$i]);
				$key = $line[0];
				unset($line[0]);

				if ( $key && isset($this->vars[$key]) )
				{
					$res .= $this->vars[$key].implode('}', $line);
				}
				else
				{
					switch ($this->unknowns)
					{
						case "keep":
							$res .= '{'.$key;
							if (count ($line) >	0) 
							{
								$res .= '}';
								$res .= implode('}', $line);
							}
						break;

						case "remove":
								$res .= implode('', $line);
						break;

						case "remove_nonjs":
								if (!empty($key) && ((false === strpos($key, ' ')) && (false === strpos($key, "\n")) && (false === strpos($key, "\t"))))
								{
									$res .= implode('}', $line);
								}
								else
								{
									$res .= '{'.$key;
									if (count ($line) >	0) 
									{
										$res .= '}';
										$res .= implode('}', $line);
									}
								}
						break;

						case "comment":
								$res .= '<!-- '.$key.' -->'.implode('', $line);
						break;
						
						case "space":
								$res .= '&nbsp;'.implode('', $line);
						break;

					}
				}
			}
		}

		return $res;
	}


	/**
	*	process left variables
	*	@deprecated parse() now handles unknown variables
	*/
	function finish($str)
	{
		switch ($this->unknowns)
		{
			case "keep":
			break;

			case "remove":
			$str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
			break;

			case "comment":
			$str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- {\\1} -->", $str);
			break;
			
			case "space":
			$str = preg_replace('/{([^ \t\r\n}]+)}/', "&nbsp;", $str);
			break;

		}

		return $str;
	}

	/**
	*	search for optional tag
	*	@param string $str text
	*	@return string
	*/
	function optional($str)
	{
		$bl_start = 0;

		// extract and clean them from parent handle
		while(is_long($bl_start = strpos($str, '<!-- opt name="', $bl_start)))
		{
			$pos = $bl_start + 15;

			$endpos = strpos($str, '" -->', $pos);
			$varname = substr($str, $pos, $endpos-$pos);
			
			$tag = '<!-- opt name="'.$varname.'" -->';
			$endtag = '<!-- /opt name="'.$varname.'" -->';

			$end_pos = strpos($str, $endtag, $bl_start);
			if (!$end_pos) { $this->error("phemplate(): optional '$varname' has no ending tag", 'fatal'); }

			$bl_end = $end_pos + strlen($endtag);


			$part1 = substr($str, 0, $bl_start);
			$part2 = substr($str, $bl_end, strlen($str));
			
			$value = $this->get_var_silent($varname);

			if ($value || $value === 0 || $value === '0')
			{
				$start_pos = $bl_start + strlen($tag);

				$block_code = substr($str, $start_pos, $end_pos-$start_pos);
				
				$str = $part1 . $this->parse($block_code) . $part2;
			}
			else
			{
				$str = $part1 . $part2;
			}

		}

		return $str;
	}

	/**
	*	does everything: loops, includes, variables, concatenation
	*	if $loop is 0 (not false, but 0) default parameters are used
	*	thanks ShiVAs for idea to have $loop as parameters.
	*
	*	@param $loop - loop, noloop or all parameters ored. 
	*	@param $append - append processed stuff to target, else $target is overwritten
	*	@return string
	*/
	function process($target, $handle, $loop = false, $include = false, $append = false, $finish = false, $optional = false)
	{
		if ($loop === 0) $loop = $this->parameters;
		
		$noloop = false;
		$parsedloop = false;
		$loopopt = false;
		if ($loop > 2)
		{
			$noloop = $loop & TPL_NOLOOP;
			$include = $loop & TPL_INCLUDE;
			$append = $loop & TPL_APPEND;
			$finish = $loop & TPL_FINISH;
			$optional = $loop & TPL_OPTIONAL;
			$parsedloop = $loop & TPL_LOOP_INNER_PARSED;
			$loopopt = $loop & TPL_LOOP_INNER_OPTIONAL;
		} 
		else
		{
			$noloop = $loop & TPL_NOLOOP;
		}
		
		if ($append and isset($this->vars[$target]))
		{
			$app = $this->get_var($target); // preserve old info
		}
		else
		{
			$app = '';
		}

		$this->set_var($target, $this->get_var($handle)); // copy contents

		if ($include) { $this->set_var($target, $this->include_files($target)); }


		if ($noloop) { $this->set_var($target, $this->parse_loops($target, TPL_NOLOOP | $loopopt | $parsedloop)); }
		elseif ($loop) { $this->set_var($target, $this->parse_loops($target, $loopopt | $parsedloop)); }

		if ($optional) { $this->set_var($target, $this->optional($this->get_var_silent($target))); }

		if ($append) { $this->set_var($target, $app . $this->parse($this->get_var_silent($target))); }
				else { $this->set_var($target, $this->parse($this->get_var($target))); }

		if ($finish) { $this->set_var($target, $this->finish($this->get_var($target))); }

		return $this->get_var($target);
	}


	/**
	*	spits out file contents
	*	@access	private
	*/
	function read_file($filename) 
	{
		$filename = $this->root . $filename;

		if (!file_exists($filename))
		{
			$this->error("phemplate::read_file(): file $filename does not exist.", 'fatal');
			return '';
		}

		$tmp = false;
		
		$filesize = filesize($filename);
		if ($filesize)
		{
			$tmp = fread($fp = fopen($filename, 'r'), $filesize);
			fclose($fp);
		}
		return $tmp;
	}

	/**	
	*	free mem used by loop
	*/
	function drop_loop($loop_handle)
	{
		if (isset($this->loops[$loop_handle])) unset($this->loops[$loop_handle]);
	}


	/**	
	*	free mem used by var
	*/
	function drop_var($handle)
	{
		if (isset($this->vars[$handle])) unset($this->vars[$handle]);
	}

	/**
	*	set error handler
	*/
	function set_error_handler(&$eh)
	{
		$this->error_handler =& $eh;
	}

	/**
	* change block syntax
	*/
	function set_block_syntax($start, $end)
	{
		if (!strpos($start, '|')) $this->error("phemplate::set_block_syntax(): no '|' in start tag", 'fatal');
		if (!strpos($end, '|')) $this->error("phemplate::set_block_syntax(): no '|' in end tag", 'fatal');
		$this->block_start_string = $start;
		$this->block_end_string = $end;
	}
	
	/**
	*	error report
	*/
	function error( $msg, $level = '')
	{
		if (isset($this->error_handler))
		{
			$lvl = E_USER_WARNING;
			if ('fatal' == $level) $lvl = E_USER_ERROR;
			$this->error_handler->report($lvl, $msg);
		}
		else 
		{
			echo "\n<br><font color='#CC0099'><b>$level:</b> $msg</font><br>\n";
			if ('fatal' == $level) { exit; }
		}
	}

}

?>