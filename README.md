## HubSpot to WordPress Migrator

This package contains an Artisan console command used to migrate a blog from 
HubSpot to WordPress. It was written in PHP using Laravel Lumen.  

This script was a one-off project and will likely require some customization on
your end if you decide to use it. I don't plan to support it any further, but I
decided to throw the code up in case others may find it useful.

Note: This script is *slow*. Since I only needed to run it once, I didn't work
on performance at all. It took nearly two hours to import just under 1000 blog
posts, including downloading all linked images. YMMV.

### Requirements
PHP 7 is required to run the script, as well as the Tidy and XML Extensions.

The following WordPress plugins must be installed prior to running this script:

* WP-API 2.x http://v2.wp-api.org/

In order to support the meta_description field, you can optionally also install:

* Yoast SEO https://yoast.com/
* REST API SEO Fields https://calderawp.com/

Also note, in order to handle automated image uploads, I had to add this to my 
wp-config.php file:

    define('ALLOW_UNFILTERED_UPLOADS', true);
    
This allows uploading images that don't necessarily end in a traditional image
file extension.  You should remove this after completing the migration.

### Configuration

Copy the `.env.example` file to `.env` and edit it to set your HubSpot HAPI key, 
WordPress username, password, and base URL.


### Usage

    php artisan import:hubspot [blogId] [--offset=n]

If you don't specify a blogId, the script will list all the blogs available in 
your account and allow you to choose one.

You can also specify an offset, in case the migration gets interrupted.

### License

GNU GPL 3.0

