<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class DistanceFunction extends FunctionNode
{
    public ?ArithmeticExpression $longitudeColumn = null;
    public ?ArithmeticExpression $latitudeColumn = null;
    public ?ArithmeticExpression $longitudeValue = null;
    public ?ArithmeticExpression $latitudeValue = null;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->longitudeColumn = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->latitudeColumn = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->longitudeValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->latitudeValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'st_distance(' .
            'point(' .
            $this->longitudeColumn->dispatch($sqlWalker) . ', ' .
            $this->latitudeColumn->dispatch($sqlWalker) .
            '), point(' .
            $this->longitudeValue->dispatch($sqlWalker) . ', ' .
            $this->latitudeValue->dispatch($sqlWalker) .
            ')' .
            ')';
    }
}
