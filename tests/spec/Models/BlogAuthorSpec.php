<?php

namespace spec\H2W\Models;

use H2W\Models\BlogAuthor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BlogAuthorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BlogAuthor::class);
    }

    function it_should_convert_a_hubspot_blog_author_to_wordpress_user()
    {
        $this->beConstructedWith(factory(BlogAuthor::class)->make()->getAttributes());

        $this->getWordPressAttributes()->shouldHaveCount(7);
    }
}
