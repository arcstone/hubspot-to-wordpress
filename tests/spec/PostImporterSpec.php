<?php

namespace spec\H2W;

use H2W\API\HubSpot;
use H2W\API\WordPress;
use H2W\Models\BlogAuthor;
use H2W\Models\Post;
use H2W\Models\Topic;
use H2W\PostImporter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PostImporterSpec extends ObjectBehavior
{
    function let(WordPress $wordPress, HubSpot $hubSpot)
    {
        $this->beConstructedWith($wordPress, $hubSpot);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PostImporter::class);
    }

    function it_should_import_a_post(WordPress $wordPress, HubSpot $hubSpot, Topic $topic)
    {
        $fakePost = factory(Post::class)->make();

        $wordPress->getUser(Argument::type('string'))->willReturn(['id' => 1]);
        $wordPress->createPost(Argument::type('array'))->willReturn([]);
        $wordPress->createMedia(Argument::type('string'))->willReturn([]);
        $wordPress->getTag(null)->willReturn(['id' => 1]);
        $wordPress->attachMediaToPost(Argument::type('array'), Argument::type('array'))->willReturn();

        $hubSpot->getTopic(Argument::type('integer'))->willReturn($topic);

        $this->import($fakePost);

        $wordPress->createPost(Argument::type('array'))->shouldHaveBeenCalled();
    }
}
