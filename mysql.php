<?php

namespace epgcore;


DEFINE('SQL_MASS','32xg5fddgsz');

/**
 * @name		Wraped MySQL
 * @version 	mysql.class.php, v 0.2 2006/12/01
 * @author 		CyberDyne (linuxxx@splius.lt)	
 */

 
 //updated to mysql due to php 5.5 compatibility
 
// protection from MySQL injection
function mysql_magic_quote ($value)
{
   	//if (get_magic_quotes_gpc())    		$value = stripslashes($value);
   
   	//if (!is_numeric($value))		$value = "'" . $this->escape($value) . "'";
   		
  	return $value;
}


// situ reiktu nebenaudoti, string provlaso buti always clean, o del mysql reikia naudoti prepared statements
function security () 
{		
   
   $_POST=security2($_POST);
    $_GET=security2($_GET);
    $_COOKIE=security2($_COOKIE);
}

function security2 ($mas) 
{
    if (is_array($mas)) 
    {
        foreach ($mas as $k=>$v) {
            $mas[$k]=security2($v);
        }
        return $mas;
    } else {
       // if (get_magic_quotes_gpc()) 	 $mas = stripslashes($mas);
   		 $mas = str_replace("'", '"', $mas); /* modas */
		$mas = addslashes($mas);
        //if (!is_numeric($mas))
 		// $mas = mysqli_real_escape_string($mas);
        return $mas;
    }
}


class mysql {
		
	var $mysql;

	
	// Constructor
	function __construct ($mysql = null)
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
		$this->lastQuery= $sql;
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


	// pirmas param uzklausa, o toliaiu neribotas param kiekis, kiekvienam placeholderiui po parametra
	//todo apsauga sutirkinanti ar tiek padavem kiek reikia paramsu kiek yra paceholderiu
	// placeholderiai %s  %i

	// TODO buildqueruy, buidwhere idejos - kaip nekartoti uzklausoje stulpeliu pavadinimu kai ju daug?
	//$d->q("Insert into aaaa SET a=?s, b=?s, c=?s")
	//$d->q("UPDATE aaaa SET ?x WHERE id=?i LIMIT 1", ['name'=>aa,'mail'=>ccc], $id);  
	// ten kur ?x ten pakeicia name=?s mail=?s  pagal tai, koks tipas yra to name ir to mail. nes ten glai buti integer ar tring, ta mes galim nustatyt

	//todo buildwhere kaip galetu veikti?
	//$d->q("select * from xxx where (name=?s OR surname=?s) ")
	//$d->q("select * from xxx where ?w ", [[name,surname],[111,111]])

	// i	corresponding variable has type integer
	// d	corresponding variable has type double (double yra kaip float, tik duoble dvigubai didesnio tikslumo)
	// s	corresponding variable has type string
	// b	corresponding variable is a blob and will be sent in packets

	// $data=[];
	// foreach ($acc as $v) $data[]=[$id,$v];
	// $db->q("INSERT IGNORE INTO `acc_lapeliai2puse_pin` SET `type`=1, parent=?i, typeID=?i", SQL_MASS, $data);


	// function q_old($sql)
	// {
	// 	//apdorojam uzklausos teksta ir bindinam
	// 	$params = func_get_args();
	// 	array_shift($params);
	// 	$params2=[];

	// 	$array = preg_split('~(\?[idsbxw])~u',$sql,-1,PREG_SPLIT_DELIM_CAPTURE);

	// 	//$anum  = count($params);
	// 	//$pnum  = floor(count($array) / 2);
	// 	//if ( $pnum != $anum ) trigger_error("Mysql klaida: neatitinka args kiekis"); 

	// 	//pasiziurim kelintas parametras yra array ir kuri kiek kartu papildomai reikes dubliuoti
	// 	$kurieKartosis=[];
		
	// 	foreach ($params as $nr=>$p)
	// 	{
	// 		if (is_array($p) && 1<$kiek=count($p)) $kurieKartosis[$nr]=$kiek;

	// 		if (is_array($p)) foreach($p as $v) $params2[]=$v;
	// 		else $params2[]=$p; 
	// 	}

	// 	//surenkam raideliu rinkini protectinimui
	// 	$newquery='';
	// 	$letterSet='';
	// 	$daliesNr=0;
	// 	//einam per sql dalis
	// 	foreach ($array as $nr => $dalis)
	// 	{
	// 		if (substr($dalis,0,1)=='?' && strlen($dalis)==2)
	// 		{
	// 			$newLetter=substr($dalis,1,1);

