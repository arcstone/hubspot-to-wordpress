<?php

namespace H2W\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected static $unguarded = true;

    public function getWordPressAttributes()
    {
        $post = [
            'date'                  => Carbon::createFromTimestamp($this->publish_date / 1000, 'America/Chicago')
                ->toRfc3339String(),
            'date_gmt'              => Carbon::createFromTimestampUTC($this->publish_date / 1000)->toRfc3339String(),
            // 'password'       => '',
            'slug'                  => $this->baseSlug($this->slug),
            'status'                => 'publish',
            'title'                 => $this->name,
            // 'content'        => $this->post_body,
            // 'author'         => 'id',
            // 'excerpt'        => '',
            // 'featured_media' => 'id', // The id of the featured media for the object.
            'comment_status'        => 'open',
            'ping_status'           => 'open',
            'format'                => 'standard',
            'sticky'                => false,
            // 'categories'     => [],
            // 'tags'                  => $this->topic_ids,
            '_yoast_wpseo_metadesc' => strip_tags($this->meta_description),
        ];

        return $post;
    }

    public function setBlogAuthorAttribute($blog_author)
    {
        $this->attributes['blog_author'] = $blog_author instanceof BlogAuthor ?
            $blog_author :
            new BlogAuthor($blog_author);
    }

    /**
     * Strip the leading permalink info HubSpot adds to the slug
     * e.g. blog/2015/12/actual-slug
     *
     * @param $slug
     * @return mixed
     */
    private function baseSlug($slug)
    {
        $matches = [];

        return preg_match('@^blog/\d{4}/\d{2}/(.+)(?:\?|$)@', $slug, $matches) === 1 ? $matches[1] : $slug;
    }
}
