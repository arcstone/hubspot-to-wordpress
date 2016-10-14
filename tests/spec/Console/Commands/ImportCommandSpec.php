<?php

namespace spec\H2W\Console\Commands;

use H2W\API\HubSpot;
use H2W\Console\Commands\ImportCommand;
use H2W\Import;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ImportCommandSpec extends ObjectBehavior
{
    function let(Import $import, HubSpot $hubSpot)
    {
        $this->beConstructedWith($import, $hubSpot);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImportCommand::class);
    }

    /* Can't spec this because of calls to parent \Illuminate\Console\Command
    function it_handles_the_command($import)
    {
        $import->handle()->shouldBeCalled();

        $this->handle('0123456789');
    }
    */
}
