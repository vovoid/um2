<?
class language_controller extends base_controller
{
  public function index()
  {
    $this->mainmenu = 'language';    
  }

  public function set()
  {
    uf_session::set('language',$this->request()->parameter('language','en_US'));
    $this->response()->redirect('/');
    return FALSE;
  }

}
?>