<?php
#-------------------------------------------------------------------------
# Module: FormBuilder
# Author: Samuel Goldstein, Morten Poulsen
#-------------------------------------------------------------------------
# CMS Made Simple is (c) 2004 - 2011 by Ted Kulp (wishy@cmsmadesimple.org)
# CMS Made Simple is (c) 2011 - 2014 by The CMSMS Dev Team
# This project's homepage is: http://www.cmsmadesimple.org
# The module's homepage is: http://dev.cmsmadesimple.org/projects/formbuilder
#-------------------------------------------------------------------------
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#-------------------------------------------------------------------------
use \FormBuilder\forms;
if(!defined('CMS_VERSION')) exit;

# make sure this is not a direct url access
if( isset($params['form_id']) && !formbuilder_utils::filter_request($params) )
{
  if( !forms::is_active_id($params['form_id']) )
  {
    echo $this->Lang('submit_error', $this->Lang('xss_error') );
    return;
  }
}

if(!isset($params['form_id']) && isset($params['form']))
{
  // get the form by name, not ID
  $params['form_id'] = $this->GetFormIDFromAlias($params['form']);
}

if(isset($params['form_id']) && isset($params['form']))
{
  forms::enqueue($params['form_id'], $params['form']);
}

$inline = FALSE;

if((isset($params['inline'])) && preg_match('/t(rue)*|y(yes)*|1/i', $params['inline']))
{
  $inline = TRUE;
}

$fbrp_callcount = 0;
$aeform         = new fbForm($this, $params, TRUE, TRUE);

$fld = $aeform->GetFormBrowserField();

if($fld !== FALSE && $fld->GetOption('feu_bind', '0') == '1')
{
  $feu = \cms_utils::get_module('FrontEndUsers');
  
  if($feu == FALSE)
  {
    debug_display("FAILED to instantiate FEU!");
    
    return;
  }
  
  if($feu->LoggedInId() === FALSE)
  {
    echo $this->Lang('please_login');
    
    return;
  }
}

if(!($inline || ($aeform->GetAttr('inline', '0') == '1'))) $id = 'cntnt01';


$smarty->assign('fb_form_has_validation_errors', 0);
$smarty->assign('fb_show_submission_errors', 0);
$smarty->assign('fb_form_header', $aeform->RenderFormHeader());
$smarty->assign('fb_form_footer', $aeform->RenderFormFooter());

$finished      = FALSE;
$fieldExpandOp = FALSE;

if(isset($params['fbrp_callcount']))
{
  $fbrp_callcount = (int)$params['fbrp_callcount'];
}

foreach($params as $pKey => $pVal)
{
  if(substr($pKey, 0, 9) == 'fbrp_FeX_' || substr($pKey, 0, 9) == 'fbrp_FeD_')
  {
    // expanding or shrinking a field
    $fieldExpandOp = TRUE;
  }
}

if(!$fieldExpandOp && (($aeform->GetPageCount() > 1 && $aeform->GetPageNumber() > 0) ||
                       (isset($params['fbrp_done']) && $params['fbrp_done'] == 1))
)
{
  // Validate form
  $res = $aeform->Validate();
  
  // We have validate errors
  if($res[0] === FALSE)
  {
    $smarty->assign('fb_form_validation_errors', $res[1]);
    $smarty->assign('fb_form_has_validation_errors', 1);
    
    $aeform->PageBack();
    
    // No validate errors, proceed
  }
  else if(isset($params['fbrp_done']) && $params['fbrp_done'] == 1)
  {
    
    // Check captcha, if installed
    $ok      = TRUE;
    $captcha = \cms_utils::get_module('Captcha');
    
    if($aeform->GetAttr('use_captcha', '0') == '1' && $captcha != NULL)
    {
      #this should work with Captcha 0.4.6 and higher (JM)
      $cptcneedsinput = method_exists($captcha, 'NeedsInputField') ? $captcha->NeedsInputField() : TRUE;
      $tovalidate     = $cptcneedsinput ? $params['fbrp_captcha_phrase'] : '';
      
      if(!$captcha->CheckCaptcha($tovalidate))
      {
        $smarty->assign('captcha_error', $aeform->GetAttr('captcha_wrong', $this->Lang('wrong_captcha')));
        
        $aeform->PageBack();
        $ok = FALSE;
      }
      
    }
    
    // All ok, dispose form and manage fileuploads
    if($ok)
    {
      $finished = TRUE;
      $aeform->manageFileUploads();
      # clear the forms queue before final disposition!
      forms::clear();
      $results = $aeform->Dispose($returnid);
    }
  }
}

