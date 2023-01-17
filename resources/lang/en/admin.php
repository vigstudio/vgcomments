<?php

return [
    'dashboard' => 'Dashboard',
    'comments_table' => 'Comments Admin Table',
    'author_name' => 'Author Name',
    'author_email' => 'Author Email',
    'author_ip' => 'Author Ip',
    'user_agent' => 'User Agent',
    'url' => 'URL',
    'content' => 'Content',
    'status' => 'Status',
    'view_page' => 'View Page',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'deleted_at' => 'Deleted At',
    'add' => 'Add',
    'true' => 'True',
    'false' => 'False',
    'select_tab' => 'Select a tab',

    'general' => 'General',
    'moderation' => 'Moderation',
    'protection' => 'Protection',
    'all' => 'All',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'reported' => 'Reported',
    'spam' => 'Spam',
    'trash' => 'Trash',
    'deleted' => 'Deleted',
    'unapprove' => 'Unapprove',

    'edit' => 'Edit',
    'delete' => 'Delete',

    'setting' => 'Setting',

    'prefix_label' => 'Prefix Route',
    'prefix_description' => 'This is the prefix for the routes that will be created by the package.',

    'allow_guests_label' => 'Allow Guests',
    'allow_guests_description' => 'Allow guests to post comments.',

    'min_length_label' => 'Min Length Content',
    'min_length_description' => 'Minimum length of the comment content.',

    'max_length_label' => 'Max Length Content',
    'max_length_description' => 'Maximum length of the comment content.',

    'throttle_max_rate_label' => 'Throttle Max Rate',
    'throttle_max_rate_description' => 'The maximum number of comment post attempts for delaying further attempts',

    'throttle_per_minutes_label' => 'Throttle Per Minutes',
    'throttle_per_minutes_description' => 'Allow users to access a given route for a given number of times per minute.',

    'moderation_label' => 'Moderation Comments Before Published',
    'moderation_description' => 'This is the setting for comments must be moderated before being published.',

    'moderation_keys_label' => 'Moderation Keys',
    'moderation_keys_description' => 'Every key is a word that will be held in the moderation queue. If the comment contains one of the words declared (One word per line).',

    'blacklist_keys_label' => 'Blacklist Keys',
    'blacklist_keys_description' => 'Every key is a word that will be held in the blacklist queue. If the comment contains one of the words declared (One word per line).',

    'censor_label' => 'Censor Check Enable',
    'censor_description' => 'Enable the censor check for the comment content.',

    'censors_text_label' => 'Censors Text (One word per line)',
    'censors_text_description' => 'Every key is a word that will be held in the censor queue. If the comment contains one of the words declared (One word per line).',

    'max_links_label' => 'Max Links Per Comment',
    'max_links_description' => 'Maximum number of links allowed in a comment.',

    'duplicates_check_label' => 'Duplicates Check (By Content)',
    'duplicates_check_description' => 'Enable the duplicates check for the comment content.',

    'report_status_label' => 'Report Status (When Max Reports)',
    'report_status_description' => 'This is the setting for comments must be reported before being published.',

    'max_reports_label' => 'Max Reports (When Report Status)',
    'max_reports_description' => 'Maximum number of reports allowed in a comment.',

    'disk_filesystem_label' => 'Disk Filesystem (Uploads)',
    'disk_filesystem_description' => 'This is the disk filesystem for the uploads that will be created by the package.',

    'upload_rules_label' => 'Upload Rules (Uploads)',
    'upload_rules_description' => 'This is the upload rules for the uploads that will be created by the package. (One rule per line)',

    'upload_rules_max_label' => 'Upload Rules Max (Uploads)',
    'upload_rules_max_description' => 'This is the upload rules max for the uploads that will be created by the package.',

    'user_column_name_label' => 'Name Attribute or Column (User Model)',
    'user_column_name_description' => 'This is the name attribute or column for the user model that will use the package.',

    'user_column_email_label' => 'Email Attribute or Column (User Model)',
    'user_column_email_description' => 'This is the email attribute or column for the user model that will use the package.',

    'user_column_url_label' => 'Url Attribute or Column (User Model)',
    'user_column_url_description' => 'This is the url attribute or column for the user model that will use the package.',

    'user_column_avatar_url_label' => 'Avatar URL Attribute or Column (User Model)',
    'user_column_avatar_url_description' => 'This is the avatar url attribute or column for the user model that will use the package.',

    'nsfw_label' => 'NSFW (Not Safe For Work)',
    'nsfw_description' => 'Enable the NSFW (Not Safe For Work) for the comment content.',

    'nsfw_api_user_label' => 'NSFW API User',
    'nsfw_api_user_description' => 'This is the NSFW API User for the NSFW that will be created by the package.',

    'nsfw_api_key_label' => 'NSFW API Key',
    'nsfw_api_key_description' => 'This is the NSFW API Key for the NSFW that will be created by the package.',

    'recaptcha_label' => 'Recaptcha V3 (Google)',
    'recaptcha_description' => 'Enable the Recaptcha V3 (Google) for the comment content.',

    'recaptcha_key_label' => 'Recaptcha Key V3',
    'recaptcha_key_description' => 'API key for the Recaptcha V3 (Google) that will be created by the package.',

    'recaptcha_secret_label' => 'Recaptcha Secret V3',
    'recaptcha_secret_description' => 'API secret for the Recaptcha V3 (Google) that will be created by the package.',

    'gravatar_label' => 'Gravatar Enable',
    'gravatar_description' => 'Enable the Gravatar for the comment content.',

    'gravatar_imageset_label' => 'Gravatar Imageset',
    'gravatar_imageset_description' => 'This is the Gravatar Imageset for the Gravatar that will be created by the package.',
];
