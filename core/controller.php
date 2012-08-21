<?php
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

class uf_controller
{
  // PRIVATE DATA
  private $_buffer_ref_count;
  private $_call_stack;
  public $_magic_methods;
  
  public function load_view($view, $data = NULL)
  {
    $controller_identifier = substr(get_class($this),0,-11);

    // include the view
    $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/view/v_'.$view.'.php';
    if(!is_file($file))
    {
      $file = uf_application::app_dir().'/modules/'.$controller_identifier.'/view/v_'.$view.'.php';
      if(!is_file($file))
      {
        $file = uf_application::app_sites_host_dir().'/base/view/v_'.$view.'.php';
        if(!is_file($file))
        {
          $file = uf_application::app_dir().'/lib/view/v_'.$view.'.php';
        }        
      }
    }    
    uf_include_view($this,$file, $data);
  }
  
  private function _load_base($view)
  {
    // try to load view from controller, else fallback to base
    if(file_exists($dir.'/view/v_'.$view.'.php'))
    {
      uf_include_view($this,$dir.'/view/v_'.$view.'.php', $data);
      if(file_exists($dir.'/view/v_'.$view.'.js'))
      {
        $this->response()->javascript(file_get_contents($dir.'/view/v_'.$view.'.js'));
      }      
    } 
    else {
      uf_include_view($this,uf_application::app_sites_host_dir().'/base/view/v_'.$view.'.php', $data);
      if(file_exists(uf_application::app_sites_host_dir().'/base/view/v_'.$view.'.js'))
      {
        $this->response()->javascript(file_get_contents(uf_application::app_sites_host_dir().'/base/view/v_'.$view.'.js'));
      }
    }
  }

  private function _push_call_stack_frame($caller,$request,$response,$options)
  {
    array_push(
      $this->_call_stack,
      array(
        'caller' => $caller,
        'request' => $request,
        'response' => $response,
        'options' => $options
      )
    );
  }

  private function _pop_call_stack_frame()
  {
    array_pop($this->_call_stack);
  }

  // BASE METHODS
  
  public function before_action() { return TRUE; }
  public function after_action() { return TRUE; }

  // PUBLIC METHODS

  // Error logging
  public function die_log_error($string)
  {
    $controller_identifier = substr(get_class($this),0,-11);
    // look for config file.
    if (is_file(UF_BASE.'/config/log_error.conf'))
    {
      include(UF_BASE.'/config/log_error.conf');
    } else
    {
      global $log_error_values;
      $log_error_values = array();
      array_push($log_error_values,'HTTP_HOST');
      array_push($log_error_values,'REMOTE_ADDR');
      array_push($log_error_values,'HTTP_X_FORWARDED_FOR');
      array_push($log_error_values,'SCRIPT_NAME');
      array_push($log_error_values,'REQUEST_URI');
      array_push($log_error_values,'HTTP_USER_AGENT');
      array_push($log_error_values,'HTTP_COOKIE');
      array_push($log_error_values,'REQUEST_TIME');
    }
    global $log_error_values;
    $trace=debug_backtrace();
    $caller=array_shift($trace);
    $line = $caller['line'];
    $file = $caller['file'];
    $caller=array_shift($trace);
    
    $error_string = "\n******* UMVC ERROR *******\n";
    $error_string .= 'Controller: '.$controller_identifier."\n";
    $error_string .= 'Action: '.$caller['function']."\n";
    $error_string .= 'File: '.$file."\n";
    $error_string .= 'Line: '.$line."\n";
    $error_string .= 'Message: '.$string."\n";
    $error_string .= "******* HTTP VARIABLES *******\n";

    foreach ($log_error_values as $lev)
    {
      if (isset($_SERVER[$lev])) $error_string .= $lev.' = '.$_SERVER[$lev]."\n";
    }
    $error_string .= '******* END *******'."\n";
    error_log($error_string);
    die();
  }


  // Language/translation support in UMVC
  // For how to build language codes, see
  // See RFC 5646 (http://tools.ietf.org/html/rfc5646)

  // For optimization purpouses, UMVC limits combinations to
  // the syntax: language-location
  // i.e. en-GB, en-US, en-AU etc.
  //

  // to support parameter translations, define a method like this (if the action is "debug"), see umvc examples for
  // a test case
  // parameters coming from the query string for the index action
  /*public function debug_translate_param($in_parameter_name)
  {
    echo 'translate-param';
    switch ($in_parameter_name)
    {
      case 'parameter1': return 'param1';
      case 'myparameter1': return 'param1';
      case 'parameter2': return 'param2';
    }
  }
  */

  // view API

