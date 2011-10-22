<?

class bake_images extends uf_baker
{
  public function process_file_name($full_path_to_file, $file_name_component, &$res_array)
  {
    $ext = substr(strrchr($file_name_component,'.'),1);
    if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
    {
      error_log(' adding_to_array');
      $res_array['static']['images'][] = $full_path_to_file;
    }
  }

  public function bake__($files, $prefix = '')
  {
    sort($files, SORT_STRING);
    foreach($files as $source_file)
    {
      $bake_base = UF_BASE.'/web/data';
      $host = uf_application::host();
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
  
}

// factory
return new bake_images;