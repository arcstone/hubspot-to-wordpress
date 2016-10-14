<?php

namespace spec\H2W\API;

use GuzzleHttp\ClientInterface;
use H2W\API\HubSpot;
use Illuminate\Support\Collection;
use PhpSpec\ObjectBehavior;

class HubSpotSpec extends ObjectBehavior
{
    function let(ClientInterface $guzzle)
    {
        $this->beConstructedWith($guzzle);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HubSpot::class);
    }

    function it_should_retrieve_the_list_of_blogs_from_hubspot()
    {
        \Cache::shouldReceive('remember')
            ->once()
            ->with(stringValue(), integerValue(), callableValue())
            ->andReturn(['total_count' => 0, 'objects' => []]);
        $this->getBlogs()->shouldReturnAnInstanceOf(Collection::class);
    }

    function it_should_retrieve_the_list_of_topics()
    {
        \Cache::shouldReceive('remember')
            ->once()
            ->with(stringValue(), integerValue(), callableValue())
            ->andReturn(['total' => 0, 'objects' => []]);

        $this->getTopics()->shouldReturnAnInstanceOf(Collection::class);
    }

    function it_should_retrieve_the_list_of_posts()
    {
        \Cache::shouldReceive('remember')
            ->once()
            ->with(stringValue(), integerValue(), callableValue())
            ->andReturn(['total_count' => 0, 'objects' => []]);
        $this->getPosts()->shouldReturnAnInstanceOf(Collection::class);
    }

    function it_should_throw_if_api_returns_invalid_data()
    {
        \Cache::shouldReceive('remember')
            ->once()
            ->with(stringValue(), integerValue(), callableValue())
            ->andThrow('Exception');

        $this->shouldThrow(\Exception::class)->duringGetPosts();
    }
}
