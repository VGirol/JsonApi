<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Exception;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApi400Exception;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Services\ExceptionService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\PhpunitException\SetExceptionsTrait;

class ExceptionServiceTest extends TestCase
{
    use SetExceptionsTrait;

    /**
     * @test
     */
    public function constructService()
    {
        $service = new ExceptionService();

        PHPUnit::assertEquals(0, $service->count());
        PHPUnit::assertFalse($service->hasErrors());

        $errors = $service->errors();
        PHPUnit::assertInstanceOf(Collection::class, $errors);
        PHPUnit::assertEquals(0, $errors->count());
    }

    /**
     * @test
     */
    public function addException()
    {
        config()->set('jsonapi.stopAtFirstError', true);

        $service = new ExceptionService();

        $exception = new Exception('test');

        $this->setFailure(Exception::class, 'test');

        $obj = $service->addException($exception);

        PHPUnit::assertSame($obj, $service);
        PHPUnit::assertEquals(1, $service->count());
        PHPUnit::assertTrue($service->hasErrors());

        $errors = $service->errors();
        PHPUnit::assertEquals(1, $errors->count());
        PHPUnit::assertSame($exception, $errors->first());
    }

    /**
     * @test
     * @dataProvider addExceptionAndStopProvider
     */
    public function addExceptionAndStop($stop, $check, $exception)
    {
        config()->set('jsonapi.stopAtFirstError', $stop);

        $service = new ExceptionService();

        $this->setFailure(get_class($exception), 'test');

        $service->addException($exception, $check);
    }

    public function addExceptionAndStopProvider()
    {
        return [
            'stop at first exception' => [
                true,
                true,
                new Exception('test')
            ],
            'JsonApiException with stop property' => [
                false,
                true,
                (new JsonApiException('test'))->stop(true)
            ]
        ];
    }

    /**
     * @test
     * @dataProvider addExceptionAndContinueProvider
     */
    public function addExceptionAndContinue($stop, $check, $exception)
    {
        config()->set('jsonapi.stopAtFirstError', $stop);

        $service = new ExceptionService();

        $service->addException($exception, $check);

        PHPUnit::assertEquals(1, $service->count());
        PHPUnit::assertTrue($service->hasErrors());

        $errors = $service->errors();
        PHPUnit::assertEquals(1, $errors->count());
        PHPUnit::assertSame($exception, $errors->first());
    }

    public function addExceptionAndContinueProvider()
    {
        return [
            'do not stop at first exception' => [
                false,
                true,
                new Exception('test')
            ],
            'do not check' => [
                true,
                false,
                new Exception('test')
            ],
            'JsonApiException without stop property' => [
                true,
                false,
                (new JsonApiException('test'))->stop(false)
            ]
        ];
    }

    /**
     * @test
     */
    public function addAndStopWithGenericClass()
    {
        config()->set('jsonapi.stopAtFirstError', false);

        $service = new ExceptionService();

        $message = 'test';
        $status = 512;

        $obj = $service->add($status, $message);

        PHPUnit::assertSame($obj, $service);
        PHPUnit::assertEquals(1, $service->count());
        PHPUnit::assertTrue($service->hasErrors());

        $errors = $service->errors();
        PHPUnit::assertEquals(1, $errors->count());

        $error = $errors->first();
        PHPUnit::assertInstanceOf(JsonApiException::class, $error);
        PHPUnit::assertEquals($message, $error->getMessage());
        PHPUnit::assertEquals($status, $error->status);
        PHPUnit::assertFalse($error->stop);
    }

    /**
     * @test
     */
    public function addAndStopWithSpecificClass()
    {
        config()->set('jsonapi.stopAtFirstError', true);

        $service = new ExceptionService();

        PHPUnit::assertEquals(0, $service->count());
        PHPUnit::assertFalse($service->hasErrors());

        $message = 'test';
        $this->setFailure(JsonApi400Exception::class, $message);

        $service->add(400, $message);
    }

    /**
     * @test
     * @dataProvider addAndStopProvider
     */
    public function addAndStop($code, $expectedClass)
    {
        $service = new ExceptionService();

        $message = 'test';
        $this->setFailure($expectedClass, $message);

        $service->add($code, $message);
    }

    public function addAndStopProvider()
    {
        return [
            'specific class' => [
                400,
                JsonApi400Exception::class
            ],
            'generic class' => [
                512,
                JsonApiException::class
            ]
        ];
    }
}