  // input:    english name
  // returns:  translated name into current language (overridable)
  public function view_lang_get_module_name($controller = '', $language = '')
  {
    // include the baked file above based on module name
    //
    if (empty($controller))
    {
      $controller = $this->request()->get_controller();
    }

    // save the controller name for subsequent calls for action and parameter translations
    global $uf_controller_lang_module_name_cache;
    $uf_controller_lang_module_name_cache = $controller;
    
    $lang = $language;
    if ($language == '') $lang = uf_application::get_language();

    $controller_identifier = uf_controller::str_to_controller($controller);
    $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/am_'.$controller_identifier.'.php';
    if(!file_exists($file))
    {
      $file = uf_application::app_dir().'/modules/'.$controller_identifier.'/am_'.$controller_identifier.'.php';
    }
    $ret = NULL;
    if(file_exists($file))
      $ret = include($file);
    if (!is_string($ret))
      return $controller;
    else
      return $ret;
  }


  // input:    english/code name of action
  // returns:  current language version

  public function view_lang_get_action_name($action, $controller = '', $language = '')
  {
    $lang = $language;
    if ($language == '') $lang = uf_application::get_language();

    // deal with the cached names
    if ($controller == '')
    {
      global $uf_controller_lang_module_name_cache;
      $controller = $uf_controller_lang_module_name_cache;
      if ($controller == '') return FALSE;
    }
    global $uf_controller_lang_action_name_cache;
    $uf_controller_lang_action_name_cache = $action;
    //------------------------

    $controller_identifier = uf_controller::str_to_controller($controller);
    $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/aa_'.$controller_identifier.'.php';
    if(!file_exists($file))
    {
      $file = uf_application::app_dir().'/modules/'.$controller_identifier.'/aa_'.$controller_identifier.'.php';
    }


    $ret = NULL;
    if (file_exists($file))
      $ret = include($file);

    if (!is_string($ret))
      return $action;
    else
      return $ret;
  }

  // input:    english/code name of action
  // returns:  current language version

  public function view_lang_get_parameter_name($param, $action = '', $controller = '', $language = '')
  {
    $lang = $language;
    if ($language == '') $lang = uf_application::get_language();

    // deal with the cached names
    if ($controller == '')
    {
      global $uf_controller_lang_module_name_cache;
      $controller = $uf_controller_lang_module_name_cache;
      if ($controller == '') return FALSE;
    }
    if ($action == '')
    {
      global $uf_controller_lang_action_name_cache;
      $action = $uf_controller_lang_action_name_cache;
      if ($action == '') return FALSE;
    }
    //------------------------


    $controller_identifier = uf_controller::str_to_controller($controller);
    $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/ap_'.$controller_identifier.'.php';

    if(!file_exists($file))
    {
      $file = uf_application::app_dir().'/modules/'.$controller_identifier.'/ap_'.$controller_identifier.'.php';
    }
    $ret = include($file);
    if (!is_string($ret))
      return $param;
    else
      return $ret;
  }

  static public function str_to_controller($str)
  {
    $f = array('ö','å','ä','ø','æ','ñ','ü','.',',',';','-','–','/',' ');
    $t = array('o','a','a','o','a','n','u','_','_','_','_','_','_','_');
    $result = preg_replace('/[^\d\w_-]/','',str_replace($f,$t,$str));
    return preg_replace('/_+/','_',$result);
  }

  public function load_plugin($plugin_name)
  {
    return $this->get_plugin($plugin_name);
  }
  
  public function get_plugin($plugin_name)
  {
    if(!isset($this->$plugin_name))
    {
      $this->$plugin_name = uf_plugin::load_plugin($this, $plugin_name);      

      $this->_magic_methods = 
        array_merge(
          $this->$plugin_name->get_magic_methods(),
          $this->_magic_methods);
    }
    return $this->$plugin_name;
  }

  public function __construct()
  {
    $this->_call_stack = array();
    $this->_buffer_ref_count = 0;    
    $this->_magic_methods = array();
  }

  public function __destruct()
  {
    while($this->_buffer_ref_count)
    {
      $this->end_buffering();
    }
  }

  public function __call($method, $args)
  {
    if(isset($this->_magic_methods[$method]))
    {
      $m = $this->_magic_methods[$method];
      if(count($m) > 0)
      {
        switch($m[0])
        {
          case 'plugins':
            if(count($m) >= 3)
            {
              return call_user_func_array(array($this->get_plugin($m[1]), $m[2]), $args);            
            }
        }        
      }
    }
  }
  
  public function caller()
  {
    if (!count($this->_call_stack)) return NULL;
    return $this->_call_stack[count($this->_call_stack) - 1]['caller'];
  }

