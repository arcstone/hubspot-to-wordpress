<?php

namespace H2W;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use H2W\API\HubSpot;
use H2W\API\WordPress;
use H2W\Models\BlogAuthor;
use H2W\Models\Media;
use H2W\Models\Post;

class PostImporter
{
    protected $attributes;
    private   $featuredMediaId = null;
    protected $media           = [];

    /**
     * @var WordPress
     */
    private $wordPress;
    /**
     * @var HubSpot
     */
    private $hubSpot;

    public function __construct(WordPress $wordPress, HubSpot $hubSpot)
    {
        $this->wordPress = $wordPress;
        $this->hubSpot   = $hubSpot;
    }

    public function getWordPress()
    {
        return $this->wordPress;
    }

    public function import(Post $post)
    {
        $this->attributes      = $post->getWordPressAttributes();
        $attributes            = &$this->attributes;
        $attributes['author']  = $this->getAuthor($post->blog_author);
        $attributes['excerpt'] = $this->getPostBody($post->post_summary, $post->id, $post->featured_image);
        $attributes['content'] = $this->getPostBody($post->post_body, $post->id, $post->featured_image);

        if ($this->featuredMediaId) {
            $attributes['featured_media'] = $this->featuredMediaId;
        } elseif ($post->featured_image) {
            if ($media = $this->upload($post->featured_image, $post->id)) {
                $attributes['featured_media'] = $media['id'];
            }
        }

        $attributes['tags']       = $this->getTags($post->topic_ids);
        $attributes['categories'] = $this->getCategories($post->campaign_name);

        $post = $this->wordPress->createPost($attributes);
        $this->wordPress->attachMediaToPost($post, $this->media);
        $this->media = [];
    }

    protected function getAuthor(BlogAuthor $blogAuthor)
    {
        try {
            if (!$user = $this->wordPress->getUser($blogAuthor->email)) {
                $user = $this->wordPress->createUser($blogAuthor->getWordPressAttributes());
            }
        } catch (RequestException $e) {
            event('console.error', [
                "Unable to create user $blogAuthor->email: $e->getMessage()",
                ['user' => $blogAuthor->getWordPressAttributes(), 'exception' => $e],
            ]);

            throw $e;
        }

        return $user['id'];
    }

    /**
     * Extract all IMG tags from HTML, add files to library, and replace in the URL
     *
     * @param string $value
     * @return mixed
     */
    protected function getPostBody($value, $postId, $featured = null)
    {
        if (empty($value)) {
            return null;
        }

        $dom = $this->tidy($value);

        // Find all images
        $images = $dom->getElementsByTagName('img');
        if ($images->length) {
            foreach ($images as $img) {
                $url = $img->getAttribute('src');

                // Data URLs don't need to be saved
                if (starts_with($url, 'data:image')) {
                    continue;
                }

                try {
                    if (!$attachment = $this->upload($url, $postId)) {
                        continue;
                    }

                    if ($url === $featured) {
                        $this->featuredMediaId = $attachment['id'];
                    }
                } catch (\Exception $e) {
                    render_console_exception($e);
                    continue;
                }

                if ($attachment['source_url']) {
                    $img->setAttribute('src', $attachment['source_url']);
                }
            }
        }

        return $dom->saveHTML();
    }

    protected function getCategories(string $categoryName = null) : array
    {
        if (empty($categoryName)) {
            return [];
        }

        if (!$category = $this->wordPress->getCategory($categoryName)) {
            $category = $this->wordPress->createCategory($categoryName);
        }

        return [$category['id']];
    }

    protected function upload($url, $postId)
    {
        if (!$media = $this->getMedia($url, $postId)) {
            try {
                $media = $this->wordPress->createMedia($url);

                // Store media id original URL mapping
                Media::create([
                    'id'           => $media['id'],
                    'post_id'      => $postId,
                    'original_url' => $url,
                ]);

                $this->media[] = $media;
            } catch (ClientException $e) {
                if ($e->getCode() === 404) {
                    event('console.error', "File not found: $url in post: {$this->attributes['title']}");

                    return null;
                }
                throw $e;
            } catch (\Throwable $e) {
                event('console.error', ["Error downloading URL: $url", $e]);

                return null;
            }
        }

        if (!is_array($media)) {
            throw new \Exception("Bad media: $url in post: {$this->attributes['title']}");
        }

        return $media;
    }

    protected function getMedia(string $url, int $postId)
    {
        if ($media = Media::where('post_id', $postId)->where('original_url', $url)->first()) {
            event('console.info', "URL already downloaded for post $postId: $url");

            return $this->wordPress->getMedia($media->id);
        }

        return null;
    }

    /**
     * @param $value
     * @return \DOMDocument
     */
    protected function tidy($value)
    {
        if (empty($value)) {
            return null;
        }

        $tidyConfig = [
            'clean'               => true,
            'doctype'             => 'html5',
            'drop-empty-elements' => false,
            'gdoc'                => true,
            'merge-divs'          => false,
            'merge-emphasis'      => false,
            'merge-spans'         => false,
            'output-html'         => true,
            'preserve-entities'   => true,
            'show-body-only'      => true,
            'vertical-space'      => false,
            'wrap'                => 0,
        ];

        $tidy = tidy_parse_string($value, $tidyConfig, 'UTF8');
        $tidy->cleanRepair();
        $value = $tidy->value;

        // Note: this must be set after the above purifier call
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument;

        $dom->validateOnParse    = true;
        $dom->preserveWhiteSpace = false;

        // Parse HTML and display any errors
        try {
            // Make sure DOMDocument knows the input is utf-8 encoded
            $cleaned = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
            $dom->loadHTML($cleaned, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            foreach (libxml_get_errors() as $error) {
                $level = '';
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        event('console.line', 'Warning: ' . $error->message);
                        continue 2;
                    case LIBXML_ERR_ERROR:
                        $level = 'Error';
                        break;
                    case LIBXML_ERR_FATAL:
                        $level = 'Fatal';
                        break;
                }

                // Ignore XML_HTML_UNKNOWN_TAG errors for HTML5 tags
                $html5Tags = [
                    'address',
                    'article',
                    'aside',
                    'audio',
                    'canvas',
                    'del',
                    'figcaption',
                    'figure',
                    'footer',
                    'header',
                    'hgroup',
                    'ins',
                    'mark',
                    'meter',
                    'nav',
                    's',
                    'section',
                    'source',
                    'sub',
                    'sup',
                    'time',
                    'var',
                    'video',
                    'wbr',
                ];

                if ($error->code === 801 &&
                    preg_match('/Tag (' . join('|', $html5Tags) . ') invalid/', $error->message) === 1
                ) {
                    continue;
                }

                render_console_exception(new \Exception(
                    "$level: $error->code: $error->message Line: $error->line, Column: $error->column",
                    $error->code
                ));
                $code = explode("\n", $cleaned);
                event('console.comment', $code[$error->line - 1]);
            }
            libxml_clear_errors();
        } catch (\Exception $e) {
            render_console_exception($e);
        }

        return $dom;
    }

    private function getTags(array $topicIds)
    {
        return array_map(function ($topicId) {
            if (!$topic = $this->hubSpot->getTopic($topicId)) {
                throw new \Exception("Topic ID $topicId not found!");
            }

            if (!$tag = $this->wordPress->getTag($topic->slug)) {
                $tag = $this->wordPress->createTag($topic->getWordPressAttributes());
            }

            return $tag['id'];
        }, $topicIds);
    }
}
