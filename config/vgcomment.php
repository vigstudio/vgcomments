<?php

return [
    'prefix' => 'vgcomments',

    /*
     * This is the name of the table that will be created by the migration and used by the Comment model shipped with this package.
     * comments: Comments Table
     * files : Files Attachment Table
     * reactions: Reactions Table
     * reports: Reports Table
     * settings: Settings Table
     */
    'table' => [
        'comments' => 'vgcomments',
        'files' => 'vgcomment_files',
        'reactions' => 'vgcomment_reactions',
        // 'reports' => 'vgcomment_reports',
        // 'settings' => 'vgcomment_settings',
    ],

    /*
     * This is the database connection that will be used by the migration and
     * the Comments model shipped with this package.
     */
    'connection' => env('VCOMMENT_DB_CONNECTION', 'mysql'),

    /**
     * Allow guests to comment
     */
    'allow_guests' => true,

    /**
     * Minium number of characters allowed in a comment
     * Maximum number of characters allowed in a comment
     */
    'min_length' => 10,
    'max_length' => 1000,

    /**
     * The maximum number of comment post attempts for delaying further attempts
     * Allow users to access a given request :max_rate times :per_minutes
     */
    'throttle' => [
        'max_rate' => 10,
        'per_minutes' => 1,
    ],

    /**
     * Comments must be moderated before being published
     */
    'moderation' => false,

    /**
     * When a comment contains one of the words declared in the array
     * Will be held in the moderation queue.
     */
    'moderation_keys' => [],

    /**
     * When a comment contains one of the words declared in the array
     * Will be marked as spam.
     */
    'blacklist_keys' => [],

    /**
     * Scan for sensitive words in comments
     */
    'censor' => true,
    /**
     * The list is not case-sensitive. If you censor "foo", then "FOO" and "Foo" are also censored.
     * Jokers are accepted: * matches any number of letters or digits, ? matches one character exactly.
     * A single space matches any number of whitespace characters, meaning that censoring "b u g" will also censor "bug" or "b u g".
     * Censored words are replaced with **** unless a replacement is specified when the censored word is added to the list.
     */
    'censors_text' => [],

    'max_links' => 10,
    'duplicates_check' => false,

    /**
     * Filesystem Disks
     * filesystems.php
     */
    'disk_filesystem' => config('filesystems.default', 'local'),

    /**
     * Rules Upload File or Image
     * rules: Rules for upload
     * max: Maximum number of files to upload
     */
    'upload_rules' => [
        'rules' => ['max:5120', 'mimes:doc,pdf,jpg,png,jpge,gif'],
        'max' => 5,
    ],

    /**
     * Column of User Table for get Data
     */
    'user_column_name' => 'name',
    'user_column_email' => 'email',
    'user_column_url' => 'url',
];
