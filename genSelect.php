<?php

namespace epgcore;

class genSelect
{

	// The Select class renders a drop down select
	// filled with elements in an array ListItem[]
	// and associates each element with values from
	// an array ListData[], and optionally selects 
	// one of the items.
	
	/*
	
	$myselect = new genSelect('pavad');
	$myselect->ListItem = array("First Item", "Second Item", "Third Item", "Fourth Item", "Fifth Item");
	$myselect->ListData = array("1", "2", "3", "4", "5");
	$myselect->SelectedData = 3;
	echo $myselect->Render();

	*/
	
	
	
	
	var 
    $ListItem=array(),			// array of items displayed in the Select
	$ListData=array(),
	$strict=false,
	$strict2=false,
    
        $ListOptions,			// array of items values
		$Classe,				// optional CSS class name to apply to the menu
		$Style,				// optional CSS style to apply to the menu
		$SelectName,		// name of the generated menu
		$SelectedData,  // optional value of item to choose
		$data,
		$kita,		    //kita pasirinkta iterpiama info, pvz javaskriptas
		$HTML;				// used to output the select code
		
	function __construct($name='', $class='', $style='') // Constructor function
	{
		$this->Classe = $class;
		$this->Style = $style;
		$this->HTML = '';
		$this->kita='';
		$this->SelectName = $name;
		
	}
	
	function Render()
	{
		//pre_dump($this->data); 
		
		$ArrayCount = count($this->ListItem);
		
		if($ArrayCount > count($this->ListData))
		{
			$this->HTML .= 'Invalid Class Definition';
			return 0;
		}
		
		$this->HTML = '<select name="'.$this->SelectName.'" class="'.$this->Classe.'" style="'.$this->Style.'" '.$this->kita.'>';
		for($i = 0; $i < $ArrayCount; $i++)
		{
			$sel = '';
			if(is_array($this->SelectedData) && array_search($this->ListData[$i], $this->SelectedData)!==FALSE )
			        $sel = 'selected class="SelectedOption"';
            else {
				//if (devpc) pre_dump($this->SelectedData);
				if ( $this->strict )
				{
					if($this->SelectedData === $this->ListData[$i])  {  $sel = 'selected class="SelectedOption"'; }
					
				} elseif ( $this->strict2 )
				{
					
					if($this->SelectedData == $this->ListData[$i] && strlen($this->SelectedData)==strlen($this->ListData[$i]))  {  $sel = 'selected class="SelectedOption"'; }
				}else {
					if($this->SelectedData == $this->ListData[$i])  {  $sel = 'selected class="SelectedOption"'; }
				}
				
			}			
			
      $opcija = (isset($this->ListOptions[$i])) ? ' '.$this->ListOptions[$i] : '';
      
      $data = '';
      if (isset($this->data[$i])) foreach ($this->data[$i] as $dname=>$dval) {
        $data .= ' '.$dname.'="'.$dval.'" ';
      }
            
			$this->HTML .= "\n\t".'<option value="'. $this->ListData[$i] . '" '.$sel.$opcija.$data.'> '. $this->ListItem[$i] . '</option>';
		}
		$this->HTML .= "</select>";
		
		return $this->HTML;
	}    
    
    function RenderRadio()
	{
		$ArrayCount = count($this->ListItem);
		
		if($ArrayCount > count($this->ListData))
		{
			$this->HTML .= 'Invalid Class Definition';
			return 0;
		}
		//<input type="radio" name="group2" value="Wine" checked> 
		$this->HTML = '<div class="'.$this->Classe.'" style="'.$this->Style.'" '.$this->kita.'>';
		for($i = 0; $i < $ArrayCount; $i++)
		{
			if(is_array($this->SelectedData) && array_search($this->ListData[$i], $this->SelectedData)!==FALSE )
			        $sel = 'checked class="SelectedOption"';
            else if($this->SelectedData == $this->ListData[$i])
                    $sel = 'checked class="SelectedOption"';
			else
			        $sel = '';
			
            $opcija = (isset($this->ListOptions[$i])) ? ' '.$this->ListOptions[$i] : '';
            
			$this->HTML .= "\n\t".'<input type="radio" name="'.$this->SelectName.'" id="rb_'. $this->ListData[$i] . '" value="'. $this->ListData[$i] . '" '.$sel.$opcija.' /><label for="rb_'. $this->ListData[$i] . '">'. $this->ListItem[$i] . '</label>';
		}
		$this->HTML .= "</div>"; 
		
		return $this->HTML;
	}  
	
	
	
	function detData($r)
	{
    if (is_array($r)) foreach ($r as $k=>$v) {
      $xd = array();
      if (is_array($v)) foreach ($v as $name=>$val) {
        if (substr($name,0,5)=='data-') $xd[$name]=$val;
      }
      $this->data[]=$xd;
    }
	}
	
	         
}


//mano isplesta klase, generuojanti konkrecius select laukus
class genSelect2 extends genSelect
{

