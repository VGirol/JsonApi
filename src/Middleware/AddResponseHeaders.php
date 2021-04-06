<?php

namespace VGirol\JsonApi\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use VGirol\JsonApi\Messages\Messages;

class AddResponseHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $response = $next($request);

        if (!$response->isEmpty()) {
            $mediaType = config('jsonapi.media-type');

            if ($response->headers->has('Content-Type')) {
                $header = $response->headers->get('Content-Type');
                if (!Str::contains($header, $mediaType)) {
                    Log::warning(
                        sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_ALLREADY_SET, $mediaType)
                    );
                }
                $headers = explode(';', $header);
                if (count($headers) > 1) {
                    Log::warning(
                        sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_WITHOUT_PARAMETERS, $mediaType)
                    );
                }
            }

            $response->header('Content-Type', $mediaType);
        }

        return $response;
    }
}
