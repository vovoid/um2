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

require_once(UF_BASE.'/core/plugin.php');
require_once(UF_BASE.'/core/plugins/array_translate_plugin.php');

class uf_application
{  
  private static $_is_initialized;
  private static $_app_sites_host_dir;
  private static $_config;
  private static $_language_overridden;
  private static $_request;
  private static $_response;

  public static function init_config()
  {
    // LOAD CONFIG FILE
    require(UF_BASE.'/config/config.php');
    self::$_config =& $uf_config;

    $n = preg_replace('/www[0-9]*\./','',$_SERVER['SERVER_NAME']);
    $dirb = self::app_dir(FALSE).'/sites/hosts/';
    if (!is_dir(UF_BASE.$dirb.$n))
    {
      self::$_app_sites_host_dir = $dirb.'FALLBACK';
    }
    else
      self::$_app_sites_host_dir = $dirb.$n;

    // look for site-specific override configuration
    if (is_file(UF_BASE.self::$_app_sites_host_dir.'/config.php'))
    {
      include_once(UF_BASE.self::$_app_sites_host_dir.'/config.php');
    }
  }
  
  public static function init()
  {
    if(!isset(self::$_is_initialized))
    {
      self::$_is_initialized = TRUE;

      self::init_config();
      
      if(uf_application::get_config('load_propel'))
      {
        // Initialize Propel with the runtime configuration
        require_once UF_BASE.'/propel/propel-1.5.6/runtime/lib/logger/BasicLogger.php';
        require_once UF_BASE.'/core/propel_logger.php';
        $logger = new MyLogger();
        require_once UF_BASE.'/propel/propel-1.5.6/runtime/lib/Propel.php';
        Propel::setLogger($logger);

        $propel_initial_conf = include(uf_application::propel_app_dir().'/data/build/conf/umvc-conf.php');
        $propel_initial_conf['datasources']['umvc']['connection'] = self::get_config('propel_db');
        Propel::setConfiguration($propel_initial_conf);
        Propel::initialize();
        
        // Add the generated 'classes' directory to the include path
        set_include_path(uf_application::propel_app_dir().'/data/build/classes'.PATH_SEPARATOR.get_include_path());
      }
    }
  }
  
  public static function run()
  {
    self::init();
    
    // PRE ROUTING
    self::$_request  = new uf_http_request;
    self::$_response = new uf_response;

    /// CREATE AND EXECUTE CONTROLLER
    uf_controller::execute_base(self::$_request,self::$_response,array('enable_buffering' => TRUE));

    self::$_response = NULL;
    self::$_request  = NULL;
  }

  public static function clear_log()
  {
    if(self::get_config('log'))
    {
      file_put_contents(UF_BASE.'/log/log.txt', '');
    }
  }

  public static function log($message)
  {
    if(self::get_config('log'))
    {
      date_default_timezone_set('UTC'); //temp hack
      $fp = fopen(UF_BASE.'/log/log.txt', 'a');
      fwrite($fp, date('Y-m-d h:i:s').' '.$message."\n");
      fclose($fp);
    }
  }

  public static function get_request()
  {
    return self::$_request;
  }

  public static function get_response()
  {
    return self::$_response;
  }


  public static function get_language()
  {
    return self::get_config('language','en-us');
  }

  public static function set_language($new_language)
  {
    // either a language prefix or routing can 
    if (empty(self::$_language_overridden))
    {
      if (self::get_config('language','') != $new_language)
      {
        self::set_language_overridden();
      }
    }
    return self::set_config('language',$new_language);
  }

  public static function set_language_overridden()
  {
    // denote that the language has been overridden by a URI prefix
    self::$_language_overridden = 1;
  }

  public static function is_language_overridden()
  {
    return self::$_language_overridden;
  }
  
  public static function app_dir($return_full_path = TRUE)
  {
    return ($return_full_path ? UF_BASE : '').self::get_config('app_dir');
  }

  public static function app_name()
  {
    return self::get_config('app_dir');
  }

  public static function propel_app_dir($return_full_path = TRUE)
  {
    return  ($return_full_path ? UF_BASE : '').self::get_config('propel_app_dir');
  }

  public static function app_sites_host_dir($return_full_path = TRUE)
  {
    return ($return_full_path ? UF_BASE : '').self::$_app_sites_host_dir;
  }
  
  public static function get_app_sites_host_dir($return_full_path = TRUE)
  {
    return self::app_sites_host_dir($return_full_path);
  }

  public static function host()
  {
    return substr(strrchr(self::$_app_sites_host_dir, '/hosts/'),1);
  }
  
  public static function get_config($name,$default_value = '')
  {
    return isset(self::$_config[$name]) ? self::$_config[$name] : $default_value;
  }

  public static function set_config($name,$value = '')
  {
    self::$_config[$name] = $value;
  }
  
    /**
     * Loads lib or its part
     * 
     * @param string $lib_name Name of the lib
     * @param string $lib_file Name of the particular lib file
     */
    public static function load_lib($lib_name, $lib_file = null) {
      $lib_name = uf_controller::str_to_controller($lib_name);

      $lib_dir = uf_application::app_dir() . '/lib/'.$lib_name;
      if (!is_dir($lib_dir)) {
        trigger_error('Missing lib: ' . $lib_name, E_USER_ERROR);
      }

      if ($lib_file) {
        $lib_script_file = $lib_dir.'/'.$lib_file.'.php';
        if (!is_file($lib_script_file)) {
          trigger_error("Lib $lib_name found, but doesn't contain $lib_file", E_USER_ERROR);
        }
        require_once $lib_script_file;
        return;
      }

      $d = opendir($lib_dir);
      while ($file = readdir($d)) {
        if (is_file($lib_dir.'/'.$file) && strpos($file, '.php') !== FALSE) {
          require_once $lib_dir.'/'.$file;
        }
      }
    }

}

/* EOF */
