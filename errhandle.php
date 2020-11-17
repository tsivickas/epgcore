<?php
namespace epgcore;

class errorhandle
{


function __construct()
{
  set_exception_handler('myException');
}


/**
 * error handle
 */
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    // error string
    $errortype = array ( E_ERROR              => 'Error',
                         E_WARNING            => 'Warning',
                         E_PARSE              => 'Parsing Error',
                         E_NOTICE             => 'Notice',
                         E_CORE_ERROR         => 'Core Error',
                         E_CORE_WARNING       => 'Core Warning',
                         E_COMPILE_ERROR      => 'Compile Error',
                         E_COMPILE_WARNING    => 'Compile Warning',
                         E_USER_ERROR         => 'User Error',
                         E_USER_WARNING       => 'User Warning',
                         E_USER_NOTICE        => 'User Notice',
                         E_STRICT             => 'Runtime Notice',
                         E_RECOVERABLE_ERROR  => 'Catchable Fatal Error' );
    
	// set of errors for which a var trace will be saved
    //$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
   
    
    $err = "DATE: ".'['.date("Y-m-d H:i:s").'] IP: ['.((isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'').']'.
           ((defined('USERDATA_ID'))?' USER:'.USERDATA_ID :'') .
           ((defined('USERDATA_COMMUNITY'))?' ACC:'.USERDATA_COMMUNITY :'') .
           "\r\nTYPE: ".$errortype[$errno].
           "\r\nERROR: ".$errmsg.
           "\r\nFILE: ".$filename." (red.:".date ("Y-m-d H:i:s", filemtime($filename)).")".
           "\r\nLINE: ".$linenum.
           "\r\nQUERY: ".((isset($_SERVER) && isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:'');
    
	if(function_exists('debug_backtrace')){
        $err .="\r\nTRACE:";
        $backtrace = debug_backtrace();
        if ($backtrace && count($backtrace)>1) {
			array_shift($backtrace);
			foreach($backtrace as $i=>$l){
				//if (devpc) { var_dump($l); exit; }
				$err .="\r\n#$i";
				if(isset($l['class'])) $err .=" ".$l['class'];
				if(isset($l['type'])) $err .=" ".$l['type'];
				if(isset($l['function'])) $err .=" ".$l['function'];
				//if(isset($l['args'])) $err .=" ARGS[".str_replace(PHP_EOL, '', print_r($l['args'],1))."]";
				if(isset($l['file'])) $err .=" in {$l['file']}";
				if(isset($l['line'])) $err .=" on line {$l['line']}";
				
			}
		}
    }
	
	
	/*
    if (in_array($errno, $user_errors))
        
        if (function_exists('wddx_serialize_value')) { $err .= "\r\nVAR: ". wddx_serialize_value($vars, "Variables"); } else {
          foreach($vars as $k=>$v) {
              if (!is_array($v) && !empty($v)) { $err .= "\r\n$".$k.'= '.var_export($v,true).';'; }
          }
        }
		*/
		
    $err .= "\r\n\r\n";
   
    // issaugome
    //$file = @fopen('php_error.log', 'a');
    $file = @fopen(  ((stripos($filename,'test')===false)?'php_error.log':'php_error_test.log') , 'a');
	@fputs($file, $err);
	fclose($file);
   
}

//error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_PARSE);
error_reporting(E_ALL);
$old_error_handler = set_error_handler("userErrorHandler");



//exception default handler 
function myException($e) {

$filename = $e->getFile();
  
$err = "DATE: ".'['.date("Y-m-d H:i:s").'] IP: ['.((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'').']'.
"\r\nUNCAUGHT EXCEPTION: ".$e->getMessage().
"\r\nFILE: ".$filename.
"\r\nLINE: ".$e->getLine().
"\r\nTRACE: ".$e->getTraceAsString().
"\r\nQUERY: ".((isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:'');

$err .= "\r\n\r\n";
//$file = @fopen('php_error.log', 'a');
$file = @fopen(  ((stripos($filename,'test')===false)?'php_error.log':'php_error_test.log') , 'a');
@fputs($file, $err);
fclose($file);
echo $e->getMessage(); 
}


}


?>
