<?

$uf_config = array(
  'log'            => FALSE,
  'languages'      => array('en-us','sv-se'),
  'language'       => 'en-us',
  'load_propel'    => TRUE,
  'app_dir'        => '/app_demo',
  'load_plugins'   => array(),
  'propel_app_dir' => '/app_demo',
  'propel_db'      => array(
                        'dsn' => 'pgsql:host=10.1.0.100;dbname=umvc',
                        'user' => 'postgres',
                        'password' => ''
                      )
  // if you don't want to load a plugin, just uncomment it/remove it
  // from the array, likewise, if you add your own, just add it here
  'baker' => array(
              'css' => array(
                        'minify' => TRUE
                      ),
              'images' => array(
                      ),
              'js' => array(
                        'minify' => TRUE
                      ),
              'routing' => array(
                      ),
              'language' => array(
                      )
             )                              
);

?>