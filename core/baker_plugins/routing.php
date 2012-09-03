<?

include_once(UF_BAKER_PLUGIN_BASE.'/interface/uf_baker_plugin.php');

class bake_routing extends uf_baker_plugin
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
    if ( substr($file_name_component,0,2) == 'r_' )
    {
      // add to destination array
      $res_array['dynamic']['routing'][] = $full_path_to_file;
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
  public function bake__pre( $dest )
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
  public function bake__(array $files, $prefix = '')
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
return new bake_routing;