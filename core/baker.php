<?
/**
* Project: µm2 model2 framework
*
* @author David Brännvall, Jonatan 'jaw' Wallmander.
*        Copyright 2011 HR North Sweden AB http://hrnorth.se
*        Copyright 2011 Vovoid Media Technologies http://vovoid.com/um2
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

class uf_baker
{
  private static $_files;
  private static $_plugins;

  static function _load_plugin($type)
  {
    $plugin_filename = UF_CORE.'/baker_plugins/'.$type.'.php';
    
    if ( is_file($plugin_filename) )
    {
      error_log('loading plugin '.$type.'...');
      self::$_plugins[$type] =  include($plugin_filename);
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
    self::load_plugins();
    $a = scandir(UF_BASE.$dir);
    array_splice($a,0,2);
    $out = array();
    foreach($a as $f)
    {
      $fp = $dir.'/'.$f;
      if(is_dir(UF_BASE.$fp))
      {
        $sub = self::_scan_dir_recursive($fp);
        $out = array_merge_recursive($out,$sub);
      } 
      else
      {
        if (is_array(self::$_plugins)) error_log('plugins is array');
        else {
          $trace = debug_backtrace();
          error_log('plugins is not array');
          error_log($trace[3]['function'] . '    '. $trace[3]['file']);
        }
        foreach (self::$_plugins as $plugin)
        {
          $plugin->process_file_name($fp, $f, $out);
        }
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
    return $out;
  }
  
  private static function _scan_dir()
  {
    if(!is_array(self::$_files))
    {
      $lib = self::_scan_dir_recursive(uf_application::app_dir(FALSE).'/lib');
      $modules = self::_scan_dir_recursive(uf_application::app_dir(FALSE).'/modules');
      $hosts   = self::_scan_dir_recursive(uf_application::app_sites_host_dir(FALSE));
      self::$_files = array_merge_recursive($lib,$modules,$hosts);

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

  private static function _bake_routing($files,$prefix='')
  {
    $output = '';
    $prefix2 = $prefix.($prefix != '' ? '_' : '');
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $f = substr(strrchr($file,'/'),1);
        if($prefix != '')
        {
          // Only prefixed files
          if(strpos($f, 'routing_'.$prefix.'_') === 0)
          {
            $data = file_get_contents($file);
            $output .= trim($data);                      
          }
        }
        else
        {
          // Only unprefixed files
          if(strpos($f,'routing_pre_') !== 0 && strpos($f,'routing_post_') !== 0)
          {
            $data = file_get_contents(UF_BASE.$file);
            $output .= trim($data);
          }
        }
      }
    }
    return $output;
  }

  private static function _bake_language($files)
  {
    if (!isset($files))
    {
      return NULL;
    }

    $output = '<?php' . "\n";
    $output .= 'return array('. "\n";
    $bake_output_directory = self::get_baked_cache_dir().'/'.uf_application::host().'/language';
    
    foreach ($files as $file)
    {
      $strings = parse_ini_file(UF_BASE.$file, TRUE);

      if (!isset($strings['locale']))
      {
        // TODO: Alert here, locale must be set in translation files.
        // If not locale is set in translation file, continue.
        continue;
      }

      $locale = $strings['locale']; unset($strings['locale']);
      foreach ($strings as $namespace => $sections)
      {
        foreach ($sections as $skey => $section)
        {
          $output .= "'".addslashes($namespace.'.'.$locale.'.'.$skey)."' => '".addslashes($section)."',". "\n";
        }
      }
    }
    $output .= ');' . "\n\n";
    $output .= '?>';
    
    return $output;
  }

  private static function _bake_js($files)
  {
    $output = '';
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $data = file_get_contents(UF_BASE.$file);
        $data = str_replace('[uf_module]', self::view_get_baked_modules_dir(), $data);
        $data = str_replace('[uf_lib]', self::view_get_baked_dir().'/lib', $data);
        $output .= $data."\n";
      }
    }
    return $output;
  }

  private static function _bake_css($files)
  {
    $output = '';
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $data = file_get_contents(UF_BASE.$file);
        $data = str_replace('[uf_module]', self::view_get_baked_modules_dir(), $data);
        $data = str_replace('[uf_lib]', self::view_get_baked_dir().'/lib', $data);
        $output .= $data."\n";
      }
    }
    return $output;
  }
  
  public static function bake($type)
  {
    $info = explode('_',$type);
    if(count($info) > 1)
    {
      $prefix = $info[0];
      $type = $info[1];
    }
    else
    {
      $prefix = '';
      $type = $info[0];
    }
    
    self::_scan_dir();

    for($i = 0; $i < 2; $i++)
    {
      $place = $i == 0 ? 'static' : 'dynamic';
      $output = '';
      if(isset(self::$_files[$place][$type]))
      {
        switch($type)
        {
          case 'routing':
            $output .= self::_bake_routing(self::$_files[$place][$type],$prefix);
            break;
          case 'js':
            if($place == 'static') 
            {
              $output .= file_get_contents(UF_BASE.'/core/umvc.js');
            }
            $output .= self::_bake_js(self::$_files[$place][$type]);
            break;
          case 'language':
            $output .= self::_bake_language(self::$_files[$place][$type]);
            break;
          case 'css':
            $output .= self::_bake_css(self::$_files[$place][$type]);
            break;
          default:
            if ( isset(self::$_plugins[$type]) )
            {
              $output .= self::$_plugins[$type]->bake__( self::$_files[$place][$type], $prefix );
            } else
            {
              error_log('no baker plugin available for '.$type);
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
  // get the baked modules dir for views - images etc
  public static function view_get_baked_modules_dir()
  {
    return '/data/baker/'.uf_application::host().''.uf_application::app_name().'/modules';
  }

  public static function load_plugins()
  {
    if (!is_array(self::$_plugins))
    {
      self::$_plugins = array();
      self::_load_plugin('images');
      /// TODO: load from config instead.
    }
  }

  public static function bake_all()
  {
    ///error_log(self::get_baked_cache_dir());
    ///error_log(self::get_baked_static_dir());
    self::_delete_directry_content(self::get_baked_cache_dir());
    self::_delete_directry_content(self::get_baked_static_dir());
    error_log('aaaaaaaaaaaaa');
    self::bake('images'); // has
    self::bake('js'); // has
    self::bake('css'); 
    self::bake('language'); // has
    self::bake('pre_routing'); // has
    self::bake('routing');  // has
    self::bake('post_routing'); // has
    error_log('bbbbbbbbbbbbb');
  }
}

?>
