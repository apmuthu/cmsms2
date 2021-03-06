<?php
/* 
   FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
   More info at http://dev.cmsmadesimple.org/projects/formbuilder
   
   A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
  This project's homepage is: http://www.cmsmadesimple.org
*/

class fbPulldownField extends fbFieldBase {

	var $optionCount;
	var $optionAdd;

    function array_sort_by_key($input)
	{
		if( !is_array($input) ) return;
		$a1 = array();
		foreach( $input as $k => $v ) {
			$a1[$v] = $k;
		}
		asort($a1);
		$a2 = array();
		foreach( $a1 as $k => $v ) {
			$a2[$v] = $k;
		}
		return $a2;
	}

	function __construct($form_ptr, &$params)
	{
		parent::__construct($form_ptr, $params);
        $mod = formbuilder_utils::GetFB();
		$this->Type = 'PulldownField';
		$this->DisplayInForm = true;
		$this->HasAddOp = true;
		$this->HasDeleteOp = true;
		$this->ValidationTypes = array(
            );
        $this->optionAdd = 0;
	}

	function GetOptionAddButton()
	{
		$mod = formbuilder_utils::GetFB();
		return $mod->Lang('add_options');
	}

	function GetOptionDeleteButton()
	{
		$mod = formbuilder_utils::GetFB();
		return $mod->Lang('delete_options');
	}

	function DoOptionAdd(&$params)
	{
		$this->optionAdd = 1;
	}

	function DoOptionDelete(&$params)
	{
		$delcount = 0;
		foreach ($params as $thisKey=>$thisVal)
			{
			if (substr($thisKey,0,9) == 'fbrp_del_')
				{
				$this->RemoveOptionElement('option_name', $thisVal - $delcount);
				$this->RemoveOptionElement('option_value', $thisVal - $delcount);
				$delcount++;
				}
			}
	}

	function countItems()
	{
			$tmp = $this->GetOptionRef('option_name');
			if (is_array($tmp))
				{
	        	$this->optionCount = count($tmp);
	        	}
	        elseif ($tmp !== false)
	        	{
	        	$this->optionCount = 1;
	        	}
	        else
	        	{
	        	$this->optionCount = 0;
	        	}
	}

	function GetFieldInput($id, &$params, $returnid)
	{
		$mod = formbuilder_utils::GetFB();
		$js = $this->GetOption('javascript','');
		$html5 = '';

		if ($this->GetOption('html5','0') == '1'&& $this->IsRequired()) 
		{
			$html5 = ' required';
		}
		// why all this? Associative arrays are not guaranteed to preserve
		// order, except in "chronological" creation order.
		$sorted =array();
		$subjects = $this->GetOptionRef('option_name');
		if (count($subjects) > 1) {
			for($i=0;$i<count($subjects);$i++) {
				$sorted[$subjects[$i]]=($i+1);
			}
			if( $this->GetOption('sort') == '1' ) {
				ksort($sorted);
			}
		}
		else {
			$sorted[$subjects] = '1';
		}

		if ($this->GetOption('select_one','') != '') {
			$sorted = array(' '.$this->GetOption('select_one','')=>'') + $sorted;
		}
		else {
			$sorted = array(' '.$mod->Lang('select_one')=>'') + $sorted;
		}
		return $mod->CreateInputDropdown($id, 'fbrp__'.$this->Id, $sorted, -1, $this->Value,$html5.$js.$this->GetCSSIdTag());
	}



    function StatusInfo()
	{
		$mod = formbuilder_utils::GetFB();
		$opt = $this->GetOption('option_name','');
		
		if (is_array($opt))
		  {
		      $num = count($opt);
		  }
		elseif ($opt != '')
			{
			$num = 1;
			}
		else
		  {
          $num = 0;
          } 
         $ret= $mod->Lang('options',$num);
        return $ret;
	}
	
	function PrePopulateAdminForm($formDescriptor)
	{
		$mod = formbuilder_utils::GetFB();

		$this->countItems();
		if ($this->optionAdd > 0)
			{
			$this->optionCount += $this->optionAdd;
			$this->optionAdd = 0;
			}
		$dests = '<table class="pagetable module_fb_table"><tr><th>'.$mod->Lang('title_option_name').'</th><th>'.
			$mod->Lang('title_option_value').'</th><th>'.
			$mod->Lang('title_delete').'</th></tr>';

		$odd = false;
		for ($i=0;$i<($this->optionCount>1?$this->optionCount:1);$i++)
			{
			$dests .=  '<tr class="'.($odd?'row1':'row2').'"><td>'.
            		$mod->CreateInputText($formDescriptor, 'fbrp_opt_option_name[]',$this->GetOptionElement('option_name',$i),25,128).
            		'</td><td>'.
            		$mod->CreateInputText($formDescriptor, 'fbrp_opt_option_value[]',$this->GetOptionElement('option_value',$i),25,128).
            		'</td><td>'.
            		$mod->CreateInputCheckbox($formDescriptor, 'fbrp_del_'.$i, $i,-1).
             		'</td></tr>';
					$odd = !$odd;
			}
		$dests .= '</table>';
		$main = array();
		$adv = array();
		$main[] = array($mod->Lang('title_select_one_message'),
						$mod->CreateInputText($formDescriptor, 'fbrp_opt_select_one',
											  $this->GetOption('select_one',$mod->Lang('select_one')),25,128));
		$main[] = array($mod->Lang('sort_options'),
						$mod->CreateInputDropdown($formDescriptor,'fbrp_opt_sort',
												  array('Yes'=>1,'No'=>0),-1,
												  $this->GetOption('sort',0)));
		array_push($main,array($mod->Lang('title_pulldown_details'),$dests));
		return array('main'=>$main,'adv'=>$adv);
	}


	function GetHumanReadableValue($as_string=true)
	{
		$mod = formbuilder_utils::GetFB();
		if ($this->HasValue())
			{
			$ret = $this->GetOptionElement('option_value',($this->Value-1));
			}
		else
			{
			//$ret = $mod->Lang('unspecified');
			$ret = $this->form_ptr->GetAttr('unspecified',$mod->Lang('unspecified'));
			}
		if ($as_string)
			{
			return $ret;
			}
		else
			{
			return array($ret);
			}
	}

}
?>
