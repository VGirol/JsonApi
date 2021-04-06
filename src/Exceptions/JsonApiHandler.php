<?php

namespace VGirol\JsonApi\Exceptions;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use VGirol\JsonApi\Services\ResponseService;

class JsonApiHandler extends ExceptionHandler
{
    /**
     * Undocumented variable
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     * @param ResponseService $responseService
     *
     * @return void
     */
    public function __construct(Container $container, ResponseService $responseService)
    {
        parent::__construct($container);
        $this->responseService = $responseService;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {   // has Accept: application/json in request
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Undocumented function
     *
     * @param Request   $request
     * @param Throwable $exception
     *
     * @return JsonResponse
     */
    protected function handleApiException($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);
        jsonapiError()->addException($exception, false);

        return $this->responseService->createErrorResponse();
    }

    /**
     * Prepare exception for rendering.
     *
     * @param \Throwable $e
     *
     * @return \Throwable
     */
    protected function prepareException(Throwable $e)
    {
        $e = parent::prepareException($e);

        if ($e instanceof NotFoundHttpException) {
            $e = new JsonApi404Exception($e->getMessage(), 0, $e);
        }

        if (method_exists($e, 'prepareException')) {
            call_user_func([$e, 'prepareException']);
        }

        return $e;
    }
}
