<?php
/* 
   FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
   More info at http://dev.cmsmadesimple.org/projects/formbuilder
   
   A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
  This project's homepage is: http://www.cmsmadesimple.org
*/

class fbCsrfField extends fbFieldBase
{

  function __construct($form_ptr, &$params)
  {
    parent::__construct($form_ptr, $params);
    $mod = formbuilder_utils::GetFB();
    $this->Type = 'CsrfField';
    $this->DisplayInForm = true;
    $this->NonRequirableField = true;
    $this->ValidationTypes = array();
    $this->HasLabel = 0;
    $this->NeedsDiv = 0;
    $this->sortable = false;
  }
  
  function GetFieldInput($id, &$params, $returnid)
  {
    
		$token_name = '__FB__CSRF__' . $this->form_ptr->GetId();
		
		if(empty($_SESSION[$token_name]))
		{
			if(version_compare(PHP_VERSION, '7.0.0') >= 0)
			{
				$_SESSION[$token_name] = bin2hex(random_bytes(32));
			}
			else
			{
				if (function_exists('mcrypt_create_iv'))
				{
					$_SESSION[$token_name] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
				}
				else
				{
					$_SESSION[$token_name] = bin2hex(openssl_random_pseudo_bytes(32));
				}
			}
			
		}
		
		$token = $_SESSION[$token_name];
		
    return '<input type="hidden" name="'.$id.'fbrp__'.$this->Id.'" value="'. $token .'"'.$this->GetCSSIdTag().' />';
  }
	
	/**
	 * Remove unneeded options
	 * @param $mainArray
	 * @param $advArray
	 */
	function PostPopulateAdminForm(&$mainArray, &$advArray)
	{
		$message = formbuilder_utils::GetFB()->Lang('empty_settings_tab_notice');
		$title = formbuilder_utils::GetFB()->Lang('title_note');
		$entry = new \stdClass();
		$entry->title = $title;
		$entry->input = '';
		$entry->help = $message;
		$advArray = [$entry];
	}
	
	// override me. Returns an array: first value is a true or false (whether or not
	// the value is valid), the second is a message
	function Validate()
	{
		$this->validatedErrorText = '';
		$token_name = '__FB__CSRF__' . $this->form_ptr->GetId();
		
		if(!empty($token_name))
		{
			if (hash_equals($_SESSION[$token_name], $this->Value))
			{
				$this->validated = true;
			}
			else
			{
				$this->validatedErrorText = 'error CSRF!';
				$this->validated = false;
			}
		}
		
		unset($_SESSION[$token_name]);
		return array($this->validated, $this->validatedErrorText);
	}


}

?>
