<?php

class Engine_Template_Processor
{
  static protected $_indentLevel = 0;
  
  static public function process($string)
  {
    // First pass - create tokens
    $rootNode = Engine_Template_Token::fromInteger(T_WLG_ROOT);
    $tokens = token_get_all($string);
    foreach( $tokens as $index => $token ) {
      $token = Engine_Template_Token::fromArray($token);
      $rootNode->addChild($token);
    }

    // Second pass - remove whitespace?
//    foreach( $rootNode->getChildrenByCode(T_WHITESPACE) as $child ) {
//      $child->remove();
//    }

    // Third pass - turn language constructs into their function-like syntax
    foreach( $rootNode->getChildrenByCode(T_ECHO) as $child ) {
      $tmp = $child->getNextSibling();
      if( $tmp && $tmp->getCode() != T_WLG_PAREN_START ) {
        $next = $child->getNextSiblingOfType(array(T_CLOSE_TAG, T_WLG_STATEMENT_SEPARATOR, T_INLINE_HTML));
        if( $next ) {
          $child->getParent()->addChildAfter(Engine_Template_Token::fromInteger(T_WLG_PAREN_START, '('), $child);
          $child->getParent()->addChildBefore(Engine_Template_Token::fromInteger(T_WLG_PAREN_STOP, ')'), $next);
        }
      }
    }

    // Fourth pass - attempt to convert parentheses
    $newRootNode = Engine_Template_Token::fromInteger(T_WLG_ROOT);
    $currentNode = $newRootNode;
    foreach( $rootNode->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_PAREN_START ) {
        $tmp = Engine_Template_Token::fromInteger(T_WLG_EXPRESSION);
        $currentNode->addChild($tmp);
        $currentNode = $tmp;

        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);
      } else if( $child->getCode() == T_WLG_PAREN_STOP ) {
        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);

        $currentNode = $currentNode->getParent();
        if( null === $currentNode ) {
          throw new Exception('Parse error!');
        }
      } else {
        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);
      }
    }
    $rootNode = $newRootNode;

    // Fifth pass - Add statement separators before close tags where applicable
    foreach( $rootNode->getChildrenByCode(T_CLOSE_TAG) as $child ) {
      $prev = $child->getPreviousSibling();
      if( $prev && !in_array($prev->getCode(), array(T_WLG_STATEMENT_SEPARATOR, T_WLG_BRACE_START, T_WLG_BRACE_STOP, T_WLG_KEYWORD_START)) ) {
        $rootNode->addChildBefore(Engine_Template_Token::fromInteger(T_WLG_STATEMENT_SEPARATOR, ';'), $child);
      }
    }

    // Sixth pass - convert nasty template blocks to normal braces
    $newRootNode = Engine_Template_Token::fromInteger(T_WLG_ROOT);
    $currentNode = $newRootNode;
    foreach( $rootNode->getChildren() as $child ) {
      $doAddChild = true;
      if( $child->getCode() == T_WLG_KEYWORD_START ) {
        $prev = $child->getPreviousSibling();
        if( $prev && $prev->getCode() == T_WLG_EXPRESSION ) {
          $prev = $prev->getPreviousSibling();
        }
        if( $prev ) {
          switch( $prev->getCode() ) {
            case T_FOR:
            case T_FOREACH:
            case T_WHILE:
            case T_IF:
              $doAddChild = false;
              $newRootNode->addChild(Engine_Template_Token::fromInteger(T_WLG_BRACE_START, '{'));
              break;

            case T_ELSE:
            case T_ELSEIF:
              $doAddChild = false;
              $newRootNode->addChild(Engine_Template_Token::fromInteger(T_WLG_BRACE_START, '{'));
              $newRootNode->addChildBefore(Engine_Template_Token::fromInteger(T_WLG_BRACE_STOP, '}'), $prev);
              break;

//            case T_ENDDECLARE:
//            case T_ENDFOR:
//            case T_ENDFOREACH:
//            case T_ENDIF:
//            case T_ENDSWITCH:
//            case T_ENDWHILE:
//              $newRootNode->addChildBefore(Engine_Template_Token::fromInteger(T_WLG_BRACE_STOP, '}'), $prev);
//              $newRootNode->removeChild($prev);
//              break;
          }
        }
      } else if( in_array($child->getCode(), array(T_ENDDECLARE, T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE)) ) {
        $newRootNode->addChild(Engine_Template_Token::fromInteger(T_WLG_BRACE_STOP, '}'));
        $newRootNode->removeChild($child);
        $doAddChild = false;
      }

      if( $doAddChild ) {
        $newRootNode->addChild($child);
      }
    }
    $rootNode = $newRootNode;
    
    // Seventh pass - attempt to convert braces
    $newRootNode = Engine_Template_Token::fromInteger(T_WLG_ROOT);
    $currentNode = $newRootNode;
    foreach( $rootNode->getChildren() as $child ) {
      if( $child->getCode() == T_WLG_BRACE_START ) {
        $tmp = Engine_Template_Token::fromInteger(T_WLG_BLOCK);
        $currentNode->addChild($tmp);
        $currentNode = $tmp;

        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);
      } else if( $child->getCode() == T_WLG_BRACE_STOP ) {
        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);
        
        $currentNode = $currentNode->getParent();
        if( null === $currentNode ) {
//          echo "<pre>";
//          echo $newRootNode->outputDebug();
//          die();
          throw new Exception('Parse error!');
        }
      } else {
        $rootNode->removeChild($child); // :S
        $currentNode->addChild($child);
      }
    }
    $rootNode = $newRootNode;

    
