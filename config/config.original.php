<?

$uf_config = array(
  'log'            => FALSE,
  'languages'      => array('en-us','sv-se'),
  'language'       => 'en-us',
  'always_bake'    => TRUE,
  'load_propel'    => TRUE,
  'app_dir'        => '/app_demo',
  'load_plugins'   => array(),
  'propel_app_dir' => '/app_demo',
  'propel_db'      => array(
                        'dsn' => 'pgsql:host=10.1.0.100;dbname=umvc',
                        'user' => 'postgres',
                        'password' => ''
                      )
);

?>