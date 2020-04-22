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

/**
 * In DQL:
 *   CAST(field AS integer) to try and extract a number.
 *
 * In configuration:
 * doctrine:
 *    dbal:
 *       orm:
 *           dql:
 *               string_functions:
 *                   cast: Nines\UtilBundle\Extensions\Doctrine\MySQL\Cast
 */
class Cast extends FunctionNode {
    /**
     * @var string[]
     */
    private $field = null;

    /**
     * @var string
     */
    private $type = null;

    public function __construct($name = 'cast') {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker) {
        return sprintf('CAST(%s AS %s)',
                       $sqlWalker->walkSimpleArithmeticExpression($this->field),
                       $this->type
        );
    }

    /**
     * {@inheritdoc}
     * CAST ( field AS expr )
     */
    public function parse(Parser $parser) : void {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->SimpleArithmeticExpression();
        $parser->match(Lexer::T_AS);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->expr = $parser->getLexer()->token['value'];
        while( ! $parser->getLexer()->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->expr .= ' ' . $parser->getLexer()->token['value'];
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
