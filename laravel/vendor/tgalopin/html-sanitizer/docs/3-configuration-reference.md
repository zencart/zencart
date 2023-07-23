# Configuration reference

Here is the configuration default values with annotations describing the specific configuration keys:

```php
$sanitizer = HtmlSanitizer\Sanitizer::create([

    /*
     * Maximum length in number of characters this sanitizer will accept as inputs.
     * Longer inputs will be truncated.
     *
     * This field is necessary to prevent a Denial of Service attack induced by extremely
     * long HTML inputs from users.
     */
    'max_input_length' => 20000,

    /*
     * List of extensions to enable on this sanitizer.
     */
    'extensions' => ['basic', 'list', 'table', 'image', 'code', 'iframe', 'details', 'extra'],

    /*
     * Configuration for specific tags.
     */
    'tags' => [
        'abbr' => [
            'allowed_attributes' => [],
        ],
        'a' => [
            'allowed_attributes' => ['href', 'title'],
            
            /*
             * If an array is provided, links targeting other hosts than one in this array
             * will be disabled (the `href` attribute will be blank). This can be useful if you want
             * to prevent links targeting external websites. Keep null to allow all hosts.
             * Any allowed domain also includes its subdomains.
             *
             * Example:
             *      'allowed_hosts' => ['trusted1.com', 'google.com'],
             */
            'allowed_hosts' => null,
            
            /*
             * If false, all links containing a mailto target will be disabled (the `href` attribute
             * will be blank).
             */
            'allow_mailto' => true,
            
            /*
             * If true, all links targets using the HTTP protocol will be rewritten to use HTTPS instead.
             */
            'force_https' => false,
        ],
        'blockquote' => [
            'allowed_attributes' => [],
        ],
        'br' => [
            'allowed_attributes' => [],
        ],
        'caption' => [
            'allowed_attributes' => [],
        ],
        'code' => [
            'allowed_attributes' => [],
        ],
        'dd' => [
            'allowed_attributes' => [],
        ],
        'del' => [
            'allowed_attributes' => [],
        ],
        'details' => [
            'allowed_attributes' => ['open'],
        ],
        'div' => [
            'allowed_attributes' => [],
        ],
        'dl' => [
            'allowed_attributes' => [],
        ],
        'dt' => [
            'allowed_attributes' => [],
        ],
        'em' => [
            'allowed_attributes' => [],
        ],
        'figcaption' => [
            'allowed_attributes' => [],
        ],
        'figure' => [
            'allowed_attributes' => [],
        ],
        'h1' => [
            'allowed_attributes' => [],
        ],
        'h2' => [
            'allowed_attributes' => [],
        ],
        'h3' => [
            'allowed_attributes' => [],
        ],
        'h4' => [
            'allowed_attributes' => [],
        ],
        'h5' => [
            'allowed_attributes' => [],
        ],
        'h6' => [
            'allowed_attributes' => [],
        ],
        'hr' => [
            'allowed_attributes' => [],
        ],
        'iframe' => [
            'allowed_attributes' => ['src', 'width', 'height', 'frameborder', 'title', 'allow', 'allowfullscreen'],
        
            /*
             * If an array is provided, iframes relying on other hosts than one in this array
             * will be disabled (the `src` attribute will be blank). This can be useful if you want
             * to prevent iframes contacting external websites.
             * Any allowed domain also includes its subdomains.
             *
             * Example:
             *      'allowed_hosts' => ['trusted1.com', 'google.com'],
             */
            'allowed_hosts' => null,
            
            /*
             * If true, all frames URLS using the HTTP protocol will be rewritten to use HTTPS instead.
             */
            'force_https' => false,
        ],
        'img' => [
            'allowed_attributes' => ['src', 'alt', 'title'],
            
            /*
             * If an array is provided, images relying on other hosts than one in this array
             * will be disabled (the `src` attribute will be blank). This can be useful if you want
             * to prevent images contacting external websites. Keep null to allow all hosts.
             * Any allowed domain also includes its subdomains.
             *
             * Example:
             *      'allowed_hosts' => ['trusted1.com', 'google.com'],
             */
            'allowed_hosts' => null,
            
            /*
             * If true, images data-uri URLs will be accepted.
             */
            'allow_data_uri' => false,
            
            /*
             * If true, all images URLs using the HTTP protocol will be rewritten to use HTTPS instead.
             */
            'force_https' => false,
        ],
        'i' => [
            'allowed_attributes' => [],
        ],
        'li' => [
            'allowed_attributes' => [],
        ],
        'ol' => [
            'allowed_attributes' => [],
        ],
        'pre' => [
            'allowed_attributes' => [],
        ],
        'p' => [
            'allowed_attributes' => [],
        ],
        'q' => [
            'allowed_attributes' => [],
        ],
        'rp' => [
            'allowed_attributes' => [],
        ],
        'rt' => [
            'allowed_attributes' => [],
        ],
        'ruby' => [
            'allowed_attributes' => [],
        ],
        'small' => [
            'allowed_attributes' => [],
        ],
        'span' => [
            'allowed_attributes' => [],
        ],
        'strong' => [
            'allowed_attributes' => [],
        ],
        'sub' => [
            'allowed_attributes' => [],
        ],
        'summary' => [
            'allowed_attributes' => [],
        ],
        'sup' => [
            'allowed_attributes' => [],
        ],
        'table' => [
            'allowed_attributes' => [],
        ],
        'tbody' => [
            'allowed_attributes' => [],
        ],
        'td' => [
            'allowed_attributes' => [],
        ],
        'tfoot' => [
            'allowed_attributes' => [],
        ],
        'thead' => [
            'allowed_attributes' => [],
        ],
        'th' => [
            'allowed_attributes' => [],
        ],
        'tr' => [
            'allowed_attributes' => [],
        ],
        'u' => [
            'allowed_attributes' => [],
        ],
        'ul' => [
            'allowed_attributes' => [],
        ],
    ],
]);
```
