<?php

namespace H2W;

use GuzzleHttp\Exception\RequestException;
use H2W\API\HubSpot;
use H2W\API\WordPress;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\Output;

class Import
{
    /**
     * @var HubSpot
     */
    private $hubSpot;
    /**
     * @var WordPress
     */
    private $wordPress;
    /**
     * @var PostImporter
     */
    private $postImporter;
    /**
     * @var Output
     */
    private $output;

    public function __construct(HubSpot $hubSpot, WordPress $wordPress, PostImporter $postImporter)
    {
        $this->hubSpot      = $hubSpot;
        $this->wordPress    = $wordPress;
        $this->postImporter = $postImporter;
    }

    public function handle($blogId, $output, $offset = 0, $dryRun = false, $verbose = false)
    {
        $this->hubSpot->setBlogId($blogId);
        $this->output = $output;
        WordPress::setDryRun($dryRun);
        $this->hubSpot->setVerbose($verbose);
        $this->wordPress->setVerbose($verbose);

        try {
            $this->output->writeln('Loading existing WordPress Users...');
            $this->wordPress->loadUsers();
            $this->output->writeln('Loading existing WordPress Categories...');
            $this->wordPress->loadCategories();
            $this->output->writeln('Loading existing WordPress Tags...');
            $this->wordPress->loadTags();
            $this->output->writeln('Loading HubSpot Topics...');
            $this->hubSpot->loadTopics();

            $this->importTopics();
            $this->importPosts($offset);
        } catch (\Exception $e) {
            throw new \Exception('Unable to complete import', $e->getCode(), $e);
        }
    }

    protected function importTopics()
    {
        $topics = $this->hubSpot->getTopics();
        $bar    = $this->output->createProgressBar(count($topics));
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%item%\n");
        $bar->setMessage('Importing Tags...');
        $bar->setMessage('', 'item');
        $bar->start();

        $requests = (function () use ($topics, $bar) {
            foreach ($topics as $topic) {
                $bar->setMessage($topic->name, 'item');
                if ($this->wordPress->getTag($topic->slug)) {
                    $bar->advance();
                } else {
                    yield $this->wordPress->createTagAsync($topic->getWordPressAttributes());
                }
            }
        })();

        \GuzzleHttp\Promise\each_limit($requests, 25,
            function (ResponseInterface $response) use ($bar) {
                $tag = json_decode($response->getBody(), true);
                event('console.info', "Created tag {$tag['name']}");
                $this->wordPress->addCreatedTag($tag);
                $bar->advance();
            },
            function (RequestException $e, $idx) use ($topics, $bar) {
                if ($e->getCode() === 500 && $e->hasResponse()) {
                    $response = json_decode($e->getResponse()->getBody(), true);
                    if ($response['code'] === 'term_exists') {
                        event('console.error', "Tag already exists: {$topics[$idx]->name}");
                        $bar->advance();

                        return;
                    }
                }
                event('console.error', "Failed to create tag {$topics[$idx]->name}: $e");
                $bar->advance();
            })->wait();

        $bar->setMessage("Complete.\n", 'item');
        $bar->finish();
    }

    protected function importPosts($offset = 0)
    {
        $this->output->writeln('Fetching Posts...');
        $posts = $this->hubSpot->getPosts($offset);
        $bar   = $this->output->createProgressBar(count($posts));
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n%item%");
        $bar->setMessage('Importing Posts...');
        $bar->setMessage('', 'item');
        $bar->start();

        foreach ($posts as $post) {
            $bar->setMessage($post->name, 'item');
            $this->postImporter->import($post);
            $bar->advance();
        }
        $bar->setMessage("Complete.\n", 'item');
        $bar->finish();
    }
}
