<?php

namespace spec\H2W\API;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Stream;
use H2W\API\WordPress;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

class WordPressSpec extends ObjectBehavior
{
    function let(ClientInterface $guzzle, ResponseInterface $response, Stream $stream)
    {
        $stream->getContents()->willReturn('{}');
        $response->getBody()->willReturn($stream);
        $guzzle->request(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->willReturn($response);
        $guzzle->getConfig(Argument::type('string'))->willReturn([]);

        $this->beConstructedWith($guzzle);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WordPress::class);
    }

    function it_should_load_existing_categories()
    {
        $this->shouldNotThrow('Exception')->duringLoadCategories();
    }

    function it_should_load_existing_tags()
    {
        $this->loadTags()->shouldNotThrow('Exception');
    }

    function it_should_create_a_user()
    {
        $attributes = [];

        $this->createUser($attributes)->shouldReturn($attributes);
    }

    function it_should_create_a_category($stream)
    {
        $name = "tagName";
        $stream->getContents()->willReturn("{\"name\": \"$name\"}");
        $this->createCategory($name)->shouldReturn(['name' => 'tagName']);
    }

    function it_should_create_a_tag()
    {
        $attributes = [];

        $this->createTag($attributes)->shouldReturn($attributes);
    }

    function it_should_create_a_post()
    {
        $attributes = [];
        $post       = [];

        $this->createPost($attributes)->shouldReturn($post);
    }

    function it_should_uploads_an_image()
    {
        $url = 'http://lorempixel.com/640/480';

        $this->createMedia($url)->shouldNotThrow();
    }
}
