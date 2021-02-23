<?php
/*
   FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
   More info at http://dev.cmsmadesimple.org/projects/formbuilder

   A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
  This project's homepage is: http://www.cmsmadesimple.org
*/

class fbPasswordField extends fbFieldBase {

	function __construct($form_ptr, &$params)
	{
      parent::__construct($form_ptr, $params);
      $mod = formbuilder_utils::GetFB();
		$this->Type = 'PasswordField';
		$this->DisplayInForm = true;
		$this->ValidationTypes = array(
            $mod->Lang('validation_none')=>'none',
            $mod->Lang('validation_regex_match')=>'regex_match',
            $mod->Lang('validation_regex_nomatch')=>'regex_nomatch'
            );

	}


	function GetFieldInput($id, &$params, $returnid)
	{
		$mod = formbuilder_utils::GetFB();
		$js = $this->GetOption('javascript','');
		$ro = '';
		$val = '';
		$html5 = '';

		if ($this->GetOption('readonly','0') == '1')
		{
			$ro = ' readonly';
		}

		if ($this->GetOption('html5','0') == '1')
		{
                        $val = $this->Value;
                        $html5 = ' placeholder="'.$this->GetOption('default').'"';
		        if ($this->IsRequired()) {
		                $html5 .= ' required';
		        }
                }
                else
                {
                        $val = $this->Value ? $this->Value : $this->GetOption('default');
                        if($this->GetOption('clear_default','0') == 1)
                        {
                                $js .= ' onfocus="if(this.value==this.defaultValue) this.value=\'\';" onblur="if(this.value==\'\') this.value=this.defaultValue;"';
                        }
                }
		if ($this->GetOption('hide','1') == '0')
		{
			return $mod->fbCreateInputText($id, 'fbrp__'.$this->Id,
				$val,$this->GetOption('length'), 255,
				$html5.$js.$ro.$this->GetCSSIdTag());
		}
		else
		{
			return $mod->CreateInputPassword($id, 'fbrp__'.$this->Id,
				$val, $this->GetOption('length'),255,
				$html5.$js.$ro.$this->GetCSSIdTag());
		}
	}

	function StatusInfo()
	{
	  $mod = formbuilder_utils::GetFB();
	  $ret = $mod->Lang('abbreviation_length',$this->GetOption('length','80'));
		if (strlen($this->ValidationType)>0)
		  {
		  	$ret .= ", ".array_search($this->ValidationType,$this->ValidationTypes);
		  }
      if ($this->GetOption('readonly','0') == '1')
         {
         $ret .= ", ".$mod->Lang('title_read_only');
         }
		 return $ret;
	}


	function PrePopulateAdminForm($formDescriptor)
	{
		$mod = formbuilder_utils::GetFB();
		$main = array(
			array($mod->Lang('title_display_length'),
			      $mod->CreateInputText($formDescriptor,
						    'fbrp_opt_length',
			         $this->GetOption('length','12'),25,25)),
			array($mod->Lang('title_minimum_length'),
			      $mod->CreateInputText($formDescriptor,
						    'fbrp_opt_min_length',
			         $this->GetOption('min_length','8'),25,25)),
			array($mod->Lang('title_hide'),
			      $mod->CreateInputHidden($formDescriptor, 'fbrp_opt_hide','0').
            		$mod->CreateInputCheckbox($formDescriptor, 'fbrp_opt_hide',
            		'1',$this->GetOption('hide','1')).$mod->Lang('title_hide_help')),
			array($mod->Lang('title_read_only'),
			      $mod->CreateInputHidden($formDescriptor, 'fbrp_opt_readonly','0').
            		$mod->CreateInputCheckbox($formDescriptor, 'fbrp_opt_readonly',
            		'1',$this->GetOption('readonly','0')))
		);
		$adv = array(
			array($mod->Lang('title_field_regex'),
			      array($mod->CreateInputText($formDescriptor,
							  'fbrp_opt_regex',
							  $this->GetOption('regex'),25,1024),$mod->Lang('title_regex_help'))),
		);
		array_push($adv, array($mod->Lang('title_field_default_value'),$mod->CreateInputText($formDescriptor, 'fbrp_opt_default',$this->GetOption('default'),25,1024)));
		array_push($adv, array($mod->Lang('title_clear_default'),$mod->CreateInputHidden($formDescriptor,'fbrp_opt_clear_default','0').$mod->CreateInputCheckbox($formDescriptor, 'fbrp_opt_clear_default','1',$this->GetOption('clear_default','0')).'<br />'.$mod->Lang('title_clear_default_help')));
		return array('main'=>$main,'adv'=>$adv);
	}


	function Validate()
	{
		$this->validated = true;
		$this->validationErrorText = '';
		$mod = formbuilder_utils::GetFB();
		switch ($this->ValidationType)
		  {
		  	   case 'none':
		  	       break;
		  	   case 'regex_match':
                  if ($this->Value !== false &&
                      ! preg_match($this->GetOption('regex','/.*/'), $this->Value))
                    {
                    $this->validated = false;
                    $this->validationErrorText = $mod->Lang('please_enter_valid',$this->Name);
                    }
		  	   	   break;
		  	   case 'regex_nomatch':
                  if ($this->Value !== false &&
                       preg_match($this->GetOption('regex','/.*/'), $this->Value))
                    {
                    $this->validated = false;
                    $this->validationErrorText = $mod->Lang('please_enter_valid',$this->Name);
                    }
		  	   	   break;
		  }
		if ($this->GetOption('min_length',0) > 0 && strlen($this->Value) < $this->GetOption('min_length',0))
			{
			$this->validated = false;
			$this->validationErrorText = $mod->Lang('please_enter_at_least',$this->GetOption('min_length',0));
			}
		return array($this->validated, $this->validationErrorText);
	}
}

?>
