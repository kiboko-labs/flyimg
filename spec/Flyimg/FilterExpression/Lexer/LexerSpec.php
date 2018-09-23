<?php

namespace spec\Flyimg\FilterExpression\Lexer;

use Flyimg\FilterExpression\Lexer\Lexer;
use Flyimg\FilterExpression\Lexer\Token;
use PhpSpec\ObjectBehavior;

class LexerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Lexer::class);
    }

    function it_can_parse_unparametered_filter()
    {
        $this->tokenize('copy')->shouldIterateLike(new \ArrayIterator([
            new Token(Token::IDENTIFIER, 4, 'copy', 0, 1, 0),
        ]));
    }

    function it_can_parse_unparametered_filter_with_brackets()
    {
        $this->tokenize('copy()')->shouldIterateLike(new \ArrayIterator([
            new Token(Token::IDENTIFIER, 4, 'copy', 0, 1, 0),
            new Token(Token::OPENING_BRACKET, 1, '(', 4, 1, 4),
            new Token(Token::CLOSING_BRACKET, 1, ')', 5, 1, 5),
        ]));
    }

    function it_can_parse_filter_with_one_parameter()
    {
        $this->tokenize('width(200)')->shouldIterateLike(new \ArrayIterator([
            new Token(Token::IDENTIFIER, 5, 'width', 0, 1, 0),
            new Token(Token::OPENING_BRACKET, 1, '(', 5, 1, 5),
            new Token(Token::NUMBER_INTEGER, 3, '200', 6, 1, 6),
            new Token(Token::CLOSING_BRACKET, 1, ')', 9, 1, 9),
        ]));
    }

    function it_can_parse_filter_with_two_parameter()
    {
        $this->tokenize('resize(200,300)')->shouldIterateLike(new \ArrayIterator([
            new Token(Token::IDENTIFIER, 6, 'resize', 0, 1, 0),
            new Token(Token::OPENING_BRACKET, 1, '(', 6, 1, 6),
            new Token(Token::NUMBER_INTEGER, 3, '200', 7, 1, 7),
            new Token(Token::CHAIN, 1, ',', 10, 1, 10),
            new Token(Token::NUMBER_INTEGER, 3, '300', 11, 1, 11),
            new Token(Token::CLOSING_BRACKET, 1, ')', 14, 1, 14),
        ]));
    }

    function it_can_parse_chained_filters()
    {
        $this->tokenize('width(200),resize(200,300),copy')->shouldIterateLike(new \ArrayIterator([
            new Token(Token::IDENTIFIER, 5, 'width', 0, 1, 0),
            new Token(Token::OPENING_BRACKET, 1, '(', 5, 1, 5),
            new Token(Token::NUMBER_INTEGER, 3, '200', 6, 1, 6),
            new Token(Token::CLOSING_BRACKET, 1, ')', 9, 1, 9),

            new Token(Token::CHAIN, 1, ',', 10, 1, 10),

            new Token(Token::IDENTIFIER, 6, 'resize', 11, 1, 11),
            new Token(Token::OPENING_BRACKET, 1, '(', 17, 1, 17),
            new Token(Token::NUMBER_INTEGER, 3, '200', 18, 1, 18),
            new Token(Token::CHAIN, 1, ',', 21, 1, 21),
            new Token(Token::NUMBER_INTEGER, 3, '300', 22, 1, 22),
            new Token(Token::CLOSING_BRACKET, 1, ')', 25, 1, 25),

            new Token(Token::CHAIN, 1, ',', 26, 1, 26),

            new Token(Token::IDENTIFIER, 4, 'copy', 27, 1, 27),
        ]));
    }
}
