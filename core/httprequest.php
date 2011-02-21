<?
class uf_http_request extends uf_request {
  private $_segments;

  public function __construct() {
    $uri = $_SERVER['REQUEST_URI'];
    $pos = strpos($uri, '?');
    if($pos !== FALSE) {
      $uri = substr($uri, 0, $pos);
    }
    $this->_segments = explode('/', $uri);
    array_shift($this->_segments);
    $input = array_merge($_GET, $_POST);
    $this->parameters($input);
  }

  public function controller() {
    return isset($this->_segments[0]) && !empty($this->_segments[0]) ? $this->_segments[0] : parent::controller();
  }

  public function action() {
    return isset($this->_segments[1]) ? $this->_segments[1] : parent::action();
  }
}