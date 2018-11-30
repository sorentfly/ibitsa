<?php

define('T_WLG_ROOT', -100);
define('T_WLG_PAREN_START', -89);
define('T_WLG_PAREN_STOP', -88);
define('T_WLG_BRACE_START', -79);
define('T_WLG_BRACE_STOP', -78);
define('T_WLG_BRACK_START', -69);
define('T_WLG_BRACK_STOP', -68);
define('T_WLG_STATEMENT_SEPARATOR', -59);
define('T_WLG_KEYWORD_START', -58);
define('T_WLG_LIST_SEPARATOR', -57);

define('T_WLG_BLOCK', -49);
define('T_WLG_EXPRESSION', -48);

class Engine_Template_Token
{
  protected $_index;

  protected $_code;

  protected $_value;

  protected $_line;

  protected $_name;

  protected $_parent;

  protected $_children = array();

  protected $_depth;

  protected $_skipRender = false;

  protected static $_indentLevel = 0;

  static public function fromArray($spec)
  {
    if( is_string($spec) ) {
      return self::fromString($spec);
    }
    $spec = array(
      'code' => $spec[0],
      'value' => $spec[1],
      'line' => isset($spec[2]) ? $spec[2] : null,
      'name' => self::getTokenNameFromCode($spec[0]),
    );
    return new Engine_Template_Token($spec);
  }

  static public function fromInteger($code, $value = null, $index = null)
  {
    $spec = array(
      'code' => $code,
      'value' => $value,
      'index' => $index,
      'name' => self::getTokenNameFromCode($code),
    );
    return new Engine_Template_Token($spec);
  }

  static public function fromString($string)
  {
    $spec = array(
      'code' => self::getTokenCodeFromValue($string),
      'value' => $string,
      'name' => self::getTokenNameFromCode(self::getTokenCodeFromValue($string)),
    );
    return new Engine_Template_Token($spec);
  }



  // General
  
  public function __construct(array $spec)
  {
    foreach( $spec as $key => $value ) {
      if( is_string($key) ) {
        $method = 'set' . ucfirst($key);
        $property = '_' . $key;
        if( method_exists($this, $method) ) {
          $this->$method($value);
        } else if( property_exists($this, $property) ) {
          $this->$property = $value;
        }
      }
    }
  }

  public function getCode()
  {
    return $this->_code;
  }