  public function request()
  {
    if (!count($this->_call_stack)) return NULL;
    return $this->_call_stack[count($this->_call_stack) - 1]['request'];
  }

  public function response()
  {
    if (!count($this->_call_stack)) return NULL;
    return $this->_call_stack[count($this->_call_stack) - 1]['response'];
  }

  public function start_buffering()
  {
    $this->_buffer_ref_count++;
    ob_start();
  }

  public function end_buffering()
  {
    $this->_buffer_ref_count--;
    $this->response()->data(ob_get_contents());
    ob_end_clean();
  }

  public function option($name,$default_value = NULL)
  {
    return array_key_exists($name,$this->_call_stack[count($this->_call_stack) - 1]['options']) ? $this->_call_stack[count($this->_call_stack) - 1]['options'] : $default_value;
  }


  static public function execute_base($request,$response,$options = NULL)
  {
    $controller_class = self::str_to_controller($request->get_controller()).'_controller';
    if(class_exists($controller_class))
    {
      // Normal module action
      $controller = new $controller_class;
      $controller->_push_call_stack_frame($controller,$request,$response,$options !== NULL ? $options : array());
      if($controller->execute_action($controller,$request->get_action(),$request,$response,array('enable_buffering' => TRUE)) === FALSE)
      {
        // 404 missing action
        $controller->_error(404);
      }
    }
    else
    {
      // 404 missing module
      $controller = new base_controller;
      $controller->_push_call_stack_frame($controller,$request,$response,$options !== NULL ? $options : array());
      $controller->_error(404);
    }
    $controller->content = $response->data();

    // Send headers
    foreach($response->headers() as $header)
    {
      header($header);
    }

    uf_include_view($controller, uf_application::app_sites_host_dir().'/base/view/v_'.$response->attribute('template').'.php');
    if(class_exists($controller_class))
    {
      $controller->_pop_call_stack_frame();
      $controller = NULL;
    }
  }
  
  public function execute_action($caller,$action,$request,&$response,$options = NULL)
  {
    if ($action == '')
    {
      $action_identifier = 'index';
    } else {
      $action_identifier = uf_controller::str_to_controller($action);
    }

    // auto load plugins, there might be a better place to do this
    $plugins = uf_application::get_config('load_plugins');
    if(is_array($plugins))
    {
      foreach($plugins as $plugin)
      {
        $this->get_plugin($plugin);
      }      
    }
    
    // load project/base language files
    uf_include_language($this, uf_application::app_sites_host_dir().'/language/l_base.'.uf_application::get_language().'.php');

    // load module/controller local language file
    $controller = substr(get_class($this),0,-11);
    if(file_exists(
      uf_application::app_sites_host_dir().
      '/modules/'.
      $controller.
      '/c_'.$controller.'.php'))
    {
      uf_include_language($this,uf_application::app_sites_host_dir().'/modules/'.$controller.'/language/l_'.$controller.'.'.uf_application::get_language().'.php');      
    }
    else
    {
      uf_include_language($this,uf_application::app_dir().'/modules/'.$controller.'/language/l_'.$controller.'.'.uf_application::get_language().'.php');      
    }

    // 404 action?
    // handle  www.foo.com/index/

    // loop the request parameters and translate them
    if (method_exists($this,$action_identifier.'_translate_param'))
    {
      $param_names = $request->get_parameter_names();
      if (count($param_names))
      foreach ($param_names as $name)
      {
        $n_name = call_user_func(array($this,$action_identifier.'_translate_param'),$name);
        if (is_string($n_name))
        $request->set_parameter_name($name, $n_name);
      }
    }
    
    // handle nonexistent controller functions
    if(!method_exists($this, $action_identifier))
    {
      if (!method_exists($this, 'error'))
      {
        return FALSE;
      }
      else
      {
        $action_identifier = 'error';
      }
    }

    $this->_push_call_stack_frame($caller,$request,$response,$options !== NULL ? $options : array());

    // start buffering?
    if($this->option('enable_buffering'))
    {
      $this->start_buffering();
    }

    //$this->_validators = array();

    $before_action_ret = $this->before_action();
    if ($before_action_ret === TRUE || $before_action_ret === NULL)
    {
      // execute action
      $view = call_user_func(array($this, $action_identifier));

      $this->after_action();

      //$this->_validator = array();

      if (is_integer($view))
      {
        $this->_error($view);
      } else
      {
        // default view?
        if($view === NULL || $view === TRUE)
        {
          $view = $action_identifier;
        }

        // no view?
        if($view !== FALSE)
        {
          $this->load_view($view);            
        }
      }
    } else
    if ($before_action_ret === FALSE)
    {
    } else
    $this->_error($before_action_ret);

    // stop buffering?
    if($this->option('enable_buffering'))
    {
      $this->end_buffering();
    }

    $this->_pop_call_stack_frame();

    return TRUE;
  }

