<?php

/**
 * Doctrine lacks a cast function. This file implements an extension which
 * implements cast.
 */

namespace Nines\UtilBundle\Extensions\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Cast a value to an integer in DQL.
 * 
 * Usage:  $qb->addSelect("INT(value) AS HIDDEN int_value");
 * 
 * @author mjoyce
 */
class CastAsIntegerFunction extends FunctionNode {
    
    /**
     * The string to cast as an integer.
     *
     * @var string
     */
    public $stringPrimary;

    /**
     * Parse the expression.
     * 
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->stringPrimary = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    
    /**
     * Build the SQL expression for the parsed DQL.
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'CAST(' . $this->stringPrimary->dispatch($sqlWalker) . ' AS unsigned)';
    }

}
