<?php

namespace spec\H2W\Models;

use H2W\Models\Comment;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Comment::class);
    }
}
