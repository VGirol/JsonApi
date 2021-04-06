# JsonApi

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Infection MSI][ico-mutation]][link-mutation]
[![Total Downloads][ico-downloads]][link-downloads]

This package is still in development !

## Technologies

- PHP 7.3+
- PHPUnit 8.0+
- Laravel 7.0+

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require-dev": {
        "vgirol/jsonapi": "dev-master"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplified by using the following command:

```sh
composer require vgirol/jsonapi
```

### Registration

The package will automatically register itself.  
If you're not using Package Discovery, add the Service Provider to your config/app.php file:

```php
VGirol\JsonApi\JsonApiServiceProvider::class
```

### Configuration

You have to publish the 2 config files with:

```php
php artisan vendor:publish --provider="VGirol\JsonApi\JsonApiServiceProvider" --tag="config"
```

## Usage

### Quick start

Create Model, migration and seed.

```php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'xxx';
    protected $primaryKey = 'xxx';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'xxx'
    ];

    public function establishments()
    {
        return $this->hasMany('App\Models\Establishment', $this->getKeyName());
    }
}
```

For each model, create a form request extending `VGirol\JsonApi\Requests\ResourceFormRequest` abstract class.

```php
// app/Http/Request/CompanyFormRequest.php

namespace App\Http\Requests;

use VGirol\JsonApi\Requests\ResourceFormRequest;

class CompanyFormRequest extends ResourceFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}

```

Create route using `Route::jsonApiResource` macro.

```php
// routes/api.php

Route::jsonApiResource(
    'companies',        // Route name
);
```

Publish the config files (`jsonapi-alias.php` and `jsonapi.php`).

```sh
php artisan vendor:publish --provider="VGirol\JsonApi\JsonApiServiceProvider" --tag="config"
```

Fill the `jsonapi-alias.php` config file.

```php
// jsonapi-alias.php

return [

    'groups' => [
        [
            'type' => 'company',        // Resource type
            'route' => 'companies',     // Route name
            'model' => \App\Models\Company::class,
            'request' => \App\Http\Requests\CompanyFormRequest::class
        ],
        [
            //
        ]
    ]
];
```

That's all !

### Documentation

A user guide can be found [here](https://).

The API documentation is available in XHTML format at the url [http://jsonapi.girol.fr/docs/ref/index.html](http://jsonapi.girol.fr/docs/ref/index.html).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email [vincent@girol.fr](mailto:vincent@girol.fr) instead of using the issue tracker.

## Credits

- [Girol Vincent][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/VGirol/JsonApi.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/VGirol/JsonApi/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/VGirol/JsonApi.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/VGirol/JsonApi.svg?style=flat-square
[ico-mutation]: https://badge.stryker-mutator.io/github.com/VGirol/JsonApi/master
[ico-downloads]: https://img.shields.io/packagist/dt/VGirol/JsonApi.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/VGirol/JsonApi
[link-travis]: https://travis-ci.org/VGirol/JsonApi
[link-scrutinizer]: https://scrutinizer-ci.com/g/VGirol/JsonApi/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/VGirol/JsonApi
[link-downloads]: https://packagist.org/packages/VGirol/JsonApi
[link-author]: https://github.com/VGirol
[link-mutation]: https://infection.github.io
[link-contributors]: ../../contributors
