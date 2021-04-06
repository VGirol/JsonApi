### Controllers

Each controller must use the `VGirol\JsonApi\Controllers\JsonApiRestFul` trait :

```php
// app/Http/Controllers/MyController.php

use VGirol\JsonApi\Controllers\JsonApiRestFul;
use Illuminate\Routing\Controller as BaseController;

class MyController extends BaseController
{
    use JsonApiRestFul;

    //
}
```

### FormRequests

Your form requests must extend the `VGirol\JsonApi\Request\ResourceFormRequest`

```php
// app/Http/Requests/MyFormRequest.php

use VGirol\JsonApi\Requests\ResourceFormRequest;

class MyFormRequest extends ResourceFormRequest
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
            ///
        ];
    }
}
```

### Resources

Your resources must extend the `VGirol\JsonApi\Resources\JsonApiResource`

```php
// app/Http/Resources/MyResource.php

use VGirol\JsonApi\Resources\JsonApiResource;

class MyResource extends JsonApiResource
{
    //
}
```

or the `VGirol\JsonApi\Resources\JsonApiResourceCollection`

```php
// app/Http/Resources/MyResourceCollection.php

use VGirol\JsonApi\Resources\JsonApiResourceCollection;

class MyResourceCollection extends JsonApiResourceCollection
{
    //
}
```

### Middleware

JsonApi package automatically registers a new middleware group called `jsonapi` with 2 middlewares :

- `VGirol\JsonApi\Middleware\CheckRequestHeaders` as `jsonapi.checkHeaders`
- `VGirol\JsonApi\Middleware\CheckQueryParameters` as `jsonapi.checkQuery`.

You may use the `middleware` method to assign this group to a route:

```php
// routes/api.php

Route::get('some/route', function () {
    //
})->middleware('jsonapi');
```

or

```php
// routes/api.php

Route::group(['middleware' => 'jsonapi'], function() {
    Route::get('some/route', 'SomeController@method');
});
```

### Exception handler

Your exception handler must extend the `VGirol\JsonApi\Exceptions\JsonApiHandler`

```php
// App/Exceptions/Handler.php

use VGirol\JsonApi\Exceptions\JsonApiHandler;

class Handler extends JsonApiHandler
{
    // nothing else
}
```
