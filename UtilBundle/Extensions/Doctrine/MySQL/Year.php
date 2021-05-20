<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Extensions\Doctrine\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Year extends FunctionNode {
    private $year;

    public function __construct($name = 'year') {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker) {
        $param = $sqlWalker->walkArithmeticPrimary($this->year);

        return "YEAR({$param})";
    }

    /**
     * {@inheritdoc}
     * YEAR(expr).
     */
    public function parse(Parser $parser) : void {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->year = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
