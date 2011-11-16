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

class uf_response
{
  private $_attributes;
  private $_headers;
  private $_data;
  private $_javascript;

  public function __construct()
  {
    $this->_attributes = array('template' => 'index');
    $this->_headers = array();
    $this->header('Content-Type','text/html; charset=UTF-8');
    $this->_data = '';
  }

  public function attribute($name,$value = NULL)
  {
    if($value !== NULL)
    {
      $this->_attributes[$name] = $value;
    }
    else
    {
      return array_key_exists($name,$this->_attributes) ? $this->_attributes[$name] : $value;
    }
  }

  public function header($name,$value = NULL)
  {
    if($value !== NULL)
    {
      $this->_headers[$name] = $value;
    }
    else
    {
      return array_key_exists($name,$this->_headers) ? $this->_headers[$name] : $value;
    }
  }
  
  public function expires_in_hours($hours = 12)
  {
    $expires = 60*60*$hours;
    $this->header('Pragma','public');
    $this->header('Cache-Control',"public, max-age=".$expires);
    $this->header('Expires', gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    
  }

  public function header404()
  {
    $this->header('#HTTP/',$_SERVER["SERVER_PROTOCOL"].' 404 Not Found'); // Special header for 404 errors
    $this->header('Status','404 Not Found');
  }

  public function headers()
  {
    $headers = array();
    foreach($this->_headers as $name => $value)
    {
      // Special header for 404 errors
      $headers[] = ($name == '#HTTP/') ? $value : ($name.': '.$value);
    }
    return $headers;
  }

  public function redirect($url)
  {
    if(strpos($url, '/') === 0)
    {
      $url = 'http://'.$_SERVER['HTTP_HOST'].$url; //$_SERVER['HTTP_HOST'] includes the domain and port from original request
    } 
    //header('Location: http://www.google.se');   
    $this->header('Location',$url);
  }
  
  public function redirect_cap(uf_controller $from_controller, $to_controller_name, $to_action_name = NULL, $parameters = NULL, $override_language = '')
  {
    $view = new uf_view;
    $view->set_controller($from_controller);
    $params = func_get_args();
    unset($params[0]);
    $uri = call_user_func_array(array($view, 'cap'), $params);
    return $this->redirect($uri);
  }
  
  public function data($data = NULL)
  {
    if($data !== NULL)
    {
      $this->_data .= $data;
    }
    else
    {
      return $this->_data;
    }
  }

  public function javascript($javascript = NULL)
  {
    if($javascript !== NULL)
    {
      $this->_javascript .= $javascript."\n";
    }
    else
    {
      return $this->_javascript;
    }
  }

  // slots should be used to send data to the base controller view/template.
  public function get_slot($name)
  {
    if (!isset($this->_slots[$name])) return NULL;
    return $this->_slots[$name];
  }
  
  public function set_slot($name, $data = NULL)
  {
    $this->_slots[$name] = $data;
  }

  public function append_slot($name, $data = NULL)
  {
    $this->_slots[$name] .= $data;
  }
}

?>