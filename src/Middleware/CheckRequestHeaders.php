<?php

namespace VGirol\JsonApi\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use VGirol\JsonApi\Exceptions\JsonApi400Exception;
use VGirol\JsonApi\Exceptions\JsonApi406Exception;
use VGirol\JsonApi\Exceptions\JsonApi415Exception;
use VGirol\JsonApi\Exceptions\JsonApi500Exception;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Services\ResponseService;

class CheckRequestHeaders
{
    /**
     * Undocumented variable
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Class constructor.
     *
     * @param ResponseService $responseService
     *
     * @return void
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            // Content-Type header
            $this->checkContentTypeHeader($request);

            // Accept header
            $this->checkAcceptHeader($request);
        } catch (Exception $e) {
            jsonapiError($e, false);

            return $this->responseService->createErrorResponse();
        }

        return $next($request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return void
     */
    private function checkContentTypeHeader($request)
    {
        $mediaType = config('jsonapi.media-type');

        // Content-Type header
        if (!$request->hasHeader('Content-Type')) {
            throw new JsonApi400Exception(
                sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_MISSING, $mediaType)
            );
        }

        $contentType = $request->header('Content-Type');
        $matches = [];
        $count = preg_match_all('/' . preg_quote($mediaType, '/') . '[;]?(.*)/', $contentType, $matches);
        if ($count === false) {
            throw new JsonApi500Exception(
                Messages::ERROR_CONTENT_TYPE_HEADER_PARSING
            );
        }
        if ($count == 0) {
            throw new JsonApi400Exception(
                sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_BAD_MEDIA_TYPE, $mediaType)
            );
        } else {
            $param = $matches[1][0];
            if ($param != '') {
                throw new JsonApi415Exception(
                    sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_WITHOUT_PARAMETERS, $mediaType)
                );
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return void
     */
    private function checkAcceptHeader($request)
    {
        $mediaType = config('jsonapi.media-type');

        // Accept header
        if ($request->hasHeader('Accept')) {
            $accept = $request->header('Accept');
            $count = preg_match_all('/' . preg_quote($mediaType, '/') . '[;]?([^,]*)/', $accept, $matches);
            if ($count === false) {
                throw new JsonApi500Exception(
                    Messages::ERROR_ACCEPT_HEADER_PARSING
                );
            }
            if ($count != 0) {
                $check = false;
                for ($i = 0; $i < $count; $i++) {
                    $param = $matches[1][$i];
                    if ($param == '') {
                        $check = true;
                    }
                }
                if (!$check) {
                    throw new JsonApi406Exception(
                        sprintf(Messages::ERROR_ACCEPT_HEADER_WITHOUT_PARAMETERS, $mediaType)
                    );
                }
            }
        }
    }
}