  public function index() {}

  public function _error($code)
  {
    ob_start();
      uf_include_language($this,uf_application::app_sites_host_dir().'/language/l_base.'.uf_application::get_language().'.php');
      uf_include_view($this,uf_application::app_dir().'/errors/v_'.$code.'.php');
      $this->response()->data(ob_get_contents());
    ob_end_clean();
    if ($code == 404) $this->response()->header404();
    return FALSE;
  }
  
  public static function autoload_controller($class)
  {
    if(substr($class,-11) === '_controller')
    {
      $controller_identifier = substr($class,0,-11);

      if ($controller_identifier == 'base')
      {
        $file = uf_application::app_sites_host_dir().'/base/c_base.php';
      }

      else 
      {
        $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/c_'.$controller_identifier.'.php';
        if(!file_exists($file))
        {
          $file = uf_application::app_dir().'/modules/'.$controller_identifier.'/c_'.$controller_identifier.'.php';
        }
      }
      if (file_exists($file))
      {
        include_once($file);        
      }
    }
  }
}


class uf_view
{
  var $controller = NULL;
  public function set_controller($new_controller)
  {
    $this->controller = $new_controller;
  }
  
  public function lang_get()
  {
    $lang_uri = '';
    $language = uf_application::get_language();
    if (uf_application::is_language_overridden())
    {
      // add language prefix
      $lang_uri = '/'.$language;
    }
    return $lang_uri;
  }

  public function lang_build_uri_module($module, $values = NULL, $override_language = '')
  {
    // TODO
  }

  // grabs all existing parameters and merges with new values from $values
  public function cap($controller_name, $action_name = NULL, $parameters = NULL, $override_language = '')
  {
    if ($controller_name == '') return '/';
    
    $request = $this->controller->request();
    $language = uf_application::get_language();
    $internal_language_override = 0;

    if (!empty($override_language))
    {
      if ($override_language != $language)
      {
        $language = $override_language;
        $internal_language_override = 1;
      }
    }

    $new_uri = '';
    
    global $routing_persist_base_uri;
    if (strlen($routing_persist_base_uri))
    {
      $new_uri = $routing_persist_base_uri;
    }


    $controller = NULL;
    if (empty($controller_name))
    {
      $controller = '';
    }
    else
    {
      $controller = $this->controller->view_lang_get_module_name($controller_name, $language);
    }
    $action = '';
    if (!empty($action_name))
    {
      $action = $this->controller->view_lang_get_action_name($action_name, $controller_name, $language);
    }

    if ($internal_language_override || uf_application::is_language_overridden())
    {
      // add language prefix
      $new_uri = '/'.$language.$new_uri;
    }

    if (strlen($controller)) $new_uri .= '/'.$controller;
    if (!empty($action))
    {
      $new_uri .= '/'.$action;
    }
    
    if (is_array($parameters))
    while (list($key, $val) = each($parameters))
    {
      $pname = $this->controller->view_lang_get_parameter_name($key, $action_name, $controller_name, $language);
      if (!empty($pname)) $new_uri .= '/'.$pname;
      $new_uri .= '/'.$val;
    }
    
    return $new_uri;
  }

  public function lpm_req($override_parameters = NULL, $override_get_parameters = NULL, $override_language = '') {
    $request = $this->controller->request();
    $get_parameters = $request->get_get_parameters();
    if (!empty($override_get_parameters))
    {
      reset($override_get_parameters);
      while (list($key, $val) = each($override_get_parameters))
      {
        if ($val === NULL)
        {
          unset($get_parameters[$key]);
        }
        else
        {
          $get_parameters[$key] = $val;
        }
      }
    }
    reset($get_parameters);
    $combined_get = array();
    while (list($key, $val) = each($get_parameters))
    {
      array_push($combined_get, $key.'='.$val);
    }
    
    
    $uri = $this->local_parameter_merge_uri($override_parameters,$override_language);
    if (!empty($get_parameters) && count($get_parameters))
    {
      return $uri.'?'.implode('&',$combined_get);
    }
    return $uri;
  }


  public function lpm_ca_uri(
    $override_controller = NULL,
    $override_action = NULL,
    $override_parameters = NULL,
    $override_language = ''
    )
  {
    return $this->local_parameter_merge_uri(
      $override_parameters,
      $override_language,
      $override_controller,
      $override_action
    );
  }

