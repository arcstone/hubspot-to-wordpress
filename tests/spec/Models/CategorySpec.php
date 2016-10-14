<?php

namespace spec\H2W\Models;

use H2W\Models\Category;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Category::class);
    }

    function it_should_convert_a_hubspot_post_to_wordpress()
    {
        $post = [];
        // $post = $this->getSamplePost();

        $this->getWordPressAttributes();
    }
}
