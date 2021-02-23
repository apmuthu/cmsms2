<?php
/*
 * FormBuilder. Copyright (c) 2005-2006 Samuel Goldstein <sjg@cmsmodules.com>
 * More info at http://dev.cmsmadesimple.org/projects/formbuilder
 *
 * A Module for CMS Made Simple, Copyright (c) 2006 by Ted Kulp (wishy@cmsmadesimple.org)
 * This project's homepage is: http://www.cmsmadesimple.org
 */

class fbDirectoryScannerField extends fbFieldBase
{
  
  public function __construct(&$form_ptr, &$params)
  {
    parent::__construct($form_ptr, $params);
    $mod = formbuilder_utils::GetFB();
    $this->Type = 'DirectoryScannerField';
    $this->HasLabel = 0;
    $this->NeedsDiv = 0;
    $this->DisplayInForm = true;
    $this->ValidationTypes = array(
      $mod->Lang('validation_none') => 'none');
    $this->sortable = false;
  }
  
  function GetHumanReadableValue($as_string = true)
  {
    /*
    if($this->GetOption('suppress_filename', '0') != '0')
    {
      return '';
    }
    */
    $path = $this->GetOption('path', '');
    $smarty_eval = $this->GetOption('smarty_eval', 1);
    $smarty = \cms_utils::get_smarty();
    $config = \cms_utils::get_config();
    $base = $config['uploads_path'];
    $root = $config['root_path'];
    $root_url = $config['root_url'];
    
    if($smarty_eval)
    {
      $eval = $smarty->fetch('string:' . $path );
      $src = urldecode($eval);
    }
    else
    {
      $src = urldecode($path);
    }
    
    
    $dir = \cms_join_path($base, $src);
    $files = array();
    
    if(is_dir($dir))
    {
      $files = glob($dir . DIRECTORY_SEPARATOR . '*');
    }
    
    $ret = array();
    if($files)
    {
      foreach($files as $one)
      {
        $t = str_replace($root, $root_url , $one);
        $ret[] = str_replace(DIRECTORY_SEPARATOR, '/' , $t);
      }
    }
    
    if($as_string)
    {
      return implode(', ', $ret);
    }
    else
    {
      return $ret;
    }
  }
  
  public function StatusInfo()
  {
    $ret = '';
    
    if($this->GetOption('path', '') != '')
    {
      $ret .= $this->GetOption('path', '');
    }
    return $ret;
  }
  
  
  public function PrePopulateAdminForm($formDescriptor)
  {
    $mod = formbuilder_utils::GetFB();
    
    $main = array(
      array(
        $mod->Lang('title_path'),
        $mod->CreateInputText($formDescriptor, 'fbrp_opt_path', $this->GetOption('path', ''), 25, 1024)
      )
    );
  
    array_push( $main, array(
    $mod->Lang('title_smarty_eval'),
        $mod->CreateInputHidden($formDescriptor, 'fbrp_opt_smarty_eval', 0) .
        $mod->CreateInputCheckbox(
          $formDescriptor, 'fbrp_opt_smarty_eval',
          1, $this->GetOption('smarty_eval', 1)
        )
                )
      );
  
    array_push($main, array($mod->Lang('title_remove_file_from_server'),
                            $mod->CreateInputHidden($formDescriptor,'fbrp_opt_remove_file','0').
                            $mod->CreateInputCheckbox($formDescriptor,
                                                      'fbrp_opt_remove_file', '1',
                                                      $this->GetOption('remove_file','0'))));

    
    $adv = array();
    
    array_push($adv, array(
            $mod->Lang('title_suppress_attachment'),
            $mod->CreateInputHidden($formDescriptor, 'fbrp_opt_suppress_attachment', 0) .
            $mod->CreateInputCheckbox(
              $formDescriptor, 'fbrp_opt_suppress_attachment',
              1, $this->GetOption('suppress_attachment', 1)
            )
          )
    );
    
    return array('main' => $main, 'adv' => $adv);
  }
  
  function AdminValidate()
  {
    $mod     = formbuilder_utils::GetFB();
    $src     = urldecode($this->GetOption('path', ''));
    $srcfile = NULL;
    $config  = cmsms()->GetConfig();
    $base    = $config['uploads_path'];
    
    if(startswith($src, $config['root_url']))
    {
      $src = str_replace($config['root_url'], $base, $src);
    }
    else if(startswith($src, $config['ssl_url']))
    {
      $src = str_replace($config['ssl_url'], $base, $src);
    }
    /*
    if(strpos($file = realpath($src), $base) === 0 && is_file($file))
    {
      $this->SetOption('path', substr($src, count($config['root_path'])));
      return array(TRUE, '');
    }
    else if(strpos($file = realpath(cms_join_path($config['root_path'], $src)), $base) === 0 && is_file($file))
    {
      $this->SetOption('path', $src);
      $this->SetOption('file_destination', $config['root_path']);
      
      return array(TRUE, '');
    }
    else if(strpos($file = realpath(cms_join_path($config['uploads_path'], $src)), $base) === 0 && is_file($file))
    {
      $this->SetOption('path', $src);
      $this->SetOption('file_destination', $config['uploads_path']);
      
      return array(TRUE, '');
    }
    else
    {
      return array(FALSE, $mod->Lang('file_doesnt_exists', array($this->GetOption('value', ''))));
    }
    
    */
  
    return array(TRUE, '');
  }
  
  function get_path()
  {
    $smarty_eval = $this->GetOption('smarty_eval', 1);
    $smarty = \cms_utils::get_smarty();
    $config = \cms_utils::get_config();
    $base    = $config['uploads_path'];
    $path = $this->GetOption('path', '');
   
    if($smarty_eval)
    {
      $eval = $smarty->fetch('string:' . $path );
      $src = urldecode($eval);
    }
    else
    {
      $src = urldecode($path);
    }
    
    return cms_join_path($base, $src);
  }
  
  public function PostDispositionAction()
  {
    if ($this->GetOption('remove_file','0') == '1')
    {
      $files = $this->GetHumanReadableValue(false);
      $path = $this->get_path();
      
      if($files)
      {
        foreach( $files as $one)
        {
          $tfn = explode('/', $one);
          $fullfilename = cms_join_path($path , end($tfn) );
          
          if (file_exists($fullfilename))
          {
            unlink($fullfilename);
          }
        }
      }
      
      if( is_dir($path) && $this->_is_dir_empty($path)) rmdir($path);
    }
  }
  
  private function _is_dir_empty($dir)
  {
    if (!is_readable($dir)) return NULL;
    return(count( scandir($dir) ) == 2);
  }
}
?>
