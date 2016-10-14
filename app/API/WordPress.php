<?php namespace H2W\API;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WordPress
 *
 * @package H2W\API
 */
class WordPress
{
    use GuzzleAPI;

    protected static $dryRun = false;

    protected $users;
    protected $categories;
    protected $tags;
    protected $media;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;

        foreach (['users', 'categories', 'tags', 'media'] as $holder) {
            $this->$holder = new Collection;
        }
    }

    public static function setDryRun($dryRun)
    {
        static::$dryRun = $dryRun;
    }

    public function loadUsers()
    {
        $this->users = $this->getUsers();
    }

    /**
     * @return Collection
     */
    public function getUsers() : Collection
    {
        return new Collection($this->request('users', ['context' => 'edit', 'per_page' => 100], 'GET', false));
    }

    public function getLoadedUsers()
    {
        return $this->users;
    }

    public function getUserId($id)
    {
        return $this->request("users/$id");
    }

    public function getUser(string $email)
    {
        return $this->users->where('email', $email)->first();
    }

    public function createUser($attributes)
    {
        if (static::$dryRun) {
            event('console.info', "Create User: {$attributes['name']}");
            $user = $attributes;
        } else {
            $user = $this->request('users', $attributes, 'POST');
        }

        $this->users->push($user);

        return $user;
    }

    /**
     * @param int $perPage
     * @param int $page
     * @return Collection
     */
    public function getPosts(int $perPage = 10, int $page = 1) : Collection
    {
        return new Collection($this->request('posts', ['per_page' => $perPage, 'page' => $page], 'GET', false));
    }

    public function getPost(int $id) : Model
    {
        $post = $this->request("posts/$id", [], 'GET', false);

        return new class($post) extends Model
        {
            protected static $unguarded = true;
        };
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function createPost(array $attributes) : array
    {
        if (static::$dryRun) {
            event('console.info', "Create post: {$attributes['title']}");

            return $attributes;
        }

        return $this->request('posts', $attributes, 'POST');
    }

    public function attachMediaToPost(array $post, array $medias)
    {
        $requests = (function () use ($post, $medias) {
            foreach ($medias as $media) {
                yield $this->guzzle->requestAsync('PUT', "media/{$media['id']}", [
                    'query' => array_merge($this->guzzle->getConfig('query') ?? [], [
                        'post' => $post['id'],
                    ]),
                    'debug' => $this->verbose,
                ]);
            }
        })();

        \GuzzleHttp\Promise\each_limit($requests, 8, function (ResponseInterface $response) {
            $media = json_decode($response->getBody(), true);
            event('console.info', "Attached media {$media['slug']} to post {$media['post']}");
        }, function ($reason) {
            event('console.error', "Failed to attach media: $reason");
        })->wait();
    }

    /**
     * @return Collection
     */
    public function getCategories() : Collection
    {
        return new Collection($this->request('categories', ['per_page' => 100], 'GET', false));
    }

    public function loadCategories()
    {
        $this->categories = $this->getCategories();
    }

    public function getCategory($name)
    {
        $category = $this->categories->where('name', trim($name))->first();

        return $category;
    }

    public function createCategory(string $name) : array
    {
        $category = ['name' => trim($name)];

        if (static::$dryRun) {
            event('console.info', "Create category: $name");
            $category['id'] = 1;
        } else {
            $category = $this->request('categories', $category, 'POST');
        }

        $this->categories->push($category);

        return $category;
    }

    public function loadTags()
    {
        $this->tags = $this->getTags();
    }

    public function getTags() : Collection
    {
        $perPage = 100;
        $tags    = new Collection;
        $page    = 1;

        do {
            $response = $this->request('tags', ['per_page' => $perPage, 'page' => $page], 'GET', false);
            $tags     = $tags->merge($response);
            $page++;
        } while (count($response) > 0);

        return $tags;
    }

    public function getTag($slug)
    {
        return $this->tags->where('slug', $slug)->first();
    }

    public function createTag(array $attributes) : array
    {
        if (static::$dryRun) {
            event('console.info', "Create tag: {$attributes['name']}");
            $tag = $attributes;
        } else {
            try {
                $tag = $this->request('tags', $attributes, 'POST');
            } catch (ClientException $e) {
                if ($e->getCode() === 500) {
                    $response = json_decode($e->getResponse()->getBody(), true);
                    if ($response->code === 'term_exists') {
                        event('console.info', "Tag already exists: {$attributes['name']}");

                        return $this->getTag($attributes['slug']);
                    }
                }
                throw $e;
            }
        }

        $this->tags->push($tag);

        return $tag;
    }

    public function createTagAsync(array $attributes)
    {
        return $this->guzzle->requestAsync('POST', 'tags', [
            'json' => $attributes,
            // 'debug' => true,
        ]);
    }

    public function addCreatedTag(array $tag)
    {
        event('console.debug', "Added new tag {$tag['name']} to collection");
        $this->tags->push($tag);
    }

    public function getCreatedTags()
    {
        return $this->tags;
    }

    public function getMedia(int $id)
    {
        try {
            return $this->request("media/$id", [], 'GET', false);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    public function createMedia(string $url) : array
    {
        if (empty($url) || filter_var($url, FILTER_VALIDATE_URL, ~FILTER_FLAG_SCHEME_REQUIRED) === false) {
            throw new \Exception("Invalid URL: $url");
        }

        if (static::$dryRun) {
            event('console.info', "Create media attachment: $url");
            $media = ['id' => null];
        } else {
            $file = $this->guzzle->request('GET', $url, ['headers' => null])->getBody()->getContents();

            try {
                $media = json_decode($this->guzzle->request('POST', 'media', [
                    'headers'   => [
                        'Content-Disposition' => 'attachment; filename="' . basename($url) . '"',
                        'content_md5'         => md5($file),
                    ],
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => $file,
                            'filename' => basename($url),
                        ],
                    ],
                ])->getBody()->getContents(), true);
            } catch (ServerException $e) {
                $response = json_decode($e->getResponse()->getBody(), true);
                event('console.error', ["Unable to add media: $url. Reason: {$response['message']}", $response]);

                return null;
            }
        }

        $media['originalUrl'] = $url;
        $this->media->push($media);

        return $media;
    }
}
