<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class CollateFunction extends FunctionNode
{
    public ?InputParameter $expressionToCollate = null;

    public ?string $collation = null;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->expressionToCollate = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->collation = $lexer->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('%s COLLATE %s', $this->expressionToCollate->dispatch($sqlWalker), $this->collation);
    }
}
