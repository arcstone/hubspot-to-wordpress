<?php

namespace spec\H2W\Models;

use H2W\Models\Post;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PostSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Post::class);
    }

    function it_should_convert_a_hubspot_post_to_wordpress()
    {
        $this->beConstructedWith(factory(Post::class)->make()->getAttributes());

        $this->getWordPressAttributes()->shouldNotThrow();
    }
}
