<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\ResponseService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\PhpunitException\SetExceptionsTrait;

class ResponseServiceTest extends TestCase
{
    use SetExceptionsTrait;

    /**
     * @test
     */
    public function createEmptyResponse()
    {
        $code = 204;

        $service = new ResponseService();

        $response = $service->createResponse($code);

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());
        PHPUnit::assertEmpty($response->getData(true));
    }

    /**
     * @test
     */
    public function createResponseFromResource()
    {
        $code = 200;

        $content = new JsonResource(null);
        $content->wrap('data');

        $service = new ResponseService();

        $response = $service->createResponse($code, $content);

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());

        $data = $response->getData(true);
        PHPUnit::assertIsArray($data);
        PHPUnit::assertArrayHasKey('data', $data);
        PHPUnit::assertIsArray($data['data']);
    }

    /**
     * @test
     */
    public function createResponseFromArray()
    {
        $code = 200;

        $content = ['data' => []];

        $service = new ResponseService();

        $response = $service->createResponse($code, $content);

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());

        $data = $response->getData(true);
        PHPUnit::assertIsArray($data);
        PHPUnit::assertArrayHasKey('data', $data);
        PHPUnit::assertIsArray($data['data']);
    }

    /**
     * @test
     */
    public function okResponse()
    {
        $code = 200;
        $content = ['data' => []];

        $service = new ResponseService();

        $response = $service->ok($content);

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());

        $data = $response->getData(true);
        PHPUnit::assertIsArray($data);
        PHPUnit::assertArrayHasKey('data', $data);
        PHPUnit::assertIsArray($data['data']);
    }

    /**
     * @test
     */
    public function createdResponse()
    {
        $code = 201;
        $content = ['data' => []];

        $service = new ResponseService();

        $response = $service->created($content);

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());

        $data = $response->getData(true);
        PHPUnit::assertIsArray($data);
        PHPUnit::assertArrayHasKey('data', $data);
        PHPUnit::assertIsArray($data['data']);
    }

    /**
     * @test
     */
    public function noContentResponse()
    {
        $code = 204;

        $service = new ResponseService();

        $response = $service->noContent();

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals($code, $response->getStatusCode());
        PHPUnit::assertEmpty($response->getData(true));
    }

    /**
     * @test
     */
    public function createErrorResponse()
    {
        jsonapiError()->add(400, 'error', false, false);

        $service = new ResponseService();

        $response = $service->createErrorResponse();

        PHPUnit::assertInstanceOf(JsonResponse::class, $response);
        PHPUnit::assertEquals(400, $response->getStatusCode());

        $data = $response->getData(true);
        PHPUnit::assertIsArray($data);
        PHPUnit::assertArrayHasKey('errors', $data);
        $errors = $data['errors'];
        PHPUnit::assertIsArray($errors);
        PHPUnit::assertCount(1, $errors);
        $error = $errors[0];
        PHPUnit::assertIsArray($error);
        PHPUnit::assertArrayHasKey('status', $error);
        PHPUnit::assertEquals(400, $error['status']);
        PHPUnit::assertArrayHasKey('details', $error);
    }

    /**
     * @test
     */
    // public function createResponse()
    // {
    //     // Creates an object with filled out fields
    //     $model = factory(Photo::class)->make();

    //     // Creates a resource
    //     $resource = ResourceObject::make($model);

    //     // Creates expected result
    //     $expected = (new HelperFactory())->document()
    //         ->setData((new HelperFactory())->resourceObject($model, 'photo', 'photos')->addSelfLink())
    //         ->addLink('self', request()->fullUrl())
    //         ->toArray();

    //     $code = 200;

    //     $service = new ResponseService();

    //     $response = $service->createResponse($code, $resource);

    //     PHPUnit::assertInstanceOf(JsonResponse::class, $response);
    //     PHPUnit::assertEquals($code, $response->getStatusCode());
    //     PHPUnit::assertEquals($expected, $response->getData(true));
    // }
}