if(!$finished)
{
  $parms              = array();
  $parms['form_name'] = $aeform->GetName();
  $parms['form_id']   = $aeform->GetId();
  $this->SendEvent('OnFormBuilderFormDisplay', $parms);
  
  if(isset($params['fb_from_fb']))
  {
    $smarty->assign(
      'fb_form_start',
      $this->CreateFormStart(
        $id, 'user_edit_resp', $returnid, 'post',
        'multipart/form-data',
        ($aeform->GetAttr('inline', '0') == '1'), '',
        array('fbrp_callcount' => $fbrp_callcount + 1)
      ) .
      $this->CreateInputHidden($id, 'response_id', isset($params['response_id']) ? $params['response_id'] : '-1')
    );
  }
  else
  {
    $smarty->assign(
      'fb_form_start',
      $this->CreateFormStart(
        $id, 'default', $returnid, 'post',
        'multipart/form-data',
        ($aeform->GetAttr('inline', '0') == '1'), '',
        array('fbrp_callcount' => $fbrp_callcount + 1)
      )
    );
  }
  
  $smarty->assign('fb_form_end', $this->CreateFormEnd());
  $smarty->assign('fb_form_done', 0);
}
else
{
  $smarty->assign('fb_form_done', 1);
  
  if($results[0] == TRUE)
  {
    $parms              = array();
    $parms['form_name'] = $aeform->GetName();
    $parms['form_id']   = $aeform->GetId();
    $this->SendEvent('OnFormBuilderFormSubmit', $parms);
    
    $act = $aeform->GetAttr('submit_action', 'text');
    
    if($act == 'text')
    {
      $message = $aeform->GetAttr('submit_response', '');
      
      $aeform->setFinishedFormSmarty(TRUE);
      echo $this->ProcessTemplateFromData($message);
      
      return;
    }
    else if($act == 'redir')
    {
      $ret = $aeform->GetAttr('redirect_page', '-1');
      if($ret != -1)
      {
        $this->RedirectContent($ret);
        
        return;
      }
    }
  }
  else
  {
    $parms                = array();
    $params['fbrp_error'] = '';
    $smarty->assign(
      'fb_submission_error',
      $this->Lang('submission_error')
    );
    
    $show = $this->GetPreference('hide_errors', '1');
    $smarty->assign('fb_submission_error_list', $results[1]);
    $smarty->assign('fb_show_submission_errors', $show);
    
    $parms['form_name'] = $aeform->GetName();
    $parms['form_id']   = $aeform->GetId();
    $this->SendEvent('OnFormBuilderFormSubmitError', $parms);
  }
}

######### a few fixes for new UDTs and simple plugins #########
$udtonce  = $aeform->GetAttr('predisplay_udt', '');
$udtevery = $aeform->GetAttr('predisplay_each_udt', '');

if( !$finished
    && (
          (!empty($udtonce) && $udtonce != '-1')
          || (!empty($udtevery) && $udtevery != '-1')
        )
  )
{
  $udt_handler_pref = $this->GetPreference('udt_handler_pref', 'core_udts');
  $usertagops = NULL;
  
  if( !formbuilder_utils::is_CMS2_3() )
  {
    if($udt_handler_pref == 'core_udts')
    {
      $method_name = 'CallUserTag';
      $usertagops = $gCms->GetUserTagOperations();
    }
  }
  else
  {
    if($udt_handler_pref == 'core_sps')
    {
      $method_name = 'call_plugin';
      $usertagops = $gCms->GetSimplePluginOperations();
    }
  }
  
  if( empty($usertagops) )
  {
    $UDTsHandler = \cms_utils::get_module($udt_handler_pref);
    
    if( is_object($UDTsHandler) )
    {
      $method_name = 'CallUserTag';
      $usertagops = $UDTsHandler->GetUserTagOperations();
    }
    else
    {
      # throw an error here? (JM)
    }
  }
  
  $parms         = $params;
  $parms['FORM'] = $aeform;
  
  if(isset($fbrp_callcount) && $fbrp_callcount == 0 && !empty($udtonce) && "-1" != $udtonce)
  {
    $tmp = $usertagops->$method_name($udtonce, $parms);
  }
  
  if(!empty($udtevery) && "-1" != $udtevery)
  {
    $tmp = $usertagops->$method_name($udtevery, $parms);
  }
}

echo $aeform->RenderForm($id, $params, $returnid);

#
# EOF
#
?>