<?php
/* 
   FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
   More info at http://dev.cmsmadesimple.org/projects/formbuilder
   
   A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
  This project's homepage is: http://www.cmsmadesimple.org
*/

class fbPageBreakField extends fbFieldBase {

	function __construct($form_ptr, &$params)
	{
        parent::__construct($form_ptr, $params);
        $mod = formbuilder_utils::GetFB();
		$this->Type = 'PageBreakField';
		$this->DisplayInForm = false;
		$this->Required = false;
		//$this->ValidationTypes = array($mod->Lang('validation_none')=>'none');
		$this->ValidationTypes = array();
		$this->NonRequirableField = true;
		$this->sortable = false;
    
    # if we want to handle multi-page upload submissions 
    # this is the place to handle the uploads which are not on the last page of the form
    # so here goes (Jo Morg)
    $this->form_ptr->manageFileUploads();
	}

	function GetFieldInput($id, &$params, $return_id)
	{
	}


	function StatusInfo()
	{
		return '';
	}


	function PrePopulateAdminForm($formDescriptor)
	{
		return array();
	}

	function PostPopulateAdminForm(&$mainArray, &$advArray)
	{
		$mod = formbuilder_utils::GetFB();
		// remove the "required" field
      $this->RemoveAdminField($mainArray, $mod->Lang('title_field_required'));
      $this->HiddenDispositionFields($mainArray, $advArray);
	}


	function Validate()
	{
		return array(true,'');
	}
	
}

?>
