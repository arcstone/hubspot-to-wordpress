<?php

namespace spec\H2W;

use H2W\API\HubSpot;
use H2W\API\WordPress;
use H2W\Import;
use H2W\Models\Category;
use H2W\Models\Post;
use H2W\Models\Topic;
use H2W\PostImporter;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportSpec extends ObjectBehavior
{
    function let(HubSpot $hubSpot, WordPress $wordPress, PostImporter $postImporter)
    {
        $this->beConstructedWith($hubSpot, $wordPress, $postImporter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Import::class);
    }

    function it_should_import_the_blog_posts(
        HubSpot $hubSpot,
        WordPress $wordPress,
        PostImporter $postImporter,
        Post $post,
        Category $category,
        Topic $topic,
        OutputStyle $output,
        ProgressBar $progressBar,
        ResponseInterface $response
    ) {
        $post->getWordPressAttributes()->willReturn([]);
        $post->getAttribute(Argument::type('string'))->willReturn();

        $category->getWordPressAttributes()->willReturn([]);

        $topic->getWordPressAttributes()->willReturn([]);
        $topic->getAttribute(Argument::type('string'))->willReturn('');

        $response->getBody()->willReturn('{"name":""}');

        $wordPress->createTagAsync(Argument::type('array'))->willReturn($response);
        $wordPress->setVerbose(Argument::type('bool'))->shouldBeCalled();
        $wordPress->loadUsers()->willReturn(new Collection);
        $wordPress->loadCategories()->willReturn(new Collection);
        $wordPress->loadTags()->willReturn(new Collection);
        $wordPress->getTag(Argument::type('string'))->willReturn([]);
        $wordPress->addCreatedTag(Argument::type('array'))->willReturn();

        $posts = new Collection([$post->getWrappedObject()]);

        $hubSpot->setBlogId(Argument::type('string'))->willReturn(null);
        $hubSpot->getPosts(Argument::type('integer'))->shouldBeCalled()->willReturn($posts);
        $hubSpot->getCategories()->willReturn(new Collection([$category->getWrappedObject()]));
        $hubSpot->getTopics()->shouldBeCalled()->willReturn(new Collection([$topic->getWrappedObject()]));
        $hubSpot->setVerbose(Argument::type('bool'))->shouldBeCalled();
        $hubSpot->loadTopics()->willReturn(new Collection);

        $progressBar->start()->willReturn();
        $progressBar->advance()->willReturn();
        $progressBar->finish()->willReturn();
        $progressBar->setFormat(Argument::type('string'))->willReturn();
        $progressBar->setMessage(Argument::cetera())->willReturn();

        $output->createProgressBar(Argument::type('integer'))->willReturn($progressBar);
        $output->writeln(Argument::type('string'))->shouldBeCalled();

        $this->shouldNotThrow(\Exception::class)->duringHandle('0123456789', $output);

        // $wordPress->createCategory(Argument::type('array'))->shouldHaveBeenCalled();
        // $wordPress->createTag(Argument::type('array'))->shouldHaveBeenCalled();
        $postImporter->import(Argument::type(Post::class))->shouldHaveBeenCalled();
    }
}
