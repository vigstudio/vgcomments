<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prefix for the routes
    |--------------------------------------------------------------------------
    |
    | This is the prefix for the routes that will be created by the package.
    |
    */
    'prefix' => 'vgcomments',

    /*
    |--------------------------------------------------------------------------
    | Name of Tables in Database
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be created by the migration and
    | used by the Comment model shipped with this package.
    |
    | "comments"    : Comments Table
    | "files"       : Files Attachment Table
    | "reactions"   : Reactions Table
    | "reports"     : Reports Table
    | "settings"    : Settings Table
    |
    */
    'table' => [
        'comments' => 'vgcomments',
        'files' => 'vgcomment_files',
        'reactions' => 'vgcomment_reactions',
        'reports' => 'vgcomment_reports',
        'settings' => 'vgcomment_settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection for the database
    |--------------------------------------------------------------------------
    |
    | This is the database connection that will be used by the migration and
    | the Comments model shipped with this package.
    |
    */
    'connection' => env('VCOMMENT_DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Broadcast
    |--------------------------------------------------------------------------
    |
    | This is the setting for enabling/disabling broadcast.
    |
    */
    'broadcast' => false,

    /*
    |--------------------------------------------------------------------------
    | Allow guests to comment
    |--------------------------------------------------------------------------
    |
    | This is the setting for allowing guests to comment.
    |
    */
    'allow_guests' => true,

    /*
    |--------------------------------------------------------------------------
    | Gravatar Settings Imageset
    |--------------------------------------------------------------------------
    |
    | This is the setting for gravatar imageset.
    | Set the imageset to use for avatars if no image is associated with the email hash, one of:
    | '404': do not load any image if none is associated with the email hash, instead return an HTTP 404 (File Not Found) response
    | 'mp': (mystery-person) a simple, cartoon-style silhouetted outline of a person (does not vary by email hash)
    | 'identicon': a geometric pattern based on an email hash
    | 'monsterid': a generated 'monster' with different colors, faces, etc
    | 'wavatar': generated faces with differing features and backgrounds
    | 'retro': awesome generated, 8-bit arcade-style pixelated faces
    | 'robohash': a generated robot with different colors, faces, etc
    | 'blank': a transparent PNG image (border added to HTML below for demonstration purposes)
    |
    */
    'gravatar' => true,
    'gravatar_imageset' => 'wavatar',

    /*
    |--------------------------------------------------------------------------
    | Validation Rules for Comments Content length
    |--------------------------------------------------------------------------
    |
    | This is the validation rules for the comments content length.
    | min_length: Minimum number of characters allowed in a comment
    | max_length: Maximum number of characters allowed in a comment
    |
    */
    'min_length' => 10,
    'max_length' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Throttle Settings for Comments requests (in minutes)
    |--------------------------------------------------------------------------
    |
    | This is the throttle settings for the comments requests.
    | max_rate: The maximum number of comment post attempts for delaying further attempts
    | per_minutes: Allow users to access a given request :throttle_max_rate times :throttle_per_minutes
    |
    */
    'throttle_max_rate' => 10,
    'throttle_per_minutes' => 1,

    /*
    |--------------------------------------------------------------------------
    | Comments must be moderated before being published
    |--------------------------------------------------------------------------
    |
    | This is the setting for comments must be moderated before being published.
    |
    */
    'moderation' => false,

    /*
    |--------------------------------------------------------------------------
    | Users Manager Comments
    |--------------------------------------------------------------------------
    |
    | This is the setting for users manager comments.
    | 'guard' => [user_id]
    |
    | Example:
    | 'web' => [1, 2, 3]
    | 'api' => [1, 2, 3]
    |
    */
    'moderation_users' => [
        'web' => [1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Keys
    |--------------------------------------------------------------------------
    |
    | This is the setting for moderation keys.
    | Every key is a word that will be held in the moderation queue.
    | If the comment contains one of the words declared in the array
    |
    */
    'moderation_keys' => [],

    /*
    |--------------------------------------------------------------------------
    | Spam Keys
    |--------------------------------------------------------------------------
    |
    | This is the setting for spam keys.
    | Every key is a word that will be marked as spam.
    | If the comment contains one of the words declared in the array
    |
    */
    'blacklist_keys' => [],

    /*
    |--------------------------------------------------------------------------
    | Censor
    |--------------------------------------------------------------------------
    |
    | This is the setting for censor.
    | 'censor'      :     Enable or disable the censor
    | 'censors_text':     Array of words to censor
    |
    | Every key is a word that will be censored.
    | If the comment contains one of the words declared in the array
    | The list is not case-sensitive. If you censor "foo", then "FOO" and "Foo" are also censored.
    | Jokers are accepted: * matches any number of letters or digits, ? matches one character exactly.
    | A single space matches any number of whitespace characters, meaning that censoring "b u g" will also censor "bug" or "b u g".
    | Censored words are replaced with **** unless a replacement is specified when the censored word is added to the list.
    |
    */
    'censor' => true,
    'censors_text' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Links in Comment
    |--------------------------------------------------------------------------
    |
    | This is the setting for max links in comment.
    |
    */
    'max_links' => 10,

    /*
    |--------------------------------------------------------------------------
    | Duplicate Comments's content
    |--------------------------------------------------------------------------
    |
    | This is the setting for duplicate comments's content.
    |
    */
    'duplicates_check' => false,

    /*
    |--------------------------------------------------------------------------
    | Report Comments's content
    |--------------------------------------------------------------------------
    |
    | This is the setting for report comments's content.
    | report_status: The status after the comment has to many reports: "pending" or "spam".
    | max_reports: The maximum number of reports before the comment is marked as "report_status".
    |
    */
    'report_status' => 'pending',
    'max_reports' => 5,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | This is the setting for filesystem disks.
    | disk_filesystem: The disk on which the files will be stored
    | "local"       : The local disk
    | "public"      : The public disk
    | "s3"          : The s3 disk
    | Setting the default disk name in the filesystems.php file
    |
    */
    'disk_filesystem' => config('filesystems.default', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Upload Rules
    |--------------------------------------------------------------------------
    |
    | This is the setting for upload rules.
    | "upload_rules"            : Rules for upload
    | "upload_rules_max"        : Maximum number of files to upload
    |
    */
    'upload_rules' => ['max:5120', 'mimes:doc,pdf,jpg,png,jpge,gif'],
    'upload_rules_max' => 5,

    /*
    |--------------------------------------------------------------------------
    | Column of User Table for get Data
    |--------------------------------------------------------------------------
    |
    | This is the setting for column of user table for get data.
    | "user_column_name"  : Column name for get name user
    | "user_column_email" : Column name for get email user
    | "user_column_url"   : Column name for get url user
    |
    */
    'user_column_name' => 'name',
    'user_column_email' => 'email',
    'user_column_url' => 'url',
    'user_column_avatar_url' => 'avatar_url',

    /*
    |--------------------------------------------------------------------------
    | Not Safe For Work
    |--------------------------------------------------------------------------
    |
    | This is the setting for not safe for work.
    | "nsfw" : Block NSFW
    | "nsfw_api" : Api form sightengine.com
    | "user" : User for api
    | "key" : Key for api
    |
    */
    'nsfw' => false,
    'nsfw_api_user' => '',
    'nsfw_api_key' => '',

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA
    |--------------------------------------------------------------------------
    |
    | This is the setting for reCAPTCHA.
    | "recaptcha" : Enable reCAPTCHA
    | "recaptcha_key" : Key for api
    | "recaptcha_secret" : Secret for api
    |
    */
    'recaptcha' => false,
    'recaptcha_key' => env('RECAPTCHA_KEY', ''),
    'recaptcha_secret' => env('RECAPTCHA_SECRET', ''),
];
