<?php

/**
 * Doctrine doesn't do MATCH AGAINST expressions. This class adds a doctrine
 * extension that implements it.
 *
 * @link http://www.xsolve.pl/blog/full-text-searching-in-symfony2-2/
 */

namespace Nines\UtilBundle\Extensions\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Implements the MatchAgainst function.
 * 
 * @example by https://gist.github.com/1234419 Jérémy Hubert
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* InParameter {Literal}? ")"
 */
class MatchAgainstFunction extends FunctionNode {

    /**
     * List of the columns to match against.
     *
     * @var array
     */
    public $columns = array();
    
    /**
     * The thing to find.
     *
     * @var string
     */
    public $needle;
    
    /**
     * If true, the match is in boolean mode.
     *
     * @var boolean
     */
    public $mode;

    /**
     * Parse the expression.
     * 
     * @param Parser $parser
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        do {
            $this->columns[] = $parser->StateFieldPathExpression();
            $parser->match(Lexer::T_COMMA);
        } while ($parser->getLexer()->isNextToken(Lexer::T_IDENTIFIER));
        $this->needle = $parser->InParameter();
        while ($parser->getLexer()->isNextToken(Lexer::T_STRING)) {
            $this->mode = $parser->Literal();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Build the SQL for the expression.
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker) {
        $haystack = null;
        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }
        $query = "MATCH(" . $haystack .
                ") AGAINST (" . $this->needle->dispatch($sqlWalker);
        if ($this->mode) {
            $query .= " " . trim($this->mode->dispatch($sqlWalker), "'") . " )";
        } else {
            $query .= " )";
        }
        return $query;
    }

}
