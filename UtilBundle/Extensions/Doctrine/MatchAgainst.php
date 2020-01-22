<?php

namespace Nines\UtilBundle\Extensions\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class MatchAgainst extends FunctionNode {

    /**
     * @var PathExpression[]
     */
    private $fieldExpressions;

    /**
     * @var string
     */
    private $query;

    private $boolean;

    private $expansion;

    public function __construct($name) {
        parent::__construct($name);
        $this->fieldExpressions = [];
        $this->query = null;
        $this->boolean = false;
        $this->expansion = false;
    }

    /**
     * @inheritDoc
     */
    public function getSql(SqlWalker $sqlWalker) {
        dump($this);
        $fields = [];
        foreach($this->fieldExpressions as $expression) {
            $fields[] = $expression->dispatch($sqlWalker);
        }
        $against = $sqlWalker->walkStringPrimary($this->query);
        if($this->boolean) {
            $against .= ' IN BOOLEAN MODE';
        }
        if($this->expansion) {
            $against .= ' WITH QUERY EXPANSION';
        }
        return sprintf('MATCH (%s) AGAINST (%s)', implode(', ', $fields), $against);
    }

    /**
     * @inheritDoc
     * MATCH ( field [, field]* ) AGAINST ( query [ BOOLEAN [ EXPAND ]] )
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->fieldExpressions[] = $parser->StateFieldPathExpression();
        $lexer = $parser->getLexer();
        while($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->fieldExpressions[] = $parser->StateFieldPathExpression();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        if( strtolower($lexer->lookahead['value']) !== 'against') {
            $parser->syntaxError('against');
        }
        $parser->match(Lexer::T_IDENTIFIER);

        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->query = $parser->StringPrimary();

        if (strtolower($lexer->lookahead['value']) === 'boolean') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->boolean = true;
        }
        if (strtolower($lexer->lookahead['value']) === 'expand') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->expansion = true;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}