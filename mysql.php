<?php

namespace epgcore;

/**
 * @name		Wraped MySQL
 */

 

class mysql {
		
	var $mysql;

	
	// Constructor
	function mysql ($mysql = null)
	{
		$this->mysql = $mysql; // nustatome prisijungimą į duomenų bazę
	}
		
	function connected ()
	{
		return ($this->mysql !== false);
	}
	
	function connect ($host, $user, $password, $db) 
	{
		$this->mysql = mysqli_connect($host, $user, $password, $db);
		//jei nepavyko prisijungti
		if (is_bool($this->mysql) && (!$this->mysql))
			return false;
		
		mysqli_set_charset ( $this->mysql , 'utf8' );
		
		//var_dump(mysqli_character_set_name($this->mysql),mysqli_error($this->mysql));
					
		//jei nepavyko pasirinkti duomenų bazės
		return $this->mysql;

	}
	
	
	function select_db($db)
	{
    $isSelected = mysqli_select_db($this->mysql, $db);
    if (!$isSelected) return false;
		return true;
	}
	
	// paskutinės užklausos insert ID
	function insertId ()
	{
		return mysqli_insert_id($this->mysql);
	}

	// paveiktų eilučių skaičius
	function affectedRows ()
	{
		return mysqli_affected_rows($this->mysql);
	}
	
	function countRows ($sql) 
	{
		$result = $this->query($sql);
		return ((is_bool($result)) && ($result == false)) ? 0 : mysqli_num_rows($result);
	}
	
	// ištraukiame nurodytą lauką
	function getField ($sql, $name=0)
	{
		$result = $this->query($sql);
				
		if ((is_bool($result)) && ($result == false)) return false;
		if (mysqli_num_rows($result) == 0) return null;
		
		$row = mysqli_fetch_assoc($result);
		return (isset($row[$name])) ? $row[$name] : false;
	}

	// grąžina pirmą eilutę
	function getRow ($sql)
	{
        $result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		if (mysqli_num_rows($result) == 0) return null;
		
		return mysqli_fetch_assoc($result);
	}

	// grąžina eilučių masyvą
	function getRows ($sql)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
			$rows [] = mysqli_fetch_assoc($result);
			
		return $rows;
	}
    
    function getFields ($sql, $field=0)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
			//$rows [] = mysqli_result($result, $i,$field);
			$rows [] =  $this->mysqli_result($result, $i, $field);
			
		return $rows;
	}
	
	function getArray ($sql, $field=0, $val=0,$is_array=false)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($is_array) $rows [$xr[$field]][] = $xr[$val];
			else $rows [$xr[$field]] = $xr[$val];
		}
		return $rows;
	}
	
	
	function getKeys ($sql, $field=0, $is_array=false)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($is_array) $rows [$xr[$field]][] = $xr;
			else $rows [$xr[$field]] = $xr;
		}
		return $rows;
	}
  
  function rowsByKey ($sql, $field=0)
	{
		return $this->getKeys($sql, $field, true);
		
		/*
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			$rows [$xr[$field]][] = $xr;
		}
		return $rows;
		*/
	}
	
	
	function getMatrix ($sql, $f1='', $f2='')
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			$rows [ $xr[$f1] ][ $xr[$f2] ] = $xr;
		}
		return $rows;
	}
	
	





   //naujosios modernios f-jos

	function matrix ($sql, $f1='', $f2='', $is_array=false, $return_array=true)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($return_array===true) {
				if ($is_array) $rows [ $xr[$f1] ][ $xr[$f2] ] [] = $xr;
				else $rows [ $xr[$f1] ][ $xr[$f2] ] = $xr;
			} else {
				if ($is_array) $rows [ $xr[$f1] ][ $xr[$f2] ] [] = $xr[$return_array];
				else $rows [ $xr[$f1] ][ $xr[$f2] ] = $xr[$return_array];
			}
		}
		return $rows;
	}
	
	function rows ($sql, $field='', $is_array=false)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($is_array)  $rows [$xr[$field]][] = $xr;
			else $rows [$xr[$field]] = $xr;
		}
		return $rows;
	}
	
	
	function triple ($sql, $f1='', $f2='', $f3='', $is_array=false)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($is_array) $rows [ $xr[$f1] ][ $xr[$f2] ][ $xr[$f3] ] [] = $xr;
			else $rows [ $xr[$f1] ][ $xr[$f2] ][ $xr[$f3] ] = $xr;
		}
		return $rows;
	}
	
	// eof naujos f-jos
	
	
	
	
	
	function quad ($sql, $f1='', $f2='', $f3='',$f4='', $is_array=false)
	{
		$result = $this->query($sql);
		
		if ((is_bool($result)) && ($result == false)) return false;
		$count = mysqli_num_rows($result);
		if ($count == 0) return null;
		
		$rows = array ();
		for ($i = 0; $i < $count; $i++)
		{
			$xr = mysqli_fetch_assoc($result);
			if ($is_array) $rows [ $xr[$f1] ][ $xr[$f2] ][ $xr[$f3] ][ $xr[$f4] ] [] = $xr;
			else $rows [ $xr[$f1] ][ $xr[$f2] ][ $xr[$f3] ][ $xr[$f4] ] = $xr;
		}
		return $rows;
	}
	
	// eof naujos f-jos
	
	
	
	
	function mysqli_result($res, $row, $field=0) { 
    $res->data_seek($row); 
    $datarow = $res->fetch_array(); 
    return $datarow[$field]; 
	} 
	
	
	function escape($str)
	{
		return mysqli_real_escape_string($this->mysql, $str);
	}
	
	
			
	// užklausos siuntimas
	function query ($sql) 
	{
		//var_dump($sql);
		//echo $sql.' ';
		//$res = mysql_query("SELECT * FROM table") or die(mysql_error()); 
		$res = mysqli_query($this->mysql, $sql) or trigger_error("Mysql klaida # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql), E_USER_ERROR); 
		return $res;
		//
		//return mysql_query($sql, $this->mysql) or trigger_error("Mysql klaida", E_USER_ERROR);
	}
	
	
	function version()
	{
	return mysqli_get_server_info($this->mysql);
	}
		
	// atsijungimas nuo duomenų bazės
	function disconnect() 
	{
		if ($this->connected())
			mysqli_close($this->mysql);
	}
					
};
	
?>
