<?
/**
* Project: µm2 model2 framework
*
* @author David Brännvall, Jonatan 'jaw' Wallmander.
*        Copyright 2011 HR North Sweden AB http://hrnorth.se
*        Copyright 2012 Vovoid Media Technologies AB http://vovoid.com/um2
* @see The GNU Public License (GPL)
*
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


require_once(UF_CORE.'/umvc.php');

define('UF_BAKER_PLUGIN_BASE',realpath(dirname(__FILE__).'/baker_plugins'));

/// TODO: write abstract interface


class uf_baker
{
  private static $_files;
  private static $_plugins;
 
 
  private static function _load_plugin( $type, $config )
  {
    $plugin_filename = UF_BAKER_PLUGIN_BASE.'/'.$type.'.php';
    
    if ( is_file($plugin_filename) )
    {
      error_log('loading plugin '.$type.'...');
      self::$_plugins[$type] = include($plugin_filename);
      self::$_plugins[$type]->set_config( $config );
    }
  }
 
  static function _sort_files($a,$b)
  {
    $a = strrchr($a,'/');
    $ap = strpos($a,'_');
    $cp = strpos($a,'_',$ap + 1);
    if($cp === FALSE) $cp = strpos($a,'.',$ap + 1);
    $c = substr($a,$ap + 1,$cp - $ap - 1);

    $b = strrchr($b,'/');
    $bp = strpos($b,'_');
    $dp = strpos($b,'_',$bp + 1);
    if($dp === FALSE) $dp = strpos($b,'.',$bp + 1);
    $d = substr($b,$bp + 1,$dp - $bp - 1);

    if(is_int($c) || is_int($d))
    {
      return strrchr($a,'/') >= strrchr($b,'/');
    }
    else
    {
      return $c >= $d;
    }
  }
  
  private static function _delete_directry_content($dir)
  {
    $files = scandir($dir);
    foreach($files as $file)
    {
      if(strpos($file, '.') === 0) continue;
      
      $current = $dir.'/'.$file;

      if(is_dir($current))
      {
        uf_baker::_delete_directry_content($dir.'/'.$file);
        rmdir($current);
      }

      if(is_file($current))
      {
        unlink($current);
      }
    }
  }

  private static function _scan_dir_recursive($dir)
  {
    $scan_result = scandir(UF_BASE.$dir);
    array_splice( $scan_result,0,2 ); // remove . and ..
    $out = array();
    foreach( $scan_result as $f )
    {
      $fp = $dir.'/'.$f;
      if(is_dir(UF_BASE.$fp))
      {
        $sub = self::_scan_dir_recursive($fp);
        $out = array_merge_recursive($out,$sub);
      } 
      else
      {
        if ( !is_array(self::$_plugins) )
        {
          $trace = debug_backtrace();
          error_log('plugins is not array');
          error_log($trace[3]['function'] . '    '. $trace[3]['file']);
        }
        $plugin_processed = FALSE;
        foreach (self::$_plugins as $plugin)
        {
          if ($plugin->process_file_name($fp, $f, $out))
          {
            $plugin_processed = TRUE;
          }
        }
        if (!$plugin_processed)
        {
          $ext = substr(strrchr($f,'.'),1);

          $is_recursive = substr($f,0,2) == 'b_';
          $is_route     = substr($f,0,2) == 'r_';
          $is_language  = in_array($ext, array('lang'));

          if($is_language)
          {
            $out['dynamic']['language'][] = $fp;
          }
          else if($is_recursive || $is_route)
          {
            // Get the right ext for php files (routing files excluded)
            $is_dynamic = $ext == 'php';
            $dest = 'static';
            if($is_dynamic)
            {
              $dest = 'dynamic';
              // skip last .php and extract file type, ie file.js.php will return js
              $ext = substr(strrchr(substr($f,0,strpos($f,'.php')),'.'),1);
            }

            $out[ $dest ][$is_route ? 'routing' : $ext][] = $fp;
          }
        }
      }
    }
    return $out;
  }
  
  private static function _scan_dir()
  {
    if(!is_array(self::$_files))
    {
      $lib = self::_scan_dir_recursive(uf_application::app_dir(FALSE).'/lib');
      $modules = self::_scan_dir_recursive(uf_application::app_dir(FALSE).'/modules');
      $errors = self::_scan_dir_recursive(uf_application::app_dir(FALSE).'/errors');
      $hosts   = self::_scan_dir_recursive(uf_application::app_sites_host_dir(FALSE));
      self::$_files = array_merge_recursive($lib,$modules,$hosts,$errors);

      if(isset(self::$_files['static']))
      {
        foreach(self::$_files['static'] as &$type)
        {
          usort($type,array('uf_baker','_sort_files'));
        }
      }
      if(isset(self::$_files['dynamic']))
      {
        foreach(self::$_files['dynamic'] as &$type)
        {
          usort($type,array('uf_baker','_sort_files'));
        }
      }
    }
  } 

  public static function bake($type)
  {
    $prefix = '';
    // split type, for instance: pre_routing
    $info = explode('_',$type);
    if(count($info) > 1)
    {
      $prefix = $info[0]; // pre
      $type = $info[1]; // routing
    }
    
    // find/scan all the files in the project
    self::_scan_dir();

    for($i = 0; $i < 2; $i++)
    {
      $place = $i == 0 ? 'static' : 'dynamic';
      $output = '';
      if(isset(self::$_files[$place][$type]))
      {
        switch($type)
        {
          default:
            if ( isset(self::$_plugins[$type]) )
            {
              $output .= self::$_plugins[$type]->bake__pre( $place );
              $output .= self::$_plugins[$type]->bake__( self::$_files[$place][$type], $prefix );
              self::$_plugins[$type]->bake__post( $prefix, $output );
            } 
        }      
      }
      $dir = '';
      if ($place == 'dynamic')
      {
        $dir = self::get_baked_cache_dir();
      } else
      {
        $dir = self::get_baked_static_dir();
      }
      $dir .= '/'.$type;

      if(!is_dir($dir))
      {
        // make dir recursively
        mkdir($dir,0777,TRUE);
      }

      if($output != '')
      {
        file_put_contents($dir.'/baked.'.($prefix!='' ? $prefix.'.' : '').$type.($place == 'dynamic' ? '.php' : ''),$output);
      }
    }
  }
  
  
  public static function load_plugins()
  {
    if (!is_array(self::$_plugins))
    {
      self::$_plugins = array();
      $plugin_config = uf_application::get_config('baker');
      while ( list( $plugin_name, $config) = each($plugin_config) )
      {
        self::_load_plugin( $plugin_name, $config );
      }
    }
  }

  public static function bake_all()
  {
    self::_delete_directry_content(self::get_baked_cache_dir());
    self::_delete_directry_content(self::get_baked_static_dir());
    self::load_plugins();
    
    error_log('plugins loaded: '.count(self::$_plugins));
    $plugins = self::$_plugins;
    reset($plugins);
    
    while (list($type, $object) = each($plugins) )
    {
      error_log('baking by plugin: '.$type);
      self::bake($type);
    }
  }
  

  // ************************************************
  // PATHS RELATIVE TO THE SYSTEM ROOT FOR USE IN PHP
  // ************************************************
  // get the current cache dir
  public static function get_baked_cache_dir()
  {
    return UF_BASE.'/cache/baker/'.uf_application::host().uf_application::app_name();
  }

  // get the current static dir
  public static function get_baked_static_dir()
  {
    return UF_BASE.'/web/data/baker/'.uf_application::host().uf_application::app_name();
  }

  // **********************************************
  // PATHS RELATIVE TO THE WEB ROOT FOR USE IN HTML
  // **********************************************

  // get the baked dir for views - images etc
  public static function view_get_baked_dir()
  {
    return '/data/baker/'.uf_application::host().uf_application::app_name();
  }
  public static function view_get_baked_base_dir()
  {
    return '/data/baker/'.uf_application::host().''.uf_application::app_name().'/base';
  }
  // get the baked modules dir for views - images etc
  public static function view_get_baked_modules_dir()
  {
    return '/data/baker/'.uf_application::host().''.uf_application::app_name().'/modules';
  }

  
}

?>
