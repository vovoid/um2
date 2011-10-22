<?

class bake_css extends uf_baker
{
  public function process_file_name($full_path_to_file, $file_name_component, &$res_array)
  {
    if ( substr($file_name_component,0,2) == 'b_' )
    {
      $ext = substr(strrchr($file_name_component,'.'),1);
      $dest = 'static';
      if ($ext == 'php') {
        $dest = 'dynamic';
        $ext = substr(strrchr(substr($file_name_component,0,strpos($file_name_component,'.php')),'.'),1);
      }
      error_log($ext.$dest);
      if (trim($ext) == 'css')
      {
        $res_array[$dest]['css'][] = $full_path_to_file;
        return TRUE;
      }
    }
  }

  public function bake__($files, $prefix = '')
  {
    $output = '';
    error_log('oink');
    if(is_array($files))
    {
      foreach($files as $file)
      {
        $data = file_get_contents(UF_BASE.$file);
        $data = str_replace('[uf_module]', uf_baker::view_get_baked_modules_dir(), $data);
        $data = str_replace('[uf_lib]', uf_baker::view_get_baked_dir().'/lib', $data);
        $output .= $data."\n";
      }
    }
    return $output;
  }
  
}

// factory
return new bake_css;