  public function lpm_uri(
    $override_parameters = NULL,
    $override_language = ''
  )
  {
    return $this->local_parameter_merge_uri($override_parameters,$override_language,NULL,NULL);
  }
  private function local_parameter_merge_uri(
    $override_parameters = NULL,
    $override_language = '',
    $override_controller = NULL,
    $override_action = NULL
  )
  {
    
    $language = uf_application::get_language();
    $internal_language_override = 0;

    if (!empty($override_language))
    {
      if ($override_language != $language)
      {
        $language = $override_language;
        $internal_language_override = 1;
      }
    }

    $new_uri = '';

    $request = $this->controller->request();
        
    $controller = '';
    if (empty($override_controller))
    {
    $controller = $this->controller->view_lang_get_module_name($request->get_controller(),$language);
    } else
    {
      $controller = $this->controller->view_lang_get_module_name($override_controller,$language);
    }
    
    $action = '';
    if (empty($override_action))
    {
      $action = $this->controller->view_lang_get_action_name($request->get_action(), $request->get_controller(), $language);
    } else
    {
      $action = $this->controller->view_lang_get_action_name($override_action, $request->get_controller(), $language);
    }

    $parameters = $request->get_uri_parameters();

    if (!empty($override_parameters))
    {
      reset($override_parameters);
      while (list($key, $val) = each($override_parameters))
      {
        if ($val === NULL)
        {
          unset($parameters[$key]);
        }
        else
        {
          $parameters[$key] = $val;
        }
      }
    }
    
    if ($internal_language_override || uf_application::is_language_overridden())
    {
      // add language prefix
      $new_uri = '/'.$language;
    }

    $new_uri .= '/'.$controller;
    if (!empty($action))
    {
      $new_uri .= '/'.$action;
    }

    if (is_array($parameters))
    while (list($key, $val) = each($parameters))
    {
      $param_name = $this->controller->view_lang_get_parameter_name($key, $request->get_action(), $request->get_controller(), $language);
      if (!empty($param_name)) $new_uri .= '/'.$param_name;
      if ($val != '' || $val === 0) $new_uri .= '/'.$val; 
    }
    return $new_uri;

  }

  // get full path for file from the view directory
  //   these files can be overridden in the hosts directories.
  //   parameters:
  //     $controller     the internal (english) name of a valid controller
  //     $path           the relative path to the file you want to include.
  //                     Examples: 'view/sub_views/static_list.html'
  //                               'view/footer.php'
  public function get_partal_filename($controller, $path)
  {
    $controller_identifier = uf_controller::str_to_controller($controller);
    if($controller_identifier == 'base')
    {
      $file = uf_application::app_sites_host_dir().'/'.$controller_identifier.'/'.$path;      
      if (file_exists($file))
      {
        return $file;
      }
    } else
    {
      $file = uf_application::app_sites_host_dir().'/modules/'.$controller_identifier.'/'.$path;
      if (file_exists($file)) 
      {
        return $file;
      }
      else
      {
        return uf_application::app_dir().'/modules/'.$controller_identifier.'/'.$path;
      }
    }
  }

  // includes a file from the view directory
  //   these files can be overridden in the hosts directories.
  //   parameters:
  //     $controller     the internal (english) name of a valid controller
  //     $path           the relative path to the file you want to include.
  //                     Examples: 'view/sub_views/static_list.html'
  //                               'view/footer.php'
  public function include_partial($controller, $path)
  {
    $partial_file = uf_view::get_partal_filename($controller, $path);
    if(file_exists($partial_file))
    {
      return include($partial_file);
    }
  }
}

function uf_include_view($uf_controller,$view,$data = NULL)
{
  // This function is used to create a clean symbol table
  extract(get_object_vars($uf_controller));
  extract(array('uf_dir_web_lib' => uf_baker::view_get_baked_dir().'/lib'));

  // init view class
  $uf_view = new uf_view();
  $uf_view->set_controller($uf_controller);

  $uf_request  = $uf_controller->request();
  $uf_response = $uf_controller->response();
  
  require($view);
}

function uf_include_language($uf_controller,$language_file)
{
  // This function is used to create a clean symbol table
  $language =& $uf_controller->language;
  if(file_exists($language_file))
  {
    include_once($language_file);    
  }
}

# register our controller factory
spl_autoload_register('uf_controller::autoload_controller');

// tempory hack for error output
register_shutdown_function('handleShutdown');
function handleShutdown() {
  $error = error_get_last();
  if($error !== NULL){
    @undefined_dummy_function();
  }
}

/* EOF */
