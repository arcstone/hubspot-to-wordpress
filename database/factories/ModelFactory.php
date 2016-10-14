<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(H2W\Models\BlogAuthor::class, function (Faker\Generator $faker) {
    $email = $faker->email;

    return [
        "username"     => explode('@', $email)[0],
        "bio"          => '',
        "twitter"      => '',
        "updated"      => $faker->unixTime * 1000,
        "linkedin"     => '',
        "facebook"     => '',
        "full_name"    => $faker->name,
        "deleted_at"   => 0,
        "slug"         => $faker->slug,
        "portal_id"    => $faker->randomNumber(6),
        "user_id"      => null,
        "created"      => $faker->unixTime * 1000,
        "gravatar_url" => 'https://app.hubspot.com/settings/avatar/' . $faker->md5(),
        "google_plus"  => '',
        "id"           => $faker->numberBetween(1000000000, mt_getrandmax()),
        "website"      => '',
        "avatar"       => '',
        "email"        => $email,
    ];
});

$factory->define(H2W\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'id'    => $faker->numberBetween(1000000000, mt_getrandmax()),
        'name'  => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(H2W\Models\Topic::class, function (Faker\Generator $faker) {
    return [
        'id'    => $faker->numberBetween(1000000000, mt_getrandmax()),
        'name'  => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(H2W\Models\Post::class, function (Faker\Generator $faker) {
    $blogAuthor = factory(\H2W\Models\BlogAuthor::class)->make();
    $title      = $faker->title;
    $url        = $faker->url;

    return [
        "analytics_page_id"                => $faker->numberBetween(1000000000, mt_getrandmax()),
        "archived"                         => false,
        "attached_stylesheets"             => [],
        "author_user_id"                   => null,
        "blog_author"                      => $blogAuthor->getAttributes(),
        "blog_author_id"                   => $blogAuthor->id,
        "blueprint_type_id"                => 0,
        "campaign"                         => null,
        "campaign_name"                    => null,
        "category_id"                      => 3,
        "cloned_from"                      => null,
        "comment_count"                    => 0,
        "compose_body"                     => null,
        "content_group_id"                 => $faker->numberBetween(1000000000, mt_getrandmax()),
        "created"                          => $faker->unixTime * 1000,
        "css"                              => [],
        "css_text"                         => "",
        "deleted_at"                       => 0,
        "deleted_by"                       => null,
        "domain"                           => "",
        "enable_domain_stylesheets"        => true,
        "enable_layout_stylesheets"        => true,
        "featured_image"                   => $faker->imageUrl(),
        "featured_image_alt_text"          => "",
        "flex_areas"                       => [],
        "folder_id"                        => null,
        "footer_html"                      => "",
        "freeze_date"                      => $faker->unixTime * 1000,
        "head_html"                        => "",
        "html_title"                       => $title,
        "id"                               => $faker->numberBetween(1000000000, mt_getrandmax()),
        "include_default_custom_css"       => null,
        "is_draft"                         => true,
        "is_instant_email_enabled"         => false,
        "is_social_publishing_enabled"     => true,
        "keywords"                         => [],
        "language"                         => null,
        "last_edit_session_id"             => null,
        "last_edit_update_id"              => null,
        "legacy_blog_tabid"                => null,
        "meta_description"                 => fake_html(),
        "meta_keywords"                    => null,
        "name"                             => $title,
        "page_expiry_date"                 => null,
        "page_expiry_enabled"              => null,
        "page_expiry_redirect_id"          => null,
        "page_expiry_redirect_url"         => null,
        "page_redirected"                  => null,
        "performable_url"                  => null,
        "personas"                         => [],
        "portal_id"                        => $faker->randomNumber(6),
        "post_body"                        => fake_html(),
        "post_summary"                     => fake_html(),
        "preview_image_src"                => null,
        "preview_key"                      => $faker->bothify('????????'),
        "processing_status"                => "",
        "publish_date"                     => $faker->unixTime * 1000,
        "publish_immediately"              => null,
        "published_url"                    => $url,
        "rss_body"                         => fake_html(),
        "rss_email_author_line_template"   => false,
        "rss_email_blog_image_max_width"   => null,
        "rss_email_by_text"                => "By",
        "rss_email_click_through_text"     => "Read more & raquo;",
        "rss_email_comment_text"           => "Comment & raquo;",
        "rss_email_entry_template"         => null,
        "rss_email_entry_template_enabled" => false,
        "rss_email_image_max_width"        => 0,
        "rss_summary"                      => fake_html(),
        "scheduled_update_date"            => null,
        "slug"                             => $faker->slug,
        "staged_from"                      => null,
        "state"                            => "PUBLISHED",
        "style_override_id"                => null,
        "subcategory"                      => "imported_blog_post",
        "tms_id"                           => null,
        "topic_ids"                        => function () {
            return factory(\H2W\Models\Topic::class, 3)->make()->pluck('id')->all();
        },
        "translated_from_id"               => null,
        "unpublished_at"                   => null,
        "updated"                          => $faker->unixTime * 1000,
        "url"                              => $url,
        "use_featured_image"               => true,
        "widget_containers"                => [],
        "widgets"                          => [],
    ];
});

function fake_html($maxDepth = 4, $maxWidth = 4)
{
    $dom = new DOMDocument();
    $dom->loadHTML(app(Faker\Generator::class)->randomHtml($maxDepth, $maxWidth));
    $html = '';
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
        $html .= $dom->saveHtml($childNode);
    }

    return $html;
}

