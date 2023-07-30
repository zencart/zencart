<p align="center">
    <img src="https://user-images.githubusercontent.com/41773797/107574578-c4014600-6be6-11eb-8309-acca0acdd6f5.png" alt="Package banner" style="width: 100%; max-width: 800px;" />
</p>

This package allows you to convert token-based date formats between standards.

## Installation

You can use Composer to install this package into your application:

```
composer require danharrin/date-format-converter
```

## Usage

Use the `convert_date_format()` method to initialise a new instance of the converter, ready to use:

```php
convert_date_format('Y-m-d H:i:s')->to('day.js');
// YYYY-MM-DD HH:mm:ss

convert_date_format('Y-m-d H:i:s')->to('moment.js');
// YYYY-MM-DD HH:mm:ss
```

## Need Help?

🐞 If you spot a bug with this package, please [submit a detailed issue](https://github.com/danharrin/date-format-converter/issues/new), and wait for assistance.

🤔 If you have a question or feature request, please [start a new discussion](https://github.com/danharrin/date-format-converter/discussions/new).

🔐 If you discover a vulnerability within the package, please review our [security policy](https://github.com/danharrin/date-format-converter/blob/main/SECURITY.md).