  public function getValue()
  {
    return $this->_value;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setDepth($depth)
  {
    $this->_depth = $depth;
    foreach( $this->_children as $child ) {
      $child->setDepth($depth + 1);
    }
    return $this;
  }

  public function getDepth()
  {
    return $this->_depth;
  }

  public function getSkipRender()
  {
    return (bool) $this->_skipRender;
  }

  public function setSkipRender($flag = true)
  {
    $this->_skipRender = (bool) $flag;
    return $this;
  }

  public function remove()
  {
    $parent = $this->getParent();
    if( null !== $parent ) {
      $parent->removeChild($this);
    }
    return $this;
  }



  // Parent

  public function getParent()
  {
    return $this->_parent;
  }
  
  public function setParent($parent = null)
  {
    $this->_parent = $parent;
    $this->setDepth($parent->getDepth() + 1);
    return $this;
  }

  public function removeParent($recursive = true)
  {
    $this->_parent = null;
    if( $recursive ) {
      foreach( $this->_children as $child ) {
        $child->removeParent($recursive);
      }
    }
    return $this;
  }



  // Children - Add
  
  public function addChild($child)
  {
    if( !($child instanceof Engine_Template_Token) ) {
      $child = new Engine_Template_Token($child);
    }
    $child->setParent($this);
    $this->_children[] = $child;
    return $this;
  }

  public function addChildAfter($child, $rel)
  {
    if( !($child instanceof Engine_Template_Token) ) {
      $child = new Engine_Template_Token($child);
    }
    $child->setParent($this);
    
    $index = $this->getChildIndex($rel);
    if( null === $index ) {
      $this->_children[] = $child;
    } else {
      array_splice($this->_children, $index + 1, 0, array($child));
    }
    return $this;
  }

  public function addChildBefore($child, $rel)
  {
    if( !($child instanceof Engine_Template_Token) ) {
      $child = new Engine_Template_Token($child);
    }
    $child->setParent($this);

    $index = $this->getChildIndex($rel);
    if( null === $index ) {
      $this->_children[] = $child;
    } else {
      array_splice($this->_children, $index, 0, array($child));
    }
    return $this;
  }
  
  public function setChildren(array $children)
  {
    foreach( $children as $child ) {
      $child->setParent($this);
    }
    $this->_children = $children;
    return $this;
  }



  // Children - Misc

  public function getChildIndex($child)
  {
    $index = null;
    foreach( $this->_children as $tmpIndex => $tmpChild ) {
      if( $child === $tmpChild ) {
        $index = $tmpIndex;
      }
    }
    return $index;
  }

  public function hasChild($child)
  {
    return null !== $this->getChildIndex($child);
  }



  // Children - Get (single)

  public function getChildAfter($child)
  {
    $index = $this->getChildIndex($child);
    return $this->getChildByIndex($index + 1);
  }

  public function getChildBefore($child)
  {
    $index = $this->getChildIndex($child);
    return $this->getChildByIndex($index - 1);
  }
  
  public function getChildByCode($code)
  {
    foreach( $this->_children as $index => $child ) {
      if( $child->getCode() == $code ) {
        return $child;
      }
    }
    return null;
  }

  public function getChildByIndex($index)
  {
    if( isset($this->_children[$index]) ) {
      return $this->_children[$index];
    } else {
      return null;
    }
  }
  public function getFirstChild()
  {
    if( count($this->_children) > 0 ) {
      return $this->_children[0];
    } else {
      return null;
    }
  }

  public function getLastChild()
  {
    if( count($this->_children) > 0 ) {
      return $this->_children[count($this->_children) - 1];
    } else {
      return null;
    }
  }



  // Children - Get (multiple)
  
  public function getChildren()
  {
    return $this->_children;
  }
  
  public function getChildrenAfter($child)
  {
    $found = false;
    $children = array();
    foreach( $this->_children as $index => $tmp ) {
      if( $tmp === $child ) {
        $found = true;
      } else if( $found ) {
        $children[] = $tmp;
      }
    }
    return $children;
  }

  public function getChildrenBefore($child)
  {
    $found = false;
    $children = array();
    foreach( $this->_children as $index => $tmp ) {
      if( $tmp === $child ) {
        $found = true;
      } else if( !$found ) {
        $children[] = $tmp;
      }
    }
    return $children;
  }

  public function getChildrenBetween($from, $to, $inclusive = false)
  {
    $children = array();
    $state = 0;
    foreach( $this->_children as $index => $child ) {
      if( $state === 0 && $child === $from ) {
        $state = 1;
      } else if( $state === 1 && $child === $to ) {
        $state = 2;
      }
      // Add to list
      if( $state === 1 ) {
        if( $child !== $from || $inclusive ) {
          $children[] = $child;
        }
      } else if( $state === 2 && $child === $to && $inclusive ) {
        $children[] = $child;
      }
    }

    if( $state !== 2 ) {
      return array();
    } else {
      return $children;
    }
  }

  public function getChildrenByCode($code)
  {
    $children = array();
    foreach( $this->_children as $child ) {
      if( is_scalar($code) && $child->getCode() == $code ) {
        $children[] = $child;
      } else if( is_array($code) && in_array($child->getCode(), $code) ) {
        $children[] = $child;
      }
    }
    return $children;
  }



  // Children - Remove

  public function removeChild($child)
  {
    if( is_array($child) ) {
      $this->removeChildren($child);
    } else {
      $this->removeChildByIndex($this->getChildIndex($child));
    }

    return $this;
  }

  public function removeChildByIndex($index)
  {
    if( null !== $index && isset($this->_children[$index]) ) {
      array_splice($this->_children, $index, 1);
    }
    return $this;
  }

  public function removeChildren($children)
  {
    if( $children instanceof Engine_Template_Token ) {
      $this->removeChild($children);
    } else {
      foreach( $children as $child ) {
        $this->removeChild($child);
      }
    }
    
    return $this;
  }



  // Children - Advanced

  public function getNextChild($rel, $skipWhitespace = true)
  {
    $index = $this->getChildIndex($rel, $skipWhitespace);
    if( null === $index || !isset($this->_children[$index+1]) ) {
      return null;
    } else {
      $child = $this->_children[$index+1];
      if( $skipWhitespace && $child->getCode() == T_WHITESPACE ) {
        return $this->getNextChild($child, $skipWhitespace);
      } else {
        return $child;
      }
    }
  }

  public function getNextChildOfType($rel, $type)
  {
    $found = false;
    foreach( $this->_children as $tmpIndex => $child ) {
      if( $child === $rel ) {
        $found = true;
      } else if( $found ) {
        if( is_scalar($type) && $child->getCode() == $type ) {
          return $child;
        } else if( is_array($type) && in_array($child->getCode(), $type) ) {
          return $child;
        }
      }
    }
    
    return null;
  }

  public function getPreviousChild($rel, $skipWhitespace = true)
  {
    $index = $this->getChildIndex($rel, $skipWhitespace);
    if( null === $index || !isset($this->_children[$index-1]) ) {
      return null;
    } else {
      $child = $this->_children[$index-1];
      if( $skipWhitespace && $child->getCode() == T_WHITESPACE ) {
        return $this->getPreviousChild($child, $skipWhitespace);
      } else {
        return $child;
      }
    }
  }

  public function getPreviousChildOfType($rel, $type)
  {
    $found = false;
    foreach( $this->_children as $tmpIndex => $child ) {
      if( $child === $rel ) {
        $found = true;
      } else if( !$found ) {
        if( is_scalar($type) && $child->getCode() == $type ) {
          return $child;
        } else if( is_array($type) && in_array($child->getCode(), $type) ) {
          return $child;
        }
      }
    }

    return null;
  }


  
  // Siblings

  public function getNextSibling($skipWhitespace = true)
  {
    $parent = $this->getParent();
    if( null !== $parent ) {
      return $parent->getNextChild($this, $skipWhitespace);
    } else {
      return null;
    }
  }

  public function getNextSiblingOfType($token)
  {
    $parent = $this->getParent();
    if( null !== $parent ) {
      return $parent->getNextChildOfType($this, $token);
    } else {
      return null;
    }
  }

  public function getPreviousSibling($skipWhitespace = true)
  {
    $parent = $this->getParent();
    if( null !== $parent ) {
      return $parent->getPreviousChild($this, $skipWhitespace);
    } else {
      return null;
    }
  }

  public function getPreviousSiblingOfType($token)
  {
    $parent = $this->getParent();
    if( null !== $parent ) {
      return $parent->getPreviousChildOfType($this, $token);
    } else {
      return null;
    }
  }



  // Elements

  public function getPreviousElement($skipWhitespace = true)
  {
    $el = null;
    if( null !== ($prev = $this->getPreviousSibling($skipWhitespace)) ) {
      $tmp = $prev;
      while( $tmp->getLastChild() ) {
        $tmp = $tmp->getLastChild();
      }
      $el = $tmp;
    } else if( null !== ($parent = $this->getParent()) ) {
      $el = $parent;
    }
    if( $el ) {
      if( $skipWhitespace && in_array($el->getCode(), array(T_WHITESPACE, T_WLG_BLOCK, T_WLG_EXPRESSION)) ) {
        return $el->getPreviousElement($skipWhitespace);
      } else {
        return $el;
      }
    } else {
      return null;
    }
  }

  public function getNextElement($skipWhitespace = true)
  {
    if( count($this->_children) > 0 ) {
      $el = $this->getFirstChild();
    } else if( null !== ($next = $this->getNextSibling($skipWhitespace)) ) {
      $el = $next;
    } else if( null !== ($parent = $this->getParent()) ) {
      $el = null;
      $tmp = $parent;
      while( !$el ) {
        $el = $tmp->getNextSibling($skipWhitespace);
        if( !$el ) {
          $tmp = $tmp->getParent();
        }
        if( !$tmp ) {
          return null;
        }
      }
    }
    if( $el ) {
      if( $skipWhitespace && in_array($el->getCode(), array(T_WHITESPACE, T_WLG_BLOCK, T_WLG_EXPRESSION)) ) {
        return $el->getNextElement($skipWhitespace, true);
      } else {
        return $el;
      }
    } else {
      return null;
    }
  }



  // Output
  
  public function output()
  {
    $content = $this->_value;
    foreach( $this->_children as $child ) {
      $content .= $child->output();
    }
    return $content;
  }

  public function outputDebug()
  {
    $content = str_pad('', $this->_depth * 2, ' ')
        . $this->getName()
        . ' ['
        . $this->getCode()
        . '] '
        . addcslashes(htmlspecialchars($this->_value), "\n\r")
        . "\n\n";
    foreach( $this->_children as $child ) {
      $child->setDepth($this->_depth + 1); // Double check depth
      $content .= $child->outputDebug();
    }
    return $content;
  }



  // Processing

  public function process()
  {
    $this->processActionFunctionizeConstructs();
    $this->processActionCleanup();
    $this->processActionExpressions();
    $this->processActionBlocks();
    $this->processActionTemplateBlocks();
    $this->processActionCleanupSeparators();

    return $this;
  }
  
  public function processActionCleanup()
  {
    // Remove whitespace?
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_WHITESPACE ) {
        $child->remove();
      }
    }

