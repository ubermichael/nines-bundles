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
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

/**
 * In DQL:
 *   STRTODATE(date, format) to parse a string into a date with format.
 *
 * In configuration:
 * doctrine:
 *    dbal:
 *       orm:
 *           dql:
 *               string_functions:
 *                   strtodate: Nines\UtilBundle\Extensions\Doctrine\MySQL\StrToDate
 */
class StrToDate extends FunctionNode {
    private $dateString;

    private $dateFormat;

    public function __construct($name = 'strtodate') {
        parent::__construct($name);
        $this->dateFormat = null;
        $this->dateString = null;
    }

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser) : void {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->dateString = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->dateFormat = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker) {
        return 'STR_TO_DATE(' .
            $this->dateString->dispatch($sqlWalker) . ', ' .
            $this->dateFormat->dispatch($sqlWalker) .
            ')';
    }
}
