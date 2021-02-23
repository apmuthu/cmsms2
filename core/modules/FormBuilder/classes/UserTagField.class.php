<?php
/* 
   FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
   More info at http://dev.cmsmadesimple.org/projects/formbuilder
   
   A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
  This project's homepage is: http://www.cmsmadesimple.org
*/

class fbUserTagField extends fbFieldBase
{
  
  function __construct($form_ptr, &$params)
  {
    parent::__construct($form_ptr, $params);
    $mod                       = formbuilder_utils::GetFB();
    $this->Type                = 'UserTagField';
    $this->IsDisposition       = FALSE;
    $this->NonRequirableField  = TRUE;
    $this->DisplayInForm       = TRUE;
    $this->DisplayInSubmission = FALSE;
    $this->sortable            = FALSE;
  }
  
  function StatusInfo()
  {
    $mod = formbuilder_utils::GetFB();
    
    return $this->GetOption('udtname', $mod->Lang('unspecified'));
  }
  
  function GetFieldInput($id, &$params, $returnid)
  {
    $mod    = formbuilder_utils::GetFB();
    $others = $this->form_ptr->GetFields();
    $unspec = $this->form_ptr->GetAttr('unspecified', $mod->Lang('unspecified'));
    $params = array();
    
    if($this->GetOption('export_form', '0') == '1')
    {
      $params['FORM'] = $this->form_ptr;
    }
    
    for($i = 0; $i < count($others); $i++)
    {
      $replVal = '';
      
      if($others[$i]->DisplayInSubmission())
      {
        $replVal = $others[$i]->GetHumanReadableValue();
        if($replVal == '')
        {
          $replVal = $unspec;
        }
      }
      
      $name                 = $others[$i]->GetVariableName();
      $params[$name]        = $replVal;
      $id                   = $others[$i]->GetId();
      $params['fld_' . $id] = $replVal;
      $alias                = $others[$i]->GetAlias();
      
      if(!empty($alias))
      {
        $params[$alias] = $replVal;
      }
    }
    
    $udt_handler_pref = $mod->GetPreference('udt_handler_pref', 'core_udts');
    $usertagops       = NULL;
    $gCms             = cmsms();
    
    if(!formbuilder_utils::is_CMS2_3())
    {
      if($udt_handler_pref == 'core_udts')
      {
        $method_name = 'CallUserTag';
        $usertagops  = $gCms->GetUserTagOperations();
      }
    }
    else
    {
      if($udt_handler_pref == 'core_sps')
      {
        $method_name = 'call_plugin';
        $usertagops  = $gCms->GetSimplePluginOperations();
      }
    }
    
    if(empty($usertagops))
    {
      $UDTsHandler = \cms_utils::get_module($udt_handler_pref);
      
      if(is_object($UDTsHandler))
      {
        $usertagops = $UDTsHandler->GetUserTagOperations();
      }
      else
      {
        # throw an error here? (JM)
      }
    }
    
    $udt = $this->GetOption('udtname');
    $res = $usertagops->$method_name($udt, $parms);
    
    if($res === FALSE)
    {
      return $mod->Lang('error_usertag', $udt);
    }
    
    return $res;
  }
  
  
  function PrePopulateAdminForm($formDescriptor)
  {
    $mod                             = formbuilder_utils::GetFB();
    $main                            = array();
    $adv                             = array();
    $gCms                            = cmsms();
    $usertaglist                     = array();
    $usertaglist[$mod->lang('none')] = -1;
    
    if(!formbuilder_utils::is_CMS2_3())
    {
      $udt_handler_pref = $mod->GetPreference('udt_handler_pref', 'core_udts');
      
      if($udt_handler_pref == 'core_sps')
      {
        $udt_handler_pref = 'core_udts';
      }
      
      if($udt_handler_pref == 'core_udts')
      {
        $usertags = $gCms->GetUserTagOperations()->ListUserTags();
      }
    }
    else
    {
      $udt_handler_pref = $mod->GetPreference('udt_handler_pref', 'core_sps');
      
      if($udt_handler_pref == 'core_udts')
      {
        $udt_handler_pref = 'core_sps';
      }
      
      if($udt_handler_pref == 'core_sps')
      {
        $usertags = $gCms->GetSimplePluginOperations()->get_list();
      }
    }
    
    # temp fix for current core bug (JM)
    $usertags = is_array($usertags) ? $usertags : [];
    
    foreach($usertags as $key => $value)
    {
      #$usertaglist[$value] = $key;
      # fix for simple plugins et al.
      $usertaglist[$value] = $value;
    }
    
    $main[] = array(
      $mod->Lang('title_udt_name'),
      $mod->CreateInputDropdown(
        $formDescriptor,
        'fbrp_opt_udtname', $usertaglist, -1,
        $this->GetOption('udtname')
      )
    );
    
    $main[] = array(
      $mod->Lang('title_export_form_to_udt'),
      $mod->CreateInputHidden($formDescriptor, 'fbrp_opt_export_form', '0') .
      $mod->CreateInputCheckbox(
        $formDescriptor, 'fbrp_opt_export_form',
        '1', $this->GetOption('export_form', '0')
      )
    );
    
    return array('main' => $main, 'adv' => $adv);
  }
  
}

?>