    // Remove open tags?
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_OPEN_TAG ) {
        $child->remove();
      }
    }

    // Replace close tags with statement separators?
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_CLOSE_TAG ) {
        $tmp = Engine_Template_Token::factory(array(T_WLG_STATEMENT_SEPARATOR, ';'));
        $child->getParent()->addChildBefore($tmp, $child);
        $child->remove();
      }
    }

    // Remove comments?
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_DOC_COMMENT ) {
        $child->remove();
      }
    }

    // Recurse
    foreach( $this->getChildren() as $child ) {
      $child->processActionCleanup();
    }
    
    return $this;
  }

  public function processActionExpressions()
  {
    // Turn parentheses into a pseudo token
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_PAREN_START ) {
        // Ignore if parent is already an expression
        if( $child->getParent()->getCode() == T_WLG_EXPRESSION ) {
          continue;
        }
        $prev = $child->getPreviousSibling();
        $next = $child->getNextSibling();
        if( !$next || $next->getCode() != T_WLG_PAREN_STOP || !$prev ) {
          throw new Exception('Parse error!');
        }
        $token = Engine_Template_Token::factory(T_WLG_EXPRESSION);
//        $this->addChildBefore($token, $child);
        $prev->addChild($token);

        $child->remove();
        $next->remove();

        $token->addChild($child);
        $token->addChild($next);
      }
    }

    // Recurse
    foreach( $this->getChildren() as $child ) {
      $child->processActionExpressions();
    }

    return $this;
  }

  public function processActionBlocks()
  {
    // Turn braces into a pseudo token
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_BRACE_START ) {
        // Ignore if parent is already a block
        if( $child->getParent()->getCode() == T_WLG_BLOCK ) {
          continue;
        }
        $prev = $child->getPreviousSibling();
        $next = $child->getNextSibling();
        if( !$next || $next->getCode() != T_WLG_BRACE_STOP || !$prev ) {
          throw new Exception('Parse error!');
        }
        $token = Engine_Template_Token::factory(T_WLG_BLOCK);
//        $this->addChildBefore($token, $child);
        $prev->addChild($token);

        $child->remove();
        $next->remove();

        $token->addChild($child);
        $token->addChild($next);
      }
    }

    // Recurse
    foreach( $this->getChildren() as $child ) {
      $child->processActionBlocks();
    }

    return $this;
  }

  public function processActionTemplateBlocks()
  {
    foreach( $this->getChildren() as $child ) {
      $close = null;
      switch( $child->getCode() ) {
        case T_FOR:
//          if( !$close ) {
//            $close = $child->getNextSiblingOfType();
//          }
        case T_FOREACH:
//          if( !$close ) {
//            $close = $child->getNextSiblingOfType(T_ENDFOREACH);
//          }
        case T_WHILE;
//          if( !$close ) {
//            $close = $child->getNextSiblingOfType(T_ENDWHILE);
//          }
        case T_IF;
//          if( !$close ) {
//            $close = $child->getNextSiblingOfType();
//          }
          $next = $child->getNextSibling();
          if( $next && $next->getCode() == T_WLG_KEYWORD_START ) {
            $next->remove();
            $this->addChildAfter(Engine_Template_Token::factory(array(T_WLG_BRACE_START, '{')), $child);
          }
          break;

        case T_ELSEIF:
        case T_ELSE:
          $next = $child->getNextSibling();
          if( $next && $next->getCode() == T_WLG_KEYWORD_START ) {
            $next->remove();
            $this->addChildAfter(Engine_Template_Token::factory(array(T_WLG_BRACE_START, '{')), $child);
            $this->addChildBefore(Engine_Template_Token::factory(array(T_WLG_BRACE_STOP, '}')), $child);
          }
          break;

        case T_ENDFOR:
        case T_ENDFOREACH:
        case T_ENDSWITCH:
        case T_ENDWHILE:
        case T_ENDIF:
          $this->addChildBefore(Engine_Template_Token::factory(array(T_WLG_BRACE_STOP, '}')), $child);
          $child->remove();
          break;
      }
    }

    /*
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_KEYWORD_START ) {
        $prev = $child->getPreviousSibling();
        if( !$prev ) {
          throw new Exception('Parse error!');
        }

        $doRemoveNext = true;
        switch( $prev->getCode() ) {
          case T_FOR:
            $next = $child->getNextSiblingOfType(T_ENDFOR);
            break;
          case T_FOREACH:
            $next = $child->getNextSiblingOfType(T_ENDFOREACH);
            break;
          case T_WHILE:
            $next = $child->getNextSiblingOfType(T_ENDWHILE);
            break;
          case T_IF:
            $next = $child->getNextSiblingOfType(array(T_ELSE, T_ELSEIF, T_ENDIF));
            if( $next->getCode() !== T_ENDIF ) {
              $doRemoveNext = false;
            }
            break;
          case T_ELSEIF:
            $next = $child->getNextSiblingOfType(array(T_ELSE, T_ELSEIF, T_ENDIF));
            if( $next->getCode() !== T_ENDIF ) {
              $doRemoveNext = false;
            }
            break;
          case T_ELSE:
            $next = $child->getNextSiblingOfType(T_ENDIF);
            break;
          case T_SWITCH:
            $next = $child->getNextSiblingOfType(T_ENDSWITCH);
            break;
          default:
//            echo "<pre>";
//            echo $this->getParent()->outputDebug();
//            die();
//            throw new Exception('Parse error!');
            continue 2;
            break;
        }
//        echo "<pre>";
//        echo $this->outputDebug();
//        die();
        if( !$next ) {
          throw new Exception('Parse error!');
        }
        $children = $this->getChildrenBetween($prev, $next, false);
        foreach( $children as $tmpchild ) {
          $tmpchild->remove();
        }

        $tmp0 = Engine_Template_Token::factory(T_WLG_BLOCK);
        $tmp1 = Engine_Template_Token::factory(array(T_WLG_BRACE_START, '{'));
        $tmp2 = Engine_Template_Token::factory(array(T_WLG_BRACE_STOP, '}'));
        
        $tmp0->addChild($tmp1);
        $tmp0->addChild($tmp2);

        $prev->addChild($tmp0);

        $tmp1->setChildren($children);

        $child->remove();

        if( $doRemoveNext ) {
          $next->remove();
        }

//        $this->addChildBefore($tmp1, $child);
//        $this->addChildBefore($tmp2, $next);
//        $child->remove();
      }
    }
     * 
     */

    // Recurse
    foreach( $this->getChildren() as $child ) {
      $child->processActionTemplateBlocks();
    }

    return $this;
  }

  public function processActionCleanupSeparators()
  {
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_BRACE_STOP ) {
        $next = $child->getNextElement();
        if( null !== $next &&
            $next->getCode() == T_WLG_STATEMENT_SEPARATOR ) {
          $next->remove();
        }
      } else if( $child->getCode() == T_WLG_BRACE_START ) {
        $next = $child->getNextElement();
        if( null !== $next &&
            $next->getCode() == T_WLG_STATEMENT_SEPARATOR ) {
          $next->remove();
        }
      }
    }

    // Recurse
    foreach( $this->getChildren() as $child ) {
      $child->processActionCleanupSeparators();
    }

    return $this;
  }

  public function processActionFunctionizeConstructs()
  {
    foreach( $this->getChildren() as $child ) {
      if( $child->getCode() == T_ECHO ) {
        $next = $child->getNextSibling();
        if( !$next ) {
          throw new Exception('Parse error!');
        }
        if( $next->getCode() !== T_WLG_PAREN_START ) {
          $sep = $child->getNextSiblingOfType(array(
            T_CLOSE_TAG, T_WLG_STATEMENT_SEPARATOR,
          ));

          // Inject new tokens
          $tmp1 = Engine_Template_Token::factory(array(T_WLG_PAREN_START, '('));
          $tmp2 = Engine_Template_Token::factory(array(T_WLG_PAREN_STOP, ')'));
          
          $this->addChildAfter($tmp1, $child);
          $this->addChildBefore($tmp2, $sep);

          // Remove whitespace after the echo or paren
          $tmp1n = $tmp1->getNextSibling(false);
          if( $tmp1n && $tmp1n->getCode() == T_WHITESPACE ) {
            $tmp1n->remove();
          }
          $tmp2p = $tmp2->getPreviousSibling(false);
          if( $tmp2p && $tmp2p->getCode() == T_WHITESPACE ) {
            $tmp2p->remove();
          }

          // Now put them as children
          $tmpChildren = $this->getChildrenBetween($tmp1, $tmp2);
          //$tmpChildren[] = $tmp2;

          foreach( $tmpChildren as $tmpChild ) {
            $tmpChild->remove();
            $tmp1->addChild($tmpChild);
          }
        }
      }
    }
    
  }
  
  public function toJavascript()
  {
    // Skip rendering
    if( $this->_skipRender ) {
      return '';
    }
    
    $content = $this->getJavascriptToken();
    foreach( $this->_children as $child ) {
      $content .= $child->toJavascript();
    }
    return $content;
  }

  public function getJavascriptToken()
  {
    $shouldIndent = false;
    $indentPost = true;
    $value = $this->_value;
    switch( $this->_code ) {
      case T_ECHO:
        $value = 'this.append';
        break;
      case T_VARIABLE:
        $value = ltrim($value, '$'); // strip $
        
//        $tmp = $this->getPreviousElement();
//        if( $tmp && $tmp->getCode() == T_STRING && $tmp->getValue() == 'var ' ) {
//
//        } else if( $value !== 'this' ) {
//          $value = 'this.get("' . $value . '")';
//        }
        break;
      case T_OBJECT_OPERATOR:
        $value = '.';
        break;
      case T_INLINE_HTML:
        $value = 'this.append(' . json_encode($this->_value) . ');';
        $shouldIndent = true;
        break;
      case T_STRING:
        $p = $this->getPreviousElement();
        $pp = $p->getPreviousElement();
        $n = $this->getNextElement();
        if( $p && $p->getCode() == T_OBJECT_OPERATOR &&
            $pp && $pp->getValue() == '$this' ) {
          if( $n && $n->getCode() == T_WLG_PAREN_START ) {
            // function call
          } else {
            // object property
            $value = 'get("' . $value . '")';
          }
        }
        break;
      case T_WLG_STATEMENT_SEPARATOR:
        if( !$this->getParent() ||
            !$this->getParent()->getParent() ||
            !$this->getParent()->getParent()->getParent() ||
            $this->getParent()->getParent()->getParent()->getCode() != T_FOR ) {
          $shouldIndent = true;
        } else {
          $value .= ' ';
        }
        if( $this->getPreviousElement()->getCode() == T_WLG_STATEMENT_SEPARATOR ) {
          $value = '';
        }
        break;
        case T_ELSEIF:
          $value = 'else if';
          break;
      case T_WLG_BRACE_START:
        $tmp = $this->getPreviousElement();
        if( $tmp && in_array($tmp->getCode(), array(T_ELSE, T_ELSEIF, T_WLG_PAREN_STOP)) ) {
          $value = ' ' . $value;
        }
        $shouldIndent = true;
        self::$_indentLevel++;
        break;
      case T_WLG_BRACE_STOP:
        $tmp = $this->getNextElement();
        if( $tmp && in_array($tmp->getCode(), array(T_ELSE, T_ELSEIF)) ) {
          $value .= ' ';
        } else {
          $shouldIndent = true;
        }
        self::$_indentLevel--;
        break;
      case T_WLG_PAREN_START:
        // For arrays, translate to braces
        $par = $this->getParent();
        if( $par && $par->getCode() == T_WLG_EXPRESSION ) {
          $par = $par->getParent();
        }
        if( $par && $par->getCode() == T_ARRAY ) {
          $value = '{';
          self::$_indentLevel++;
          $shouldIndent = true;
        } else {
          $tmp = $this->getPreviousElement();
          if( $tmp && in_array($tmp->getCode(), array(T_IF, T_FOR, T_FOREACH, T_ELSEIF, T_WHILE)) ) {
            $value .= ' ';
          }
        }
        break;
      case T_WLG_PAREN_STOP:
        // For arrays, translate to braces
        $par = $this->getParent();
        if( $par && $par->getCode() == T_WLG_EXPRESSION ) {
          $par = $par->getParent();
        }
        if( $par && $par->getCode() == T_ARRAY ) {
          self::$_indentLevel--;
          $shouldIndent = true;
          $indentPost = false;
          $value = '}';
        } else {
          $tmp = $this->getNextElement();
          if( $tmp && $tmp->getCode() == T_WLG_BRACE_START ) {
            $value = ' ' . $value;
          }
        }
        break;

      case T_DOUBLE_ARROW:
        $value = ' ' . ':' . ' ';
        break;
      case T_DOUBLE_COLON:
        $value = '.';
        break;

      case T_WLG_LIST_SEPARATOR:
        $par = $this->getParent();
        if( $par && $par->getCode() == T_WLG_PAREN_START ) {
          $par = $par->getParent();
        }
        if( $par && $par->getCode() == T_WLG_EXPRESSION ) {
          $par = $par->getParent();
        }
        if( $par && $par->getCode() == T_ARRAY ) {
          $shouldIndent = true;
        } else {
          $value .= ' ';
        }
        break;

      case T_ARRAY:
        $value = ''; // ignore
        break;

      case T_FOREACH:
        $value = 'for';

        // le sigh
        $tmp = $this->getFirstChild();
        if( $tmp ) {
          $tmp2 = $tmp->getNextSibling();
          $tmp = $tmp->getFirstChild();
        }
        if( $tmp ) {
          $ref = $tmp->getChildByCode(T_AS);
          $ref2 = $tmp->getChildByCode(T_DOUBLE_ARROW);
          if( $ref ) {
            $before = $tmp->getChildrenBefore($ref);
            if( $ref2 ) {
              $after = $tmp->getChildrenBetween($ref, $ref2, false);
              $end = $tmp->getChildrenAfter($ref2);
            } else {
              $end = null;
              $after = $tmp->getChildrenAfter($ref);
            }
            if( !empty($before) && !empty($after) ) {
              $tmp->removeChildren($before);
              $tmp->removeChildren($after);
              $tmp->removeChildren($ref);

              // Now add them back backwards -_-
              $tmpArr = array_merge(array(
                Engine_Template_Token::factory(array(T_STRING, 'var '))
              ), $after, array(
                $ref
              ), $before);
              $tmp->setChildren($tmpArr);
            }
          }
        }
        break;

        case T_AS:
          $value = ' ' . 'in' . ' ';
          break;

      case T_OPEN_TAG:
      case T_CLOSE_TAG:
      //case T_WLG_KEYWORD_START:
      case T_ENDDECLARE: case T_ENDFOR: case T_ENDFOREACH: case T_ENDIF:
      case T_ENDSWITCH: case T_ENDWHILE:
        throw new Exception('Translation error! ' . $this->getName());
        break;
    }

    if( $shouldIndent ) {
      $tmp = $this->getNextElement();
      $adj = '';
      if( $tmp && $tmp->getCode() == T_WLG_BRACE_STOP ) {
        $adj =  "\n" . str_pad('', (self::$_indentLevel - 1) * 2, ' ');
      } else {
        $adj =  "\n" . str_pad('', self::$_indentLevel  * 2, ' ');
      }
      if( $indentPost ) {
        $value .= $adj;
      } else {
        $value = $adj . $value;
      }
    }

    return $value;
  }



  // Utility
  
  static public function getTokenCodeFromValue($value)
  {
    switch( $value ) {
      case '(':
        return T_WLG_PAREN_START;
        break;
      case ')':
        return T_WLG_PAREN_STOP;
        break;
      case '{':
        return T_WLG_BRACE_START;
        break;
      case '}':
        return T_WLG_BRACE_STOP;
        break;
      case '[':
        return T_WLG_BRACK_START;
        break;
      case ']':
        return T_WLG_BRACK_STOP;
        break;
      case ';':
        return T_WLG_STATEMENT_SEPARATOR;
        break;
      case ':':
        return T_WLG_KEYWORD_START;
        break;
      case ',':
        return T_WLG_LIST_SEPARATOR;
        break;
      default:
        return null;
        break;
    }
  }

  static public function getTokenNameFromCode($token)
  {
    switch( $token ) {
      case T_WLG_ROOT:
        return 'T_WLG_ROOT';
        break;
      case T_WLG_PAREN_START:
        return 'T_WLG_PAREN_START';
        break;
      case T_WLG_PAREN_STOP:
        return 'T_WLG_PAREN_STOP';
        break;
      case T_WLG_BRACE_START:
        return 'T_WLG_BRACE_START';
        break;
      case T_WLG_BRACE_STOP:
        return 'T_WLG_BRACE_STOP';
        break;
      case T_WLG_BRACK_START:
        return 'T_WLG_BRACK_START';
        break;
      case T_WLG_BRACK_STOP:
        return 'T_WLG_BRACK_STOP';
        break;
      case T_WLG_STATEMENT_SEPARATOR:
        return 'T_WLG_STATEMENT_SEPARATOR';
        break;
      case T_WLG_KEYWORD_START:
        return 'T_WLG_KEYWORD_START';
        break;
      case T_WLG_LIST_SEPARATOR:
        return 'T_WLG_LIST_SEPARATOR';
        break;

      case T_WLG_BLOCK:
        return 'T_WLG_BLOCK';
        break;
      case T_WLG_EXPRESSION:
        return 'T_WLG_EXPRESSION';
        break;

      default:
        return token_name($token);
        break;
    }
  }
  
  static public function isStartToken($token)
  {
    if( ($token instanceof Engine_Template_Token) ) {
      $token = $token->getCode();
    }
    
    return in_array($token, array(
      T_WLG_PAREN_START, T_WLG_BRACE_START, T_WLG_BRACK_START,
      T_START_HEREDOC,

      //T_OPEN_TAG,
      
      // Unique
//      T_DOLLAR_OPEN_CURLY_BRACES, T_OPEN_TAG_WITH_ECHO,
//      T_WLG_KEYWORD_START,
    ));
  }

  static public function isStopToken($token)
  {
    if( ($token instanceof Engine_Template_Token) ) {
      $token = $token->getCode();
    }
    
    return in_array($token, array(
      T_WLG_PAREN_STOP, T_WLG_BRACE_STOP, T_WLG_BRACK_STOP,
      T_END_HEREDOC,

      //T_CLOSE_TAG,

      // Unique
//      T_ENDDECLARE, T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE,
    ));
  }
}