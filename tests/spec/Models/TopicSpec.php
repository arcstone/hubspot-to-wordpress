<?php

namespace spec\H2W\Models;

use H2W\Models\Topic;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TopicSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Topic::class);
    }

    function it_should_convert_a_hubspot_post_to_wordpress()
    {
        $this->getWordPressAttributes();
    }
}
