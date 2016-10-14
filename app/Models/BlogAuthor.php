<?php

namespace H2W\Models;

use Illuminate\Database\Eloquent\Model;

class BlogAuthor extends Model
{
    protected static $unguarded = true;

    public function getWordPressAttributes()
    {
        if (!$this->email) {
            throw new \Exception("Invalid user, no email address specified: " . $this->toJson());
        }

        return [
            // 'id'          => $this->id,
            'username'    => $this->username ?? $this->email,
            'name'        => $this->full_name,
            // 'first_name'         => '',
            // 'last_name'          => '',
            'email'       => $this->email,
            // 'url'                => '',
            // 'description'        => '',
            // 'link'               => '',
            // 'nickname'           => '',
            'slug'        => $this->slug,
            // 'registered_date'    => '',
            'roles'       => ['author'],
            'password'    => str_random(32),
            // 'capabilities'       => '',
            // 'extra_capabilities' => '',
            'avatar_urls' => $this->getGravatarUrls($this->gravatar_url),
        ];
    }

    protected function getGravatarUrls($url)
    {
        if (empty($url)) {
            return [];
        }

        if (preg_match('@https://app.hubspot.com/settings/avatar/[a-f0-9]{32}@i', $url) !== 1) {
            throw new \Exception('Invalid gravatar URL: ' . $url);
        }

        $baseUrl = str_replace('app.hubspot.com/settings', 'www.gravatar.com', $url);

        return [
            '24' => "$baseUrl?s=24&d=mm&r=g",
            '48' => "$baseUrl?s=48&d=mm&r=g",
            '96' => "$baseUrl?s=96&d=mm&r=g",
        ];
    }
}
