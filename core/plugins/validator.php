<?
/**
 * Project: umvc: A Model View Controller framework
 *
 * @author David Brännvall, Jonatan Wallmander, HR North Sweden AB http://hrnorth.se, Copyright (C) 2011.
 * @see The GNU Public License (GPL)
 */
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

class validator_helper
{
  private $_form_id;
  private $_rules;
  private $_request;
  private $_response;
  private $_result;
  private $_translate_callback;

  public function __construct($form_id, $request, $response)
  {
    $this->_form_id = $form_id;
    $this->_rules = array();
    $this->_request = $request;
    $this->_response = $response;
    $this->_result = array();
  }

  public function set_translate_callback($callback)
  {
    $this->_translate_callback = $callback;    
  }

  public function add_rule($name, $callback)
  {
    $name = uf_controller::str_to_controller($name);
    $this->_rules[$name][] = array('callback' => $callback);
  }

  public function validate()
  {
    $this->_result = array();
    $result = TRUE;
    foreach($this->_request->parameters() as $key => $val)
    {
      $key = uf_controller::str_to_controller($key);
      if(array_key_exists($key, $this->_rules)) {
        $result_for_key = TRUE;
        foreach($this->_rules[$key] as $rules)
        {
          $message_id = '';

          // Call validator function
          $callback = $rules['callback'];
          $r = is_array($callback)
            ? call_user_func($callback, $val, $message_id)
            : $callback($val, $message_id);

          if(!$r)
          {
            // The validator failed so trigger an event for it
            $message = !is_null($this->_translate_callback)
              ? call_user_func($this->_translate_callback, $message_id)
              : $message_id; 
            $data = array(
              'form_id' => $this->_form_id,
              'name' => $key,
              'message' => $message);
            $this->_response->javascript('$(function(){umvc.trigger("umvc.validator.error",'.json_encode($data).');});');
            $result = FALSE;
            $result_for_key = FALSE;
          }
        }
        $this->_result[$key] = $result_for_key;
      }
    }

    if($result && count($this->_rules))
    {
      $data = array('message' => 'success');
      $this->_response->javascript('$(function(){umvc.trigger("umvc.validator.success",'.json_encode($data).');});');
    }

    return $result;
  }
  
  public function result($name)
  {
    return isset($this->_result[$name]) ? $this->_result[$name] : FALSE;
  }
}

class validator_plugin extends uf_plugin
{
  private $_validators;  

  public function __construct()
  {
    $this->_validators = array();    
  }

  public function validator($id)
  {
    if(!isset($this->_validators[$id]))
    {
      $this->_validators[$id] = new validator_helper($id, $this->get_controller()->request(), $this->get_controller()->response());
    }
    return  $this->_validators[$id];
  }
}

/* EOF */