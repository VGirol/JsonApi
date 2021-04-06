<?php

namespace VGirol\JsonApi\Middleware;

use Closure;
use Exception;
use VGirol\JsonApi\Exceptions\JsonApi400Exception;
use VGirol\JsonApi\Services\ResponseService;

class CheckQueryParameters
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            // Parse query parameters
            $this->parseQueryParameters($request);
        } catch (Exception $e) {
            jsonapiError($e, false);

            return $this->responseService->createErrorResponse();
        }

        return $next($request);
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    private function parseQueryParameters($request)
    {
        $services = [
            'sort', 'filter', 'include', 'pagination', 'fields'
        ];
        foreach ($services as $serviceName) {
            $func = 'jsonapi' . ucfirst($serviceName);
            $service = $func($request);

            if (!$service->hasQuery($request)) {
                continue;
            }

            if (!$service->allowedByServer()) {
                throw new JsonApi400Exception(
                    constant(
                        'VGirol\JsonApi\Messages\Messages::ERROR_QUERY_PARAMETER_'
                            . strtoupper($serviceName)
                            . '_NOT_ALLOWED_BY_SERVER'
                    )
                );
            }

            if (!$service->allowedForRoute($request)) {
                throw new JsonApi400Exception(
                    constant(
                        'VGirol\JsonApi\Messages\Messages::ERROR_QUERY_PARAMETER_'
                            . strtoupper($serviceName)
                            . '_NOT_ALLOWED_FOR_ROUTE'
                    )
                );
            }
        }
    }
}
