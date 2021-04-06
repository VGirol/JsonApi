<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\ModelService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;

class ModelServiceTest extends TestCase
{
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpToolsDB();
    }

    /**
     * @test
     */
    public function getModelTable()
    {
        $service = resolve(ModelService::class);

        PHPUnit::assertEquals('photo', $service->getModelTable(Photo::class));
    }

    /**
     * @test
     */
    public function getModelKeyName()
    {
        $service = resolve(ModelService::class);

        PHPUnit::assertEquals('PHOTO_ID', $service->getModelKeyName(Photo::class));
    }

    /**
     * @test
     */
    public function getVisibleWhenAllFieldsAreVisible()
    {
        $expected = [
            'AUTHOR_ID',
            'AUTHOR_NAME'
        ];

        $service = resolve(ModelService::class);

        PHPUnit::assertEquals($expected, $service->getVisible(Author::class));
    }

    /**
     * @test
     */
    public function getVisibleWhenSomeFieldsAreHidden()
    {
        $expected = [
            'PHOTO_ID',
            'PHOTO_TITLE',
            'PHOTO_SIZE',
            'PHOTO_DATE'
        ];

        $service = resolve(ModelService::class);

        PHPUnit::assertEquals($expected, $service->getVisible(Photo::class));
    }
}
