<?php

namespace spec\Flyimg\App\OptionResolver;

use Flyimg\App\OptionResolver\FilterResolver;
use Flyimg\FilterExpression\Lexer\Lexer;
use PhpSpec\ObjectBehavior;

class FilterResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], new Lexer());
        $this->shouldHaveType(FilterResolver::class);
    }
}
