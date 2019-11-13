<?php
/**
 * Website: http://sourceforge.net/projects/advancedhtmldom/
 * Description: A drop-in replacement for simple html dom
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author P Guardiario <pguardiario@gmail.com>
 * @version 0.0.11
 */

if(!class_exists('PGBrowser')){

$attributes = array('href', 'src', 'id', 'class', 'name', 'text', 'height', 'width', 'content', 'value', 'title', 'alt');

$tags = array('a', 'abbr', 'address', 'area', 'article', 'aside', 'audio', 'b', 'base', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'data', 'datalist', 'dd', 'detail', 'dialog', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'font', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i', 'iframe', 'img', 'image', 'input', 'label', 'legend', 'li', 'map', 'mark', 'menu', 'meta', 'nav', 'noscript', 'object', 'ol', 'optgroup', 'option', 'p', 'param', 'pre', 'script', 'section', 'select', 'small', 'source', 'span', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'track', 'u', 'ul', 'var', 'video');

define('TAG_REGEX', "/^(" . implode('|', $tags) . ")$/");
define('TAGS_REGEX', "/^(" . implode('|', $tags) . ")e?s$/");

define('ATTRIBUTE_REGEX', "/^(" . implode('|', $attributes) . "|data-[\w\-]+)$/");
define('ATTRIBUTES_REGEX', "/^(" . implode('|', $attributes) . "|data-[\w\-]+)e?s$/");

class AdvancedHtmlBase{
  public $doc;
  public $dom;
  public $node;
  public $is_text = false;

  public function text(){ return $this->node->nodeValue; }
  public function html(){ return $this->doc->dom->saveHTML($this->node); }
  public function __toString(){ return $this->html(); }

  public function remove(){
    $this->node->parentNode->removeChild($this->node);
    return $this;
  }

  public function str(){ return new Str($this->text); }
  public function match($re){
    $str = new Str($this->text);
    return $str->match($re);
  }
  public function scan($re){
    $str = new Str($this->text);
    return $str->scan($re);
  }
  public function clean($str){ return trim(preg_replace('/\s+/', ' ', $str)); }
  public function trim($str){ return trim($str); }

  public function find($css, $index = null){
    $xpath = CSS::xpath_for($css);
    if(!isset($this->doc) || !isset($this->doc->xpath)) return null;
    if(null === $index){
      return new AHTMLNodeList($this->doc->xpath->query($xpath, $this->node), $this->doc);
    } else {
      $nl = $this->doc->xpath->query($xpath, $this->node);
      if($index < 0) $index = $nl->length + $index;
      $node = $nl->item($index);
      return $node ? new AHTMLNode($node, $this->doc) : null;
    }
  }

  // magic methods
  public function __call($key, $args){
    $key = strtolower(str_replace('_', '', $key));
    switch($key){
      case 'innertext': return ($this->is_text || !$this->children->length) ? $this->text() : $this->find('./text()|./*')->outertext ;
      case 'plaintext': return $this->text();
      case 'outertext':
      case 'html':
      case 'save':
         return $this->html();
      case 'innerhtml':
        $ret = '';
        foreach($this->node->childNodes as $child) $ret .= $this->doc->dom->saveHTML($child);
        return $ret;

      case 'tag':
        return $this->node->nodeName;
      case 'next': return $this->at('./following-sibling::*[1]|./following-sibling::text()[1]|./following-sibling::comment()[1]');

      case 'index': return $this->search('./preceding-sibling::*')->length + 1;

      /*
      DOMNode::insertBefore — Adds a new child
      */

      // simple-html-dom junk methods
      case 'clear':
        return;

      // search functions
      case 'at':
      case 'getelementbytagname':
        return $this->find($args[0], 0);

      case 'search':
      case 'getelementsbytagname':
        return isset($args[1]) ? $this->find($args[0], $args[1]) : $this->find($args[0]);

      case 'getelementbyid': return $this->find('#' . $args[0], 0);
      case 'getelementsbyid': return isset($args[1]) ? $this->find('#' . $args[0], $args[1]) : $this->find('#' . $args[0]);

      // attributes
      case 'hasattribute': return !$this->is_text && $this->node->hasAttribute($args[0]);
      case 'getattribute': $arg = $args[0]; return $this->$arg;
      case 'setattribute': $arg0 = $args[0]; $arg1 = $args[1]; return $this->$arg0 = $arg1;
      case 'removeattribute': $arg = $args[0]; return $this->$arg = null;
      case 'getattribute': return $this->node->getAttribute($args[0]);
      case 'setattribute': return $this->$args[0] = $args[1];
      case 'removeattribute': return $this->$args[0] = null;


      // wrap
      case 'wrap':
        return $this->replace('<' . $args[0] . '>' . $this . '</' . $args[0] . '>');
      case 'unwrap':
        return $this->parent->replace($this);

      case 'str':
        return new Str($this->text);

      // heirarchy
      case 'firstchild': return $this->at('> *');
      case 'lastchild': return $this->at('> *:last');
      case 'nextsibling': return $this->at('+ *');
      case 'prevsibling': return $this->at('./preceding-sibling::*[1]');
      case 'parent': return $this->at('./..');
      case 'children':
      case 'childnodes':
        $nl = $this->search('./*');
        return isset($args[0]) ? $nl[$args[0]] : $nl;

      
      case 'child': // including text/comment nodes
        $nl = $this->search('./*|./text()|./comment()');
        return isset($args[0]) ? $nl[$args[0]] : $nl;

    }

    // $doc->spans[x]
    if(preg_match(TAGS_REGEX, $key, $m)) return $this->find($m[1]);
    if(preg_match(TAG_REGEX, $key, $m)) return $this->find($m[1], 0);

    if(preg_match('/(clean|trim|str)(.*)/', $key, $m) && isset($m[2])){
      $arg1 = $m[1];
      $arg2 = $m[2];
      return $this->$arg1($this->$arg2);
    }
	  if (in_array($key,array('dom','node','doc'))) return;
    if(!preg_match(ATTRIBUTE_REGEX, $key, $m)) trigger_error('Unknown method or property: ' . $key, E_USER_WARNING);
    if(!$this->node || $this->is_text) return null;
    return $this->node->getAttribute($key);
  }

  public function __get($key){
    return $this->$key();
  }
  
  public function destruct()
  {
	 $this->doc = NULL;
	 $this->node = NULL;
	 $this->dom = NULL;

	 unset($this->node);
	 unset($this->dom);
	 unset($this->doc);

  }
  
}

class AdvancedHtmlDom extends AdvancedHtmlBase{
  public $xpath;
  public $root;

  function __construct($html = null, $is_xml = false){
    $this->doc = $this;
    if($html) $this->load($html, $is_xml);
  }

  public function load($html, $is_xml = false){
    $this->dom = new DOMDocument();
    if($is_xml){
      @$this->dom->loadXML(preg_replace('/xmlns=".*?"/ ', '', $html));
    } else {
      @$this->dom->loadHTML($html);
    }
    $this->xpath = new DOMXPath($this->dom);
    //$this->root = new AHTMLNode($this->dom->documentElement, $this->doc);
    $this->root = $this->at('body');
  }
  public function load_file($file, $is_xml = false){ $this->load(file_get_contents($file), $is_xml); }

  // special cases
  public function text(){ return $this->root->text; }
  public function title(){ return $this->at('title')->text(); }
  
  public function destruct()
  {
		$this->xpath = NULL;
	  $this->root = NULL;
	  $this->dom = NULL;
	  $this->doc = NULL;

	  unset($this->xpath);
	  unset($this->root);
	  unset($this->dom);
	  unset($this->doc);

	}

}

class AHTMLNodeList implements Iterator, Countable, ArrayAccess{
  private $nodeList;
  private $doc;
  private $counter = 0;

  function __construct($nodeList, $doc){
    $this->nodeList = $nodeList;
    $this->doc = $doc;
  }
  
  public function destruct()
  {
	 $this->nodeList = NULL;
	 $this->doc = NULL;
	 $this->counter = NULL;

	 unset($this->doc);
	 unset($this->nodeList);
	 unset($this->counter);
  } 

  /*
  abstract public boolean offsetExists ( mixed $offset )
  abstract public mixed offsetGet ( mixed $offset )
  abstract public void offsetSet ( mixed $offset , mixed $value )
  abstract public void offsetUnset ( mixed $offset )
  */

  public function offsetExists($offset){ return 0 <= $offset && $offset < $this->nodeList->length; }
  public function offsetGet($offset){ return new AHTMLNode($this->nodeList->item($offset), $this->doc); }
  public function offsetSet($offset, $value){ trigger_error('offsetSet not implemented', E_USER_WARNING); }
  public function offsetUnset($offset){ trigger_error('offsetUnset not implemented', E_USER_WARNING); }

  public function count(){
    return $this->nodeList->length;
  }

  public function rewind(){
    $this->counter = 0;
  }

  public function current(){
    return new AHTMLNode($this->nodeList->item($this->counter), $this->doc);
  }

  public function key(){
    return $this->counter;
  }

  public function next(){
    $this->counter++;
  }

  public function valid(){
    return $this->counter < $this->nodeList->length;
  }

  public function last(){
    return ($this->nodeList->length > 0) ? new AHTMLNode($this->nodeList->item($this->nodeList->length - 1), $this->doc) : null;
  }

  public function remove(){
    foreach($this as $node){
      $node->remove();
    }
    return $this;
  }

  function map($c){
    $ret = array();
    foreach($this as $node){
      $ret[] = $c($node);
    }
    return $ret;
  }


  //math methods
  public function doMath($nl, $op = 'plus'){
    $paths = array();
    $other_paths = array();

    foreach($this as $node){ $paths[] = $node->node->getNodePath(); }
    foreach($nl as $node){ $other_paths[] = $node->node->getNodePath(); }
    switch($op){
      case 'plus': $new_paths = array_unique(array_merge($paths, $other_paths)); break;
      case 'minus': $new_paths = array_diff($paths, $other_paths); break;
      case 'intersect': $new_paths = array_intersect($paths, $other_paths); break;
    }
    return new AHTMLNodeList($this->doc->xpath->query(implode('|', $new_paths)), $this->doc);
  }

  public function minus($nl){ return $this -> doMath($nl, 'minus'); }
  public function plus($nl){ return $this -> doMath($nl, 'plus'); }
  public function intersect($nl){ return $this -> doMath($nl, 'intersect'); }


  // magic methods
  public function __call($key, $values){
    $key = strtolower(str_replace('_', '', $key));
    switch($key){
      case 'to_a':
        $retval = array();
        foreach($this as $node){
          $retval[] = new AHTMLNode($this->nodeList->item($this->counter), $this->doc);
        }
        return $retval;
    }
    // otherwise

    $retval = array();

  /*
      if(preg_match(TAGS_REGEX, $key, $m)) return $this->find($m[1]);
      if(preg_match(TAG_REGEX, $key, $m)) return $this->find($m[1], 0);
  */

    if(preg_match(ATTRIBUTES_REGEX, $key, $m) || preg_match('/^((clean|trim|str).*)s$/', $key, $m)){
      foreach($this as $node){
        $arg = $m[1];
        $retval[] = $node->$arg;
      }
      return $retval;
    }

    if(preg_match(ATTRIBUTE_REGEX, $key, $m)){
      foreach($this as $node){
        $arg = $m[1];
        $retval[] = $node->$arg;
      }
      return implode('', $retval);
    }

    // what now?
    foreach($this as $node){$retval[] = isset($values[0]) ? $node->$key($values[0]) : $node->$key();}
    return implode('', $retval);
  }

  public function __get($key){
    return $this->$key();
  }

  public function __toString(){ return $this->html(); }

  public function length(){ return $this->nodeList->length; }
}

class AHTMLNode extends AdvancedHtmlBase implements ArrayAccess{
  private $_path;

  function __construct($node, $doc){
    $this->node = $node;
    $this->_path = $node->getNodePath();
    $this->doc = $doc;
    $this->is_text = !!($node->nodeName == '#text');
  }
  public function destruct()
  {
	  $this->_path = NULL;
	  $this->doc = NULL;
	  $this->node = NULL;
	  $this->dom = NULL;

	  unset($this->_path);
	  unset($this->doc);
	  unset($this->node);
	  unset($this->dom);

  }
  
  private function get_fragment($html){
    $dom = $this->doc->dom;
    $fragment = $dom->createDocumentFragment() or die('nope');
    $fragment->appendXML($html);
    return $fragment;
  }

  function replace($html){
    $node = empty($html) ? null : $this->before($html);
    $this->remove();
    return $node;
  }

  function before($html){
    $fragment = $this->get_fragment($html);
    $this->node->parentNode->insertBefore($fragment, $this->node);
    return new AHTMLNode($this->node->previousSibling, $this->doc);
  }

  function after($html){
    $fragment = $this->get_fragment($html);
    if($ref_node = $this->node->nextSibling){
      $this->node->parentNode->insertBefore($fragment, $ref_node);
    } else {
      $this->node->parentNode->appendChild($fragment);
    }
  }

  function decamelize($str){
    $str = preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $str);
    return preg_replace('/ /', '_', strtolower($str));
  }

  function attributes(){
    $ret = array();
    foreach($this->node->attributes as $attr){
      $ret[$attr->nodeName] = $attr->nodeValue;
    }
    return $ret;
  }

  function flatten($key = null, $level = 1){
    $children = $this->children;
    $ret = array();
    $tag = $this->tag;
    if($this->at('./preceding-sibling::' . $this->tag) || $this->at('./following-sibling::' . $this->tag) || ($key = $this->tag . 's')){
      $count = $this->search('./preceding-sibling::' . $this->tag)->length + 1;
      $tag .= '_' . $count;
    }
    if($children->length == 0){
      $ret[$this->decamelize(implode(' ', array_filter(array($key, $tag))))] = $this->text;
    } else {
      foreach($children as $child){
        $ret = array_merge($ret, $child->flatten(implode(' ', array_filter(array($key, $level <= 0 ? $tag : null))), $level - 1));
      }
    }
    return $ret;
  }

  public function __set($key, $value){
    switch($key){
      case 'text':
      case 'innertext':
      case 'innerText':
      case 'plaintext':
        $this->node->nodeValue = $value;
        return;
      case 'outertext':
        $this->replace($value); return;
      case 'tag':
        $el = $this->replace('<' . $value . '>' . $this->innerhtml . '</' . $value . '>');
        foreach($this->node->attributes as $key => $att){
          $el->$key = $att->nodeValue;
        }
        $this->node = $el->node;
        return;

      //default: trigger_error('Unknown property: ' . $key, E_USER_WARNING);
      //case 'name': return $this->node->nodeName;
    }
   
    if (in_array($key,array('_path','dom','doc','node'))) return;
    
    //trigger_error('Unknown property: ' . $key, E_USER_WARNING);
    if($value === null){
      $this->node->removeAttribute($key);
    } else {
      $this->node->setAttribute($key, $value);
    }

  }

  public function offsetExists($offset){ return true; }
  public function offsetGet($offset){ return $this->node->getAttribute($offset); }
  public function offsetSet($key, $value){
    if($value){
      $this->node->setAttribute($key, $value);
    } else {
      $this->node->removeAttribute($key);
    }
    //trigger_error('offsetSet not implemented', E_USER_WARNING);
  }
  public function offsetUnset($offset){ trigger_error('offsetUnset not implemented', E_USER_WARNING); }

  public function title(){ return $this->node->getAttribute('title'); }
}

class CSS{
  private static function is_xpath($str){
    return preg_match('/^\(?\.?\//', $str);
  }

  static function do_id($str){
    if(!preg_match('/^#(.*)/', $str, $m)) die('no attribute match!');
    return "@id = '" . $m[1] . "'";
  }

  static function do_class($str){
    if(!preg_match('/^\.(.*)/', $str, $m)) die('no attribute match!');
    return "contains(concat(' ', normalize-space(@class), ' '), ' " . $m[1] . " ')";
  }

  private static function parse_nth($str){
    switch(true){
      case preg_match('/^(-?\d+)(?:n\+(\d+))$/', $str, $m): return array(intval($m[1]), intval($m[2]));
      case preg_match('/^(-?\d+)(?:n\+(\d+))$/', $str, $m): return array(intval($m[1]), intval($m[2]));
      case preg_match('/^n\+(\d+)$/', $str, $m): return array(1, intval($m[1]));
      case preg_match('/^-n\+(\d+)$/', $str, $m): return array(-1, intval($m[1]));
      case preg_match('/^(\d+)n$/', $str, $m): return array(intval($m[1]), 0);
      case preg_match('/^even$/', $str, $m): return self::parse_nth('2n+0');
      case preg_match('/^odd$/', $str, $m): return self::parse_nth('2n+1');
      case preg_match('/^(-?\d+)$/', $str, $m): return array(null, intval($m[1]));;
      default: die('no match: ' . $str);
    }
  }

  private static function nth($str, $last = false){
    list($a, $b) = self::parse_nth($str);
    //echo $a . ":" . $b . "\n";
    $tokens = array();
    if($last){
      if($a === null) return "position() = last() - " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "((last()-position()+1) >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "((last()-position()+1) <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "((((last()-position()+1)-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "((last()-position()+1) mod " . abs($a) . ") = 0";
    } else {
      if($a === null) return "position() = " . $b;
      if($b > 0 && $a >= 0) $tokens[] = "(position() >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "(position() <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "(((position()-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "(position() mod " . abs($a) . ") = 0";
    }
    return implode(' and ', $tokens);
  }

  // This stuff is wrong, I need to look at this some more.
  private static function nth_child($str, $last = false){
    list($a, $b) = self::parse_nth($str);
    //echo $a . ":" . $b . "\n";
    $tokens = array();
    if($last){
      if($a === null) return "count(following-sibling::*) = " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "((last()-position()+1) >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "((last()-position()+1) <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "((((last()-position()+1)-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "((last()-position()+1) mod " . abs($a) . ") = 0";
    } else {
      if($a === null) return "count(preceding-sibling::*) = " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "(position() >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "(position() <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "(((position()-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "(position() mod " . abs($a) . ") = 0";
    }
    return implode(' and ', $tokens);
  }

  private static function not($str){
    switch(true){
      case preg_match('/^\.(\w+)$/', $str, $m): return self::do_class($str);
      case preg_match('/^\#(\w+)$/', $str, $m): return self::do_id($str);
      case preg_match('/^(\w+)$/', $str, $m): return "self::" . $str;
      case preg_match('/^\[(.*)\]$/', $str, $m): return substr(self::do_braces($str), 1, -1);
      default: return self::translate($str);
    }
  }


  static function do_pseudo($str, $name){
    if(!preg_match('/^:([\w-]+)(?:\((.*)\))?$/', $str, $m)) die('no attribute match!');
    //var_dump($m); exit;
    @list($_, $pseudo, $value) = $m;

    switch(true){
      #case preg_match('/^\[.*\]$/', $value): $inner = preg_replace('/^\[(.*)\]$/', '\1', self::do_braces($value)); break;
      default: $inner = self::translate($value); break;
    }

//    self::translate_part($value)
    switch($pseudo){
      case 'last': return "[position() = last()]";
      case 'first': return "[position() = 1]";
      case 'parent': return "[node()]";
      case 'contains': return "[contains(., " . $value . ")]";
      case 'nth': return "[position() = " . $value . "]";
      case 'gt': return "[position() > " . $value . "]";
      case 'lt': return "[position() < " . $value . "]";
      case 'eq': return "[position() = " . $value . "]";
      case 'root': return "[not(parent::*)]";
#      case 'nth-child': return "[count(preceding-sibling::*) = " . ($value - 1) . "]";
      case 'nth-child': return "[" . self::nth_child($value) . "]";
#      case 'nth-last-child': return "[count(following-sibling::*) = " . ($value - 1) . "]";
      case 'nth-last-child': return "[" . self::nth_child($value, true) . "]";
#      case 'nth-of-type': return "[position() = " . $value . "]";
      case 'nth-of-type': return "[" . self::nth($value) . "]";
#      case 'nth-last-of-type': return $value ? "[position() = last() - " . ($value - 1) . "]" : "[position() = last()";
      case 'nth-last-of-type': return "[" . self::nth($value, true) . "]";
      case 'first-child': return "[count(preceding-sibling::*) = 0]";
      case 'first-of-type': return "[position() = 1]";
      case 'last-child': return "[count(following-sibling::*) = 0]";
      case 'last-of-type': return "[position() = last()]";
      case 'only-child': return "[count(preceding-sibling::*) = 0 and count(following-sibling::*) = 0]";
      case 'only-of-type': return "[last() = 1]";
      case 'empty': return "[not(node())]";
      case 'not': return "[not(" . self::not($value) . ")]";
#      case 'has': return "[" . $inner . "]";
      case 'has': return "[" . $inner . "]";
  //      case 'link': return "[link(.)]";
      case 'link':
      case 'visited':
      case 'hover':
      case 'active':
        return "[" . $pseudo . "(.)]";

      default: die('unknown pseudo element: ' . $str);
    }
  }

  static function do_braces($str){
    $re = '/("(?>[^"]|(?R))*\)"|\'(?>[^\']|(?R))*\'|[~^$*|]?=)\s*/';

    $tokens = preg_split($re, substr($str, 1, strlen($str) - 2), 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
//    var_dump($tokens);
    $attr = trim(array_shift($tokens));
//     && )
    if(!$op = @trim(array_shift($tokens))){
      switch(true){
        case preg_match('/^\d+$/', $attr): return "[count(preceding-sibling::*) = " . ($attr - 1) . "]"; // [2] -> [count(preceding-sibling::*) = 1]
        default: return "[@" . $attr . "]"; // [foo] => [@foo]
      }
    }
    switch(true){
      case preg_match('/^(text|comment)$/', $attr, $m): $attr = $m[1] . "()"; break;
      case !preg_match('/[@(]/', $attr): $attr = '@' . $attr; break;
    }
//    if(!preg_match('/[@(]/', $attr)) $attr = '@' . $attr;
    $value = @trim(array_shift($tokens));
    if(!preg_match('/^["\'].*["\']$/', $value)) $value = "'" . $value . "'";
//    $value = "'" . preg_replace('/^["\'](.*)["\']$/', '\1', $value) . "'";

    switch($op){
      case '*=': return "[contains(" . $attr . ", " . $value . ")]";
      case '^=': return "[starts-with(" .$attr. ", " . $value . ")]";
      case '~=': return "[contains(concat(\" \", " . $attr . ", \" \"),concat(\" \", " . $value . ", \" \"))]";
      case '$=': return "[substring(" . $attr . ", string-length(" . $attr . ") - string-length(" . $value . ") + 1, string-length(" . $value . ")) = " . $value . "]";
      case '|=': return "[" . $attr . " = " . $value . " or starts-with(" . $attr . ", concat(" . $value . ", '-'))]";
      case '=': return "[" . $attr . " = " . $value . "]";
      default: die('unknown op: ' . $op);
    }
  }

  static function translate_nav($str){
    switch($str){
      case '+': return '/following-sibling::';
      case '~': return '/following-sibling::';
      case '>': return '/';
      case '': return '//';
    }
  }

  static function translate_part($str, $last_nav = ''){
    $str = preg_replace('/:contains\(([^()]*)\)/', '[text*=\\1]', $str); // quick and dirty contains fix
    $retval = array();
    $re = '/(:(?:nth-last-child|nth-of-type|nth-last-of-type|first-child|last-child|first-of-type|last-of-type|only-child|only-of-type|nth-child|first|last|gt|lt|eq|root|nth|empty|not|has|contains|parent|link|visited|hover|active)(?:\((?>[^()]|(?R))*\))?|\[(?>[^\[\]]|(?R))*\]|[#.][\w-]+)/';
    $name = '*';
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      switch(true){
        case preg_match('/^:/', $token): $retval[] = self::do_pseudo($token, $name); break;
        case preg_match('/^\[/', $token): $retval[] = self::do_braces($token); break;
        case preg_match('/^#/', $token): $retval[] = "[" . self::do_id($token) . "]"; break;
        case preg_match('/^\./', $token): $retval[] = "[" . self::do_class($token) . "]"; break;
        default: $name = $token;
      }
    }
    if(in_array($name, array('text', 'comment'))) $name .= '()';
    return ($last_nav === '+' ? "*[1]/self::" : '') . $name . implode('', $retval);
    //return $name . implode('', $retval);
  }

  static function translate($str){
    $retval = array();
    $re = '/(\((?>[^()]|(?R))*\)|\[(?>[^\[\]]|(?R))*\]|\s*[+~>]\s*| \s*)/';
    $item = '';

    $last_nav = null;
    //echo "\n!" . $str . "!\n";
    //var_dump(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      $token = trim($token);
      //echo $token . "-\n";
      switch($token){
        case '>':
        case '~':
        case '+':
        case '':
          if(!empty($item)) $retval[] = self::translate_part(trim($item), $last_nav);
          $item = '';
          $last_nav = $token;
          if(!isset($first_nav)){
            $first_nav = trim($token);
          } else {
            $retval[] = self::translate_nav(trim($token));
          }
          break;
        default:
          if(!isset($first_nav)) $first_nav = '';
          $item .= $token;
      }
    }
    //    var_dump($first_nav, $retval); exit;

    $retval[] = self::translate_part(trim($item), $last_nav);
    if(!isset($first_nav)) $first_nav = '';
    return '.' . self::translate_nav($first_nav) . implode('', $retval);
  }

  private static function get_expressions($str){
    $retval = array();
    $re = '/(\((?>[^()]|(?R))*\)|\[(?>[^\[\]]|(?R))*\]|,)/';
    $item = '';
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      if(',' == $token){
        $retval[] = trim($item);
        $item = '';
      } else{
        $item .= $token;
      }
    }
    $retval[] = trim($item);
    return $retval;
  }

  public static function xpath_for($str){
    if(self::is_xpath($str)) return $str;
    $str = preg_replace('/\b(text|comment)\(\)/', '\1', $str);
    $retval = array();
    foreach(self::get_expressions($str) as $expr){
      $retval[] = self::translate($expr);
    }
    return implode('|', $retval);
  }
}

/**
 * PHPStr - Add regex functionality to PHP strings
 * Website: https://github.com/monkeysuffrage/phpstr
 *
 * @author P Guardiario <pguardiario@gmail.com>
 * @version 0.2
 */

/**
 * Str
 */

 class Str{
  var $text;

  function __construct($str){
    $this->text = $str;
  }

  function match($regex, $group_number = 0){
    if(!preg_match($regex, $this->text, $m)) return false;
    $val = $m[$group_number];
    return $val; // new Str($val);
  }

  function scan($regex, $group_number = 0){
    preg_match_all($regex , $this->text, $m);
    return $m[$group_number];
  }

  function gsub($regex, $replacement, $limit = -1){
    if('Closure' == @get_class($replacement)){
      $val = preg_replace_callback($regex, $replacement, $this->text, $limit);
    } else {
      $val = preg_replace($regex, $replacement, $this->text, $limit);
    }
    return new Str($val);
  }

  function sub($regex, $replacement){
    $val = $this->gsub($regex, $replacement, 1);
    return new Str($val);
  }

  function split($regex, $limit = -1){
    return preg_split($regex, $this->text, $limit);
  }

  public function __toString(){ return $this->text; }
  public function to_s(){ return $this->text; }
}

function str_get_html($html){ return new AdvancedHtmlDom($html); }
function file_get_html($url){ return str_get_html(file_get_contents($url)); }

function str_get_xml($html){ return new AdvancedHtmlDom($html, true); }
function file_get_xml($url){ return str_get_xml(file_get_contents($url)); }
}