	// 			//spec atvejai
	// 			if ($newLetter=='x')
	// 			{
	// 				// is daliesNr suprantame kurio params oziureti ir kiek kartu atkartoti
	// 				// a=?s, b=?s, c=?s
	// 				$skirtukas=[];
	// 				foreach ($params[$daliesNr] as $colName=>$colVal)
	// 				{
	// 					//sudarom query dalis
						
	// 					$colType='s';
	// 					if (gettype($colVal)=='integer') $colType='i';
	// 					elseif (gettype($colVal)=='double') $colType='d';
	// 					$skirtukas[]="{$colName}=?";

	// 					//papildom letterSeta
	// 					$letterSet.=$colType;

	// 					//na o paramsai jau attiinka kaip kad yra surinkti masyve

	// 				}
	// 				$skirtukas=implode(', ',$skirtukas);

	// 			} 
	// 			elseif ($newLetter=='w')
	// 			{
	// 				// is daliesNr suprantame kurio params oziureti ir kiek kartu atkartoti
	// 				// a=?s, b=?s, c=?s
	// 				$skirtukas=[];
	// 				foreach ($params[$daliesNr] as $colName=>$colVal)
	// 				{
	// 					//sudarom query dalis
						
	// 					$colType='s';
	// 					if (gettype($colVal)=='integer') $colType='i';
	// 					elseif (gettype($colVal)=='double') $colType='d';
	// 					$skirtukas[]="{$colName} LIKE ?";

	// 					//papildom letterSeta
	// 					$letterSet.=$colType;

	// 					//na o paramsai jau attiinka kaip kad yra surinkti masyve

	// 				}
	// 				$skirtukas=  ($skirtukas) ?  ' ('.implode(' OR ',$skirtukas).') '     :    ' 1=1 ';

	// 			}
	// 			else {

	// 				//default letters

	// 				$skirtukas='?';

	// 				//jeigu reikia pakartojame paskutiniaja raide tiek kiek reikia kartu
	// 				if (isset($kurieKartosis[$daliesNr]))
	// 				{
	// 					$newLetter=str_repeat($newLetter,$kurieKartosis[$daliesNr]);
	// 					$skirtukas.=str_repeat(','.$skirtukas,$kurieKartosis[$daliesNr]-1);

	// 					//taipogi reikia atitinkamai padidinti klaustuku skaiciu, vietoj ?   turi gautis ?,?,?,?
	// 				}

	// 				$letterSet.=$newLetter;
					

	// 			}

	// 			$newquery.= $skirtukas;

	// 			++$daliesNr;
	// 		} else {

	// 			$newquery.= $dalis;
	// 		}

	// 	}

	// 	//toliau seka parametru bindinimas

	// 	// pre_dump($sql);
	// 	// pre_dump($params);
	// 	// pre_dump($array);
	// 	// pre_dump($kurieKartosis);
	// 	// pre_dump($letterSet);
	// 	// pre_dump($newquery);
	// 	// pre_dump($params2);

	// 	//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // ta ijugnus mestu eceptionus

	// 	$this->lastQuery= $newquery;

	// 	$this->stmt = false; // tas viska issprendzia. be jo neduopda klaidos pranesimo
	// 	$this->stmt = mysqli_prepare($this->mysql, $newquery);


		
	// 	if ($this->stmt===false) {
	// 		trigger_error("Mysql klaida prepare # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql), E_USER_ERROR); 
			
	// 	}	else {

	// 		//cia turim prikurt tiek kiek yra paduota params
	// 		if($letterSet && false===mysqli_stmt_bind_param($this->stmt, $letterSet, ...$params2))
	// 		{
	// 			// cia ir taip gaunam ziniu i err loga
	// 			//trigger_error("Mysql klaida bind # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql), E_USER_ERROR); 
	// 		} else {
	// 			if(false===mysqli_stmt_execute($this->stmt))
	// 			{
	// 				trigger_error("Mysql klaida exec # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql)." ".mysqli_stmt_error($this->stmt), E_USER_ERROR); 
	// 				//if (devpc) exit(mysqli_error($this->mysql));
	// 			}

	// 		}

			
	// 	}

	// 	//pre_dump('stmt-err',  mysqli_stmt_error($this->stmt) );

		

		

	// 	return $this;
	// }



