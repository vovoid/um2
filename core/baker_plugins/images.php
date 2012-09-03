<?

/**
* Project: µm2 model2 framework
*
* @author David Brännvall, Jonatan 'jaw' Wallmander.
*        Copyright 2011-2012 HR North Sweden AB http://hrnorth.se
*        Copyright 2011-2012 Vovoid Media Technologies AB http://vovoid.com/um2
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


include_once(UF_BAKER_PLUGIN_BASE.'/interface/uf_baker_plugin.php');

class bake_images extends uf_baker_plugin
{
  /**
   * Looks at a filename to determine wether or not it's this plugin's responsibility
   *
   * This will operate on the full_path_to_file to identify - either via opening
   * the file or (preferred) by looking at the filename.
   * 
   * 
   * @param string  $full_path_to_file
   * @param string  $file_name_component
   * @param array   $res_array - the full path of the file gets append to this if it matches
   */
  public function process_file_name($full_path_to_file, $file_name_component, &$res_array)
  {
    $ext = substr(strrchr($file_name_component,'.'),1);
    if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
    {
      $res_array['static']['images'][] = $full_path_to_file;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Precedent step of the baking process - if you need to add a scaffold or similar
   * 
   * @param string $dest String containing either 'static' or 'dynamic'
   *             depending on destination of the baking run.
   */
  public function bake__pre($dest)
  {
  }
  
  /**
   * Bakes the contents of the files
   *
   * Actual baking - this will be run twice, once for the static and once for the
   * dynamic output. Dynamic output is anything that will be processed by PHP
   * before leaving the system.
   * 
   * @param array $files Array containing the full path names of each file that 
   *              are to be baked. 
   * @param string $prefix 
   * 
   * @return string Returns the concatenated string (if applicable)
   */
  public function bake__($files, $prefix = '')
  {
    // destination directory
    $bake_base = UF_BASE.'/web/data';
    $host = uf_application::host();

    sort($files, SORT_STRING);
    foreach($files as $source_file)
    {
      $file = substr(strrchr($source_file, '/'), 1);
      $file = $source_file;
      $mp = strpos($source_file, '/modules/');

      $dir =
        $mp !== FALSE
            ? uf_application::get_config('app_dir').substr($source_file, $mp)
          : $dir = $source_file;

      $mpb = strpos($source_file, '/base/');
      if ($mpb !== FALSE)
      {
        // deal with files present in the "base" directory
        $file = substr($dir, strrpos($source_file,'/') + 1);
        if ($file[0] == '/') $file = substr($file,1);
        //echo 'file is: '.$file.'<br />';

        $dir_t = substr($source_file,$mpb);
        $dir_t = substr($dir_t, 0, strrpos($dir_t,'/'));
        //echo 'dir_t: &nbsp; '.$dir_t."<br />";
        //echo 'dir_t: &nbsp; '.substr($dir_t, 0, strrpos($dir_t,'/'))."<br />";

        $dir = uf_baker::get_baked_static_dir().$dir_t;
      } else
      {
        $file = substr($dir, strrpos($dir,'/') + 1);
        //echo 'file is: '.$file.'<br />';
        $dir = $bake_base.'/baker/'.$host.substr($dir, 0, strrpos($dir,'/'));
      }

      // check if destination dir exists and create if needed
      if(!is_dir($dir))
      {
        mkdir($dir, 0777, TRUE);
      }
      /*
       * Debug output:
      echo 'dir is: '.$dir.'<br /><br />';
      echo 'host is:'.$host.'<br />';
      echo 'bake base is: '.$bake_base.'<br /><br />';
      echo 'uf base is: '.UF_BASE.'<br /><br />';
      echo 'copy from: '.UF_BASE.$source_file."<br />";
      echo 'copy to: '.$dir.'/'.$file."<br/><br/><br />";
      echo '**********************************<br />';*/
      copy(UF_BASE.$source_file, $dir.'/'.$file);

    }
    //die();
  }

  /**
   * Post step of the baking process - if you need to process the output in some way.
   * 
   * @param string $dest String containing either 'static' or 'dynamic'
   *               depending on destination of the baking run.
   * @param array $baked_result String containing the contents of the baking
   *              depending on destination of the baking run.
   */
  public function bake__post($dest, &$baked_result)
  {
  }
}

// factory
return new bake_images;