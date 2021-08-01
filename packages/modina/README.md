# Install

`composer require modina/modina`

## Automate module discovery after composer install

Add `"@php artisan modina:discover --ansi"` at the end of the `post-autoload-dump` in `composer.json`.

```json

// composer.json

{

    // ...

    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@php artisan modina:discover --ansi"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ]
    }

    // ...

}
```

# Usage

## Creating a new module

Lorem Epsom.