  function menesiai($name, $selected='', $class='', $style='')
  {
    $this->Classe = $class;
		$this->Style = $style;
		$this->HTML = '';
		$this->SelectName = $name;
		$this->SelectedData = $selected;
    $this->ListItem = array('Sausis', 'Vasaris', 'Kovas', 'Balandis', 'Gegužė', 'Birželis', 'Liepa', 'Rugpjūtis', 'Rugsėjis', 'Spalis', 'Lapkritis', 'Gruodis');
    $this->ListData = array('01','02','03','04','05','06','07','08','09','10','11','12');
    return $this->Render();
  }
  
  function metai($name, $selected='', $class='', $style='')
  {
    $this->Classe = $class;
		$this->Style = $style;
		$this->HTML = '';
		$this->SelectName = $name;
		$this->SelectedData = $selected;
    $this->ListItem=array();
    $this->ListData=array();
    $dabar=date("Y");
    for($i=0;$i<21;++$i)
    {
      $this->ListItem[] = $dabar-$i;
      $this->ListData[] = $dabar-$i;  
    }
    return $this->Render();
  }
  
  
  function dienos($name, $selected='', $class='', $style='')
  {
    $this->Classe = $class;
		$this->Style = $style;
		$this->HTML = '';
		$this->SelectName = $name;
		$this->SelectedData = $selected;
    $this->ListItem=array();
    $this->ListData=array();
    for($i=1;$i<32;++$i) 
    {
      $this->ListItem[] = $i;
      $this->ListData[] = ($i>9) ? $i : '0'.$i;      
    }
    return $this->Render();
  }

}

class genSelect3 extends genSelect
{
    var $noselect = true;
    var $itraukti = true;
    
    function __construct($coa, $noselect=true, $itraukti=true) {
        $this->noselect = $noselect;
        $this->itraukti = $itraukti;
        if (isset($coa[0]) && is_array($coa[0]) && array_key_exists('code',$coa[0])) $this->arrParse($coa); else $this->arrParse2($coa);
        $this->detData($coa);
    }
    
    function arrParse($coa) {
        if ($this->noselect) { $this->ListData[]=''; $this->ListItem[]='-nepasirinktas-'; $this->data[]=array(); }
        if (is_array($coa)) foreach ($coa as $v) {
            $this->ListData[]=$v['code'];
            $this->ListItem[]= ($this->itraukti) ? $v['code'].' '.$v['name'] : $v['name'];
        }
    }
    
    function arrParse2($coa) {
        if ($this->noselect) { $this->ListData[]=''; $this->ListItem[]='-nepasirinktas-';  $this->data[]=array(); }
        if (is_array($coa)) foreach ($coa as $k=>$v) {
            $this->ListData[]=$k;
            $this->ListItem[]= ($this->itraukti) ? $k.' '.$v : $v;
        }
    }
    
    function Render3 ($name, $selected='') {
        $this->SelectName = $name;
        $this->SelectedData = $selected;
        return $this->Render();
    }
    
    function shift($mas) {
        foreach ($mas as $k=>$v) {
            array_unshift($this->ListData, $k);
            array_unshift($this->ListItem, $v);
            if (isset($this->data)) array_unshift($this->data, array());
        }
    } 
    
    function multi($k) {
        $this->kita.=' multiple="true" size='.$k.' ';
    }
}

class genSelect4 extends genSelect3
{
    function __construct($arr, $key, $val, $noselect=true, $itraukti=false) {
        $this->noselect = $noselect;
        $this->itraukti = $itraukti;
        if ($this->noselect) { $this->ListData[]=''; $this->ListItem[]='-nepasirinktas-';  $this->data[]=array(); }
        if (is_array($arr)) foreach ($arr as $v) {
            if (isset($v[$key]) && isset($v[$val])) {
                $this->ListData[]=$v[$key];
                $this->ListItem[]= ($this->itraukti) ? $v[$key].' '.$v[$val] : $v[$val];
            }
        }
        $this->detData($arr);
    }   
}


class genHTML
{

  
  public static function groupSel($arr,$name,$grp,$optval,$optname,$selected,$size=5,$id='')
  {
    $html='<select id="'.$id.'" name="'.$name.'" multiple size="'.$size.'" >'.PHP_EOL;
    foreach ($arr as $k=>$v)
    {
      $pirmas = current($v);
      $html.='  <optgroup label="'.htmlspecialchars($pirmas[$grp]).'">'.PHP_EOL;
      foreach ($v as $vv)
      {
        $sl= ($selected && array_search($vv[$optval],$selected)!==false) ? 'selected' : '';
		
		//tikrina ar yra data parametru
		$datas=array();
		foreach ($vv as $xx=>$yy) {
			if (substr($xx,0,5)=='data-') $datas[]=  $xx.'="'.$yy.'"';
		}
		
        $html.='    <option value="'.$vv[$optval].'" '.$sl.' '.implode(' ',$datas).'>'.$vv[$optname].'</option>'.PHP_EOL;
      }
      $html.='  </optgroup>'.PHP_EOL;
    }
    $html.='</select>';
    return $html;
  }
  
}

?>