<?php

namespace spec\H2W\Models;

use H2W\Models\Campaign;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CampaignSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Campaign::class);
    }
}
