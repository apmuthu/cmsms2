<?php


namespace FormBuilder;


class forms
{
  const SESSION_LIST_NAME = '___FB_FORMS_LIST__';
  private static $_forms = [];
  
  private static function _store()
  {
    $_SESSION[self::SESSION_LIST_NAME] = self::$_forms;
  }
  
  private static function _read()
  {
    self::$_forms = isset($_SESSION[self::SESSION_LIST_NAME]) ? $_SESSION[self::SESSION_LIST_NAME] : [];
  }
  
  static function enqueue($fid, $falias)
  {
    self::$_forms[$fid] = $falias;
    self::_store();
  }
  
  static function dequeue($fid)
  {
    unset(self::$_forms[$fid]);
    self::_store();
  }
  
  static function is_active_id($fid)
  {
    self::_read();
    return in_array($fid, array_keys(self::$_forms) );
  }
  
  static function is_active_alias($falias)
  {
    self::_read();
    return in_array($falias, self::$_forms);
  }
  
  static function clear()
  {
    self::$_forms = [];
    self::_store();
  }
  
}

?>