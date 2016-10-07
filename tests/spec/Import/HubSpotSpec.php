<?php

namespace spec\H2W\Import;

use H2W\API\HubSpot as HubSpotAPI;
use H2W\Import\HubSpot;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HubSpotSpec extends ObjectBehavior
{
    function let(HubSpotAPI $hubSpot)
    {
        $this->beConstructedWith($hubSpot);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HubSpot::class);
    }
}
