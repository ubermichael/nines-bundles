<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Extensions\Doctrine\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class MatchAgainst extends FunctionNode {
    /**
     * @var PathExpression[]
     */
    private $fieldExpressions = [];

    /**
     * @var string
     */
    private $query;

    private $boolean;

    private $expansion;

    public function __construct($name = 'match') {
        parent::__construct($name);
        $this->fieldExpressions = [];
        $this->query = null;
        $this->boolean = false;
        $this->expansion = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker) {
        $fields = [];

        foreach ($this->fieldExpressions as $expression) {
            $fields[] = $expression->dispatch($sqlWalker);
        }
        $against = $sqlWalker->walkStringPrimary($this->query);
        if ($this->boolean) {
            $against .= ' IN BOOLEAN MODE';
        }
        if ($this->expansion) {
            $against .= ' WITH QUERY EXPANSION';
        }

        return sprintf('MATCH (%s) AGAINST (%s)', implode(', ', $fields), $against);
    }

    /**
     * {@inheritdoc}
     * MATCH ( field [, field]* ) AGAINST ( query [ BOOLEAN [ EXPAND ]] ).
     */
    public function parse(Parser $parser) : void {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->fieldExpressions[] = $parser->StateFieldPathExpression();
        $lexer = $parser->getLexer();
        while ($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->fieldExpressions[] = $parser->StateFieldPathExpression();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        if ('against' !== mb_strtolower($lexer->lookahead['value'])) {
            $parser->syntaxError('against');
        }
        $parser->match(Lexer::T_IDENTIFIER);

        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->query = $parser->StringPrimary();

        if ('boolean' === mb_strtolower($lexer->lookahead['value'])) {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->boolean = true;
        }
        if ('expand' === mb_strtolower($lexer->lookahead['value'])) {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->expansion = true;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
