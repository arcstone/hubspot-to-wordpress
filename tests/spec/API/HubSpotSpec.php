<?php

namespace spec\H2W\API;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Stream;
use H2W\API\HubSpot;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

class HubSpotSpec extends ObjectBehavior
{
    function let(ClientInterface $guzzle)
    {
        $this->beConstructedWith($guzzle);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HubSpot::class);
    }

    function it_should_retrieve_the_list_of_blogs_from_hubspot($guzzle, ResponseInterface $response, Stream $stream)
    {
        // $blogsResponse = '{"total_count": 1, "objects": [{"allow_comments": false, "attached_stylesheets": [], "captcha_after_days": 7, "captcha_always": false, "close_comments_older": 0, "comment_date_format": "medium", "comment_form_guid": "25cb2ffe-327d-45a6-a69f-459ffe47ee08", "comment_max_thread_depth": "3", "comment_moderation": false, "comment_notification_emails": [], "comment_should_create_contact": true, "comment_verification_text": "your comment has been received.", "created": 1419018297000, "daily_notification_email_id": 2250249587, "default_group_style_id": "", "deleted_at": 0, "description": "Our public forum for shining light on the tech we use and the culture we love.", "domain": "", "domain_when_published": "www.arcstone.com", "email_api_subscription_id": null, "enable_google_amp_output": false, "enable_social_auto_publishing": true, "header": null, "html_footer": "", "html_footer_is_shared": true, "html_head": "", "html_head_is_shared": true, "html_keywords": [], "html_title": "ArcStone Blog", "id": 2250249427, "instant_notification_email_id": 2250249577, "item_layout_id": 2546805329, "item_template_is_shared": false, "item_template_path": "generated_layouts/2546805329.html", "language": "en_US", "legacy_guid": null, "legacy_module_id": null, "legacy_tab_id": null, "listing_layout_id": 2552760025, "listing_template_path": "generated_layouts/2552760025.html", "month_filter_format": "MMM yyyy", "monthly_notification_email_id": 2250249567, "name": "ArcStone Blog", "portal_id": 320867, "post_html_footer": "", "post_html_head": "", "posts_per_listing_page": 12, "posts_per_rss_feed": 10, "public_title": "Blog", "publish_date_format": "MMM \'\'yy", "root_url": "http://www.arcstone.com/blog", "rss_custom_feed": null, "rss_description": null, "rss_item_footer": null, "rss_item_header": null, "show_social_link_facebook": true, "show_social_link_google_plus": true, "show_social_link_linkedin": true, "show_social_link_twitter": true, "show_summary_in_emails": true, "show_summary_in_listing": true, "show_summary_in_rss": true, "slug": "blog", "social_account_twitter": "", "social_publishing_slug": null, "social_sharing_delicious": null, "social_sharing_digg": null, "social_sharing_email": null, "social_sharing_facebook_like": null, "social_sharing_facebook_send": null, "social_sharing_googlebuzz": null, "social_sharing_googleplusone": null, "social_sharing_linkedin": null, "social_sharing_reddit": null, "social_sharing_stumbleupon": null, "social_sharing_twitter": null, "social_sharing_twitter_account": null, "subscription_contacts_property": "blog_blog_subscription", "subscription_email_type": null, "subscription_form_guid": "baa4e1a2-703d-48d0-9d5b-88626103dae7", "subscription_lists_by_type": {"monthly": 197, "instant": 198, "daily": 199, "weekly": 200}, "updated": 1447426291000, "use_featured_image_in_summary": true, "weekly_notification_email_id": 2250249597}], "limit": 20, "offset": 0}';
        $blogsResponse = '{}';

        $stream->getContents()->willReturn($blogsResponse);
        $response->getBody()->willReturn($stream);
        $guzzle->request('GET', Argument::type('string'))->shouldBeCalled()->willReturn($response);

        $this->getBlogs()->shouldReturn([]);
    }
}