//    echo "<pre>" . PHP_EOL . PHP_EOL;
//    echo $rootNode->outputDebug();
//    echo "<hr>" . PHP_EOL . PHP_EOL;
//    echo $rootNode->output();
//    echo "<hr>" . PHP_EOL . PHP_EOL;
//    echo self::toJavascript($rootNode);
//    die();
    
    return $rootNode;
  }

  static public function toJavascript($node)
  {
    if( $node->getCode() == T_WLG_ROOT ) {
      self::$_indentLevel = 0;
    }

    if( $node->getSkipRender() ) {
      return '';
    }
    
    $content = self::getJavascriptToken($node);

    if( $node->getSkipRender() ) {
      return $content;
    }

    foreach( $node->getChildren() as $child ) {
      $content .= self::toJavascript($child);
    }
    return $content;
  }

  static public function getJavascriptToken($node)
  {
    $value = $node->getValue();
    $code = $node->getCode();
    $indent = 0;
    switch( $code ) {
      case T_ARRAY:
        $value = ''; // ignore
        break;
      case T_CLOSE_TAG:
        $value = ''; // . "\n";
        //$indent = 1;
        break;
      case T_DOC_COMMENT:
        $value = '';
        break;
      case T_DOUBLE_ARROW:
        $value = ' ' . ':' . ' ';
        break;
      case T_DOUBLE_COLON:
        $value = '.';
        break;
      case T_ECHO:
        $value = 'this.append';
        break;
      case T_ELSE:
        $value = ' else ';
        break;
      case T_ELSEIF:
        $value = ' else if ';
        break;
      case T_EMPTY:
        $value = 'this.empty';
        break;
      case T_FOREACH:
        $expression = $node->getNextSibling();
        if( $expression && $expression->getCode() == T_WLG_EXPRESSION ) {
          $block = $expression->getNextSibling();
          if( $block && $block->getCode() == T_WLG_BLOCK ) {
            // Process the expression
            $refPStart = $expression->getChildByCode(T_WLG_PAREN_START);
            $refPStop = $expression->getChildByCode(T_WLG_PAREN_STOP);
            $refAs = $expression->getChildByCode(T_AS);
            $refArrow = $expression->getChildByCode(T_DOUBLE_ARROW);
            if( !$refAs ) {
              throw new Exception('Translation error!');
            }
            $expr1 = $expression->getChildrenBetween($refPStart, $refAs, false);
            if( $refArrow ) {
              $expr3 = $expression->getChildrenBetween($refAs, $refArrow, false);
              $expr2 = $expression->getChildrenBetween($refArrow, $refPStop, false);
            } else {
              $expr3 = null;
              $expr2 = $expression->getChildrenBetween($refAs, $refPStop, false);
            }
            $expression->removeChildren($refAs);
            if( $refArrow ) {
              $expression->removeChildren($refArrow);
            }
            $expression->removeChildren($expr2);
            if( $expr3 ) {
              $expression->removeChildren($expr3);
            }

            // Ugly hack
            $expression->addChild(Engine_Template_Token::fromInteger(T_OBJECT_OPERATOR, '->'));
            $expression->addChild(Engine_Template_Token::fromInteger(T_STRING, 'each(function('));
            foreach( $expr2 as $t ) {
              $expression->addChild($t);
            }
            if( $expr3 ) {
              $expression->addChild(Engine_Template_Token::fromInteger(T_WLG_LIST_SEPARATOR, ','));
              foreach( $expr3 as $t ) {
                $expression->addChild($t);
              }
            }
            $expression->addChild(Engine_Template_Token::fromInteger(T_STRING, ') '));

            $block->getChildByCode(T_WLG_BRACE_STOP)->addChild(Engine_Template_Token::fromInteger(T_STRING, '.bind(this)'));
            $block->getChildByCode(T_WLG_BRACE_STOP)->addChild(Engine_Template_Token::fromInteger(T_STRING, ')'));
            $block->getChildByCode(T_WLG_BRACE_STOP)->addChild(Engine_Template_Token::fromInteger(T_WLG_STATEMENT_SEPARATOR, ';'));

//            $expression->getParent()->addChildAfter(Engine_Template_Token::fromInteger(T_OBJECT_OPERATOR, '->'), $expression);
//            $expression->getParent()->addChildAfter(Engine_Template_Token::fromInteger(T_STRING, '->'), $expression);

//            echo $expression->getParent()->outputDebug();
//            die();

            $value = '';
          }
        }
        break;
      case T_INLINE_HTML:
        $value = 'this.append(' . json_encode($value) . ');';
        //$value .= "\n";
        $indent = 1;
        break;
      case T_ISSET:
        $value = 'this.isset';
        break;
      case T_OBJECT_OPERATOR:
        $value = '.';
        break;
      case T_OPEN_TAG:
        $value = '';
        break;
      case T_STRING:
        $prev = $node->getPreviousElement();
        if( $prev && $prev->getCode() == T_OBJECT_OPERATOR ) {
          $prev2 = $prev->getPreviousElement();
          if( $prev2 && $prev2->getCode() == T_VARIABLE && $prev2->getValue() == '$this' ) {
            // Check if next is paren
            $next = $node->getNextElement();
            if( $next && $next->getCode() != T_WLG_PAREN_START ) {
              $value = 'get("' . $value . '")';
            } else if( !in_array($value, array('append', 'print', 'assign',
                'log', 'empty', 'isset', 'isJavascript')) ) {
              // Inject actual function name as first argument
              // Only add comma if there are other children
              if( count($next->getParent()->getChildren()) > 0 &&
                  $next->getNextElement()->getCode() != T_WLG_PAREN_STOP ) {
                $next->getParent()->addChildAfter(Engine_Template_Token::fromInteger(T_WLG_LIST_SEPARATOR, ','), $next);
              }
              $next->getParent()->addChildAfter(Engine_Template_Token::fromInteger(T_CONSTANT_ENCAPSED_STRING, '"' . $value . '"'), $next);
              // Set self to call
              $value = 'call';
            }
          }
        }
        break;
      case T_WHITESPACE:
        if( true ) {
          // Skip?
          $value = '';
        } else {
          // Truncate to a single space?
          if( strlen($value) > 1 && $value[0] == ' ' ) {
            $value = ' ';
          }
        }
        break;
      case T_WLG_BRACE_START:
        self::$_indentLevel++;
        $indent = 1;
        break;
      case T_WLG_BRACE_STOP:
        self::$_indentLevel--;

        $tmp = $node->getNextElement();
        if( $tmp && in_array($tmp->getCode(), array(T_ELSE, T_ELSEIF)) ) {

        } else {
          $indent = 1;
        }
        break;
      case T_WLG_LIST_SEPARATOR:
        $value .= ' ';
        break;
      case T_WLG_PAREN_START:
        // If parent.previous was an array, change to {
        if( $node->getParent() &&
            ($tmp = $node->getParent()->getPreviousElement()) &&
            $tmp->getCode() == T_ARRAY) {
          $value = '{';
        }
        break;
      case T_WLG_PAREN_STOP:
        // If parent.previous was an array, change to }
        if( $node->getParent() &&
            ($tmp = $node->getParent()->getPreviousElement()) &&
            $tmp->getCode() == T_ARRAY) {
          $value = '}';
        }
        break;
      case T_WLG_STATEMENT_SEPARATOR:
        if( $node->getParent() &&
            ($tmp = $node->getParent()->getPreviousElement()) &&
            $tmp->getCode() == T_FOR ) {
          // ignore
        } else {
          $prev = $node->getPreviousElement();
          if( $prev && in_array($prev->getCode(), array(T_WLG_STATEMENT_SEPARATOR, T_WLG_BRACE_START, T_WLG_BRACE_STOP, T_WLG_KEYWORD_START)) ) {
            $value = '';
          }
          $indent = 1;
        }
        break;
      case T_VARIABLE:
        $value = ltrim($value, '$'); // strip $
        break;
    }

    // Indent
    if( $indent ) {
      // Hack to fix closing brace
      $tmp = $node->getNextElement();
      if( $tmp ) {
        if( $tmp->getCode() == T_OPEN_TAG ) {
          $tmp = $tmp->getNextElement();
        }
//        if( $tmp->getCode() != T_WLG_BRACE_STOP ) {
//          $tmp = null;
//        }
      }

      if( $tmp && $tmp->getCode() == T_WLG_BRACE_STOP ) {
        $adj = "\n" . str_pad('', (self::$_indentLevel - 1) * 2, ' ');
      } else {
        $adj = "\n" . str_pad('', self::$_indentLevel * 2, ' ');
      }
      if( $indent === -1 ) {
        $value = $adj . $value;
      } else if( $indent === 1 ) {
        $value .= $adj;
      }
    }
    
    return $value;
  }
}