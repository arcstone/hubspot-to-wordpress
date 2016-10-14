<?php

namespace spec\H2W\Models;

use H2W\Models\Blog;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BlogSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Blog::class);
    }
}