	function q($sql)
	{
		//apdorojam uzklausos teksta ir bindinam
		$params = func_get_args();
		array_shift($params);

		//kai neperduotas masiskumo parametras, virtualiai pakuriame masiskaji masyva
		$masinis=[];
		$masinis2=[];
		//pre_dump('tikrinam',  $params[0],SQL_MASS, $params[0]===SQL_MASS);
        //isset cia naudoti negalim, nes jeigu pirmas params parametras bus null tai isset grazins false kas nera gerai siuo atveju
        if ( is_array($params) && array_key_exists(0, $params) && $params[0]!==SQL_MASS) $masinis[]=$params;
        else {
            array_shift($params);
            if ( is_array($params) && array_key_exists(0, $params)) $masinis=$params[0];
        }

		if (isset($masinis[0])) $params=$masinis[0];

		//pre_dump($masinis,$params);
		

        //$array = preg_split('~(\?[idsbxw])~u',$sql,-1,PREG_SPLIT_DELIM_CAPTURE);
        // po ?i skaitomas kad porivalo buti tarpas, kablelis ar skliaustas todel gale privalomai pridedam tarpa tam ka veiktu nurodu formavimai pacioje sql pvz url/?id=  o cia jau ?i simbolis kaip tik aptinkamas
        $array = preg_split('~(\?[idsbxw])([\s,)]{1})~u', $sql." ",-1,PREG_SPLIT_DELIM_CAPTURE);


		//$anum  = count($params);
		//$pnum  = floor(count($array) / 2);
		//if ( $pnum != $anum ) trigger_error("Mysql klaida: neatitinka args kiekis"); 

		//pasiziurim kelintas parametras yra array ir kuri kiek kartu papildomai reikes dubliuoti
		$kurieKartosis=[];
		
		foreach ($params as $nr=>$p)
		{
			if (is_array($p) && 1<$kiek=count($p)) $kurieKartosis[$nr]=$kiek;
		}

		foreach ($masinis as $mkey=>$params)
		{
			foreach ($params as $nr=>$p)
			{
				if (is_array($p)) foreach($p as $v) $masinis2[$mkey][]=$v;
				else $masinis2[$mkey][]=$p; 
			}
		}


		//surenkam raideliu rinkini protectinimui
		$newquery='';
		$letterSet='';
		$daliesNr=0;
		//einam per sql dalis
		foreach ($array as $nr => $dalis)
		{
			if (substr($dalis,0,1)=='?' && strlen($dalis)==2)
			{
				$newLetter=substr($dalis,1,1);

				//spec atvejai
				if ($newLetter=='x')
				{
					// is daliesNr suprantame kurio params oziureti ir kiek kartu atkartoti
					// a=?s, b=?s, c=?s
					$skirtukas=[];
					foreach ($params[$daliesNr] as $colName=>$colVal)
					{
						//sudarom query dalis
						
						$colType='s';
						if (gettype($colVal)=='integer') $colType='i';
						elseif (gettype($colVal)=='double') $colType='d';
						$skirtukas[]="{$colName}=? ";

						//papildom letterSeta
						$letterSet.=$colType;

						//na o paramsai jau attiinka kaip kad yra surinkti masyve

					}
					$skirtukas=implode(', ',$skirtukas);

				} 
				elseif ($newLetter=='w')
				{
					// is daliesNr suprantame kurio params oziureti ir kiek kartu atkartoti
					// a=?s, b=?s, c=?s
					$skirtukas=[];
					foreach ($params[$daliesNr] as $colName=>$colVal)
					{
						//sudarom query dalis
						
						$colType='s';
						if (gettype($colVal)=='integer') $colType='i';
						elseif (gettype($colVal)=='double') $colType='d';
						$skirtukas[]="{$colName} LIKE ? ";

						//papildom letterSeta
						$letterSet.=$colType;

						//na o paramsai jau attiinka kaip kad yra surinkti masyve

					}
					$skirtukas=  ($skirtukas) ?  ' ('.implode(' OR ',$skirtukas).') '     :    ' 1=1 ';

				}
				else {

					//default letters

					$skirtukas='? ';

					//jeigu reikia pakartojame paskutiniaja raide tiek kiek reikia kartu
					if (isset($kurieKartosis[$daliesNr]))
					{
						$newLetter=str_repeat($newLetter,$kurieKartosis[$daliesNr]);
						$skirtukas.=str_repeat(','.$skirtukas,$kurieKartosis[$daliesNr]-1);

						//taipogi reikia atitinkamai padidinti klaustuku skaiciu, vietoj ?   turi gautis ?,?,?,?
					}

					$letterSet.=$newLetter;
					

				}

				$newquery.= $skirtukas;

				++$daliesNr;
			} else {

				$newquery.= $dalis;
			}

		}

		//toliau seka parametru bindinimas

		// pre_dump($sql);
		// pre_dump($params);
		// pre_dump($array);
		// pre_dump($kurieKartosis);
		// pre_dump($letterSet);
		// pre_dump($newquery);
		// pre_dump($params2);

		//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // ta ijugnus mestu eceptionus

		$this->lastQuery= $newquery;

		$this->stmt = false; // tas viska issprendzia. be jo neduopda klaidos pranesimo
		$this->stmt = mysqli_prepare($this->mysql, $newquery);


		
		if ($this->stmt===false) {
			trigger_error("Mysql klaida prepare # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql), E_USER_ERROR); 
			
		}	else {

			//if (devpc) pre_dump($masinis2);
			//info  jeigu nera paduota paramsu mes vistiek privalom paleisti bent karta execution
			if (!$masinis2) {
				if(false===mysqli_stmt_execute($this->stmt))
				{
					trigger_error("Mysql klaida exec # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql)." ".mysqli_stmt_error($this->stmt), E_USER_ERROR); 
					//if (devpc) exit(mysqli_error($this->mysql));
				}
			} else {
				foreach ($masinis2 as $params2)
				{
					//pre_dump('---',$letterSet,$params2);
					//cia turim prikurt tiek kiek yra paduota params
					if($letterSet && false===mysqli_stmt_bind_param($this->stmt, $letterSet, ...$params2))
					{
						// cia ir taip gaunam ziniu i err loga
						//trigger_error("Mysql klaida bind # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql), E_USER_ERROR); 
					} else {
						
						if(false===mysqli_stmt_execute($this->stmt))
						{
							trigger_error("Mysql klaida exec # ".mysqli_errno($this->mysql)." ".mysqli_error($this->mysql)." ".mysqli_stmt_error($this->stmt), E_USER_ERROR); 
							//if (devpc) exit(mysqli_error($this->mysql));
						}
					}
					
				}
			}
			
		}

		//if (devpc) pre_dump($this->lastQuery);

		//pre_dump('stmt-err',  mysqli_stmt_error($this->stmt) );

		

		

		return $this;
	}



	// vienos eilute paemimas arba netgi vieno col
	function rw ()
	{		
		// jeigu nera eiluciu turi grazinti null, jeigu klaida false
		if($this->stmt===false) return false;

		$result = mysqli_stmt_get_result($this->stmt);
		if ($result===false) return false;

		if ($result->num_rows==0) return null;

		$rez = mysqli_fetch_array($result, MYSQLI_ASSOC);

		if (count($rez)==1) return current($rez);

		return $rez;
	}

	// keliu eiluciu paemimas su galimybe grupuoti
	function rws ($gruopByField=false,$is_array=false)
	{
		// jeigu nera eiluciu turi grazinti null, jeigu klaida false
		if($this->stmt===false) return false;

		$result = mysqli_stmt_get_result($this->stmt);
		if ($result===false) return false;

		if ($result->num_rows==0) return null;

		//pre_dump($result);

		$rows = array();

		if (is_callable($gruopByField)) 
		{

			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$gruopByField($rows,$row);
			}	

		} else {

			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				
				//jeigu uzklausta tik vieno stulpelio, tai ta reikmse ir grazinam o ne masyva
				if (!$gruopByField && count($row)==1) $row = current($row);

				if ($gruopByField===false) $rows[]=$row;
				else {
					if ($is_array) $rows[$row[$gruopByField]][] = $row;
					else $rows[$row[$gruopByField]] = $row;
				}
				
			}

		}

		return $rows;
		
		
		//pre_dump( $this );
		// $result = $this->query($sql);
		
		// if ((is_bool($result)) && ($result == false)) return false;
		// $count = mysqli_num_rows($result);
		// if ($count == 0) return null;
		
	
	}

	function num()
	{
		// jeigu klaida false
		if($this->stmt===false) return false;

		$result = mysqli_stmt_get_result($this->stmt);
		if ($result===false) return false;

		return $result->num_rows;
	}

	function debug()
	{
		// nustatom kad reikia debugint i kazkur, ir tada fiksuosis kazkur pats statementas bei paramsai kokie paduodami
		return $this;
	}

	function begin()
	{
		return mysqli_begin_transaction($this->mysql);
	}

	function commit()
	{
		return mysqli_commit($this->mysql);
	}

					
};
	
?>
