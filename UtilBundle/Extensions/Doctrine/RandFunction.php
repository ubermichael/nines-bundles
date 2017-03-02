<?php

/**
 * Doctrine doesn't have a random function. This extension 
 * adds one.
 */

namespace Nines\UtilBundle\Extensions\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Implements a random function in Doctrine queries. Add the extension to 
 * your config like so:
 * 
 * doctrine:
 *   orm:
 *       dql:
 *           numeric_functions:
 *               RAND: Nines\UtilBundle\Extensions\Doctrine\RandFunction
 *
 */
class RandFunction extends FunctionNode {
    
    /**
     * Parse the expression.
     * 
     * @param Parser $parser
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    
    /**
     * Return the generated SQL.
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker) {
        return 'RAND()';
    }
    
}
