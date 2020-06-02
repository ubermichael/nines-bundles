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

class Regexp extends FunctionNode {
    /**
     * @var PathExpression
     */
    private $expr;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var string
     */
    private $options;

    public function __construct($name = 'regexp') {
        parent::__construct($name);
        $this->options = '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker) {
        $expression = $this->expr->dispatch($sqlWalker);
        $pattern = $sqlWalker->walkStringPrimary($this->regex);
        if ($this->options) {
            $options = $sqlWalker->walkStringPrimary($this->options);
        } else {
            $options = "''";
        }

        return sprintf("REGEXP_LIKE(%s, %s, %s)", $expression, $pattern, $options);
    }

    /**
     * {@inheritdoc}
     * YEAR(expr).
     */
    public function parse(Parser $parser) : void {
        $lexer = $parser->getLexer();
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->expr = $parser->StateFieldPathExpression();
        $parser->match(Lexer::T_COMMA);
        $this->regex = $parser->StringPrimary();

        if (Lexer::T_COMMA === $lexer->lookahead['type']) {
            $parser->match(Lexer::T_COMMA);
            $this->options = $parser->StringPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
