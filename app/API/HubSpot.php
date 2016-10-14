<?php

namespace H2W\API;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use H2W\Models\Blog;
use H2W\Models\Category;
use H2W\Models\Post;
use H2W\Models\Topic;
use Illuminate\Support\Collection;

class HubSpot
{
    use GuzzleAPI {
        GuzzleAPI::request as guzzleRequest;
    }

    /**
     * @var string
     */
    protected $blogId;
    /**
     * @var Collection
     */
    protected $topics;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
        $this->topics = new Collection;
    }

    protected function request($route, $query = [], $method = 'GET', $cache = true)
    {
        $data = $this->guzzleRequest($route, $query, $method, $cache);

        if (!isset($data['objects'])) {
            throw new \Exception(static::class . ': Invalid data received from API: ' . print_r($data, true));
        }

        return $data;
    }

    public function getBlogs()
    {
        return $this->getCollection('/content/api/v2/blogs', Blog::class);
    }

    public function getCategories()
    {
        return $this->getCollection('categories', Category::class, ['blog_id' => $this->blogId]);
    }

    public function loadTopics()
    {
        $this->topics = $this->getTopics();
    }

    public function getTopics() : Collection
    {
        if (!$this->topics || $this->topics->count() === 0) {
            $this->topics = $this->getCollection('/blogs/v3/topics', Topic::class, ['blog_id' => $this->blogId], 'total');
        }

        return $this->topics;
    }

    public function getTopic(int $id)
    {
        if (!$topic = $this->topics->where('id', $id)->first()) {
            try {
                $topic = new Topic($this->guzzleRequest("/blogs/v3/topics/$id", [], 'GET', false));
            } catch (ClientException $e) {
                if ($e->getCode() === 404) {
                    event('console.error', "Topic not found with ID: $id");

                    return null;
                }

                throw $e;
            }
        }

        return $topic;
    }

    public function getPosts($offset = 0)
    {
        return $this->getCollection('/content/api/v2/blog-posts', Post::class, [
            'blog_id' => $this->blogId,
            'state'   => 'PUBLISHED',
            'offset'  => $offset,
        ]);
    }

    public function getPost(int $offset)
    {
        $posts = $this->request('/content/api/v2/blog-posts', [
            'blog_id' => $this->blogId,
            'state'   => 'PUBLISHED',
            'limit'   => 1,
            'offset'  => $offset,
        ], 'GET', false);

        return new Post($posts['objects'][0]);
    }

    public function setBlogId($blogId)
    {
        $this->blogId = $blogId;
    }
}
