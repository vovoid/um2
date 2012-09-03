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

class bake_js extends uf_baker_plugin
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
    if ( substr($file_name_component,0,2) == 'b_' )
    {
      $ext = substr(strrchr($file_name_component,'.'),1);
      $dest = 'static';
      if ($ext == 'php') {
        $dest = 'dynamic';
        $ext = 
          substr(
            strrchr(
              substr(
                $file_name_component,
                0,
                strpos($file_name_component,'.php')
              )
              ,
              '.'
            ),
            1
          )
        ;
      }
      
      if (trim($ext) == 'js')
      {
        // add to destination array
        $res_array[$dest]['js'][] = $full_path_to_file;
        return TRUE;
      }
    }
    return FALSE;
  }
  
  /**
   * Precedent step of the baking process - if you need to add a scaffold or similar
   * 
   * @param string $dest String containing either 'static' or 'dynamic'
   *             depending on destination of the baking run.
   */
  public function bake__pre( $dest )
  {
    if ( $dest == 'static' )
    {
      return file_get_contents( UF_CORE.'/umvc.js' );
    }
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
  public function bake__(array $files, $prefix = '')
  {
    $output = '';
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $data = file_get_contents(UF_BASE.$file);
        $data = str_replace('[uf_module]', uf_baker::view_get_baked_modules_dir(), $data);
        $data = str_replace('[uf_base]', uf_baker::view_get_baked_base_dir(), $data);
        $data = str_replace('[uf_lib]', uf_baker::view_get_baked_dir().'/lib', $data);
        $output .= $data."\n";
      }
    }
    return $output;
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
    if (! (isset($this->config['minify']) && $this->config['minify'] === TRUE) )
    return;

    // include minifier class
    include_once( UF_BAKER_PLUGIN_BASE.'/lib/js.php');
    $baked_result = JSMin::minify($baked_result);
  }
}

// factory
return new bake_js;