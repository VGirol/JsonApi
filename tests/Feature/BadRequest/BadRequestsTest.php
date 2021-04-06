<?php

namespace VGirol\JsonApi\Tests\Feature\BadRequest;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;

class BadRequestsTest extends TestCase
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
        $this->setUpToolsRoutes();
    }

    private function jsonEmpty($method, $uri, array $data = [], array $headers = [])
    {
        $files = $this->extractFilesFromDataArray($data);

        $content = json_encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'Accept' => 'application/json',
        ], $headers);

        return $this->call($method, $uri, [], [], $files, $this->transformHeadersToServerVars($headers), $content);
    }

    /**
     * GET /endpoint/
     * Should return 400
     *
     * @test
     */
    public function requestHasNoContentTypeHeader()
    {
        // Set config
        config()->set('jsonapi.stopAtFirstError', true);

        $mediaType = $this->getMediaType();

        // Sends request and gets response
        $headers = [];
        $url = route('photos.index');
        $content = [];
        $response = $this->jsonEmpty('GET', $url, $content, $headers);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse400(
            [
                [
                    Members::ERROR_STATUS => '400',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[400],
                    Members::ERROR_DETAILS => sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_MISSING, $mediaType)
                ]
            ]
        );
    }

    /**
     * GET /endpoint/
     * Should return 400
     *
     * @test
     */
    public function requestHasContentTypeHeaderWithBadMediaType()
    {
        // Set config
        config()->set('jsonapi.stopAtFirstError', true);

        $mediaType = $this->getMediaType();

        // Sends request and gets response
        $headers = ['Content-Type' => 'application/json'];
        $url = route('photos.index');
        $content = [];
        $response = parent::json('GET', $url, $content, $headers);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse400(
            [
                [
                    Members::ERROR_STATUS => '400',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[400],
                    Members::ERROR_DETAILS => sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_BAD_MEDIA_TYPE, $mediaType)
                ]
            ]
        );
    }

    /**
     * GET /endpoint/
     * Should return 415
     *
     * @test
     */
    public function requestHasContentTypeHeaderWithMediaTypeParameter()
    {
        // Set config
        config()->set('jsonapi.stopAtFirstError', true);

        $mediaType = $this->getMediaType();

        // Sends request and gets response
        $headers = ['Content-Type' => "{$mediaType}; param=value"];
        $url = route('photos.index');
        $content = [];
        $response = $this->jsonApi('GET', $url, $content, $headers);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse415(
            [
                [
                    Members::ERROR_STATUS => '415',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[415],
                    Members::ERROR_DETAILS => sprintf(
                        Messages::ERROR_CONTENT_TYPE_HEADER_WITHOUT_PARAMETERS,
                        $mediaType
                    )
                ]
            ]
        );
    }

    /**
     * GET /endpoint/
     * Should return 406
     *
     * @test
     */
    public function requestHasNotValidAcceptHeader()
    {
        // Set config
        config()->set('jsonapi.stopAtFirstError', true);

        $mediaType = $this->getMediaType();

        // Sends request and gets response
        $headers = [
            'Content-Type' => $mediaType,
            'Accept' => "{$mediaType}; param=value, application/json, {$mediaType}; charset=utf-8"
        ];
        $url = route('photos.index');
        $content = [];
        $response = $this->jsonApi('GET', $url, $content, $headers);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse406(
            [
                [
                    Members::ERROR_STATUS => '406',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[406],
                    Members::ERROR_DETAILS => sprintf(Messages::ERROR_ACCEPT_HEADER_WITHOUT_PARAMETERS, $mediaType)
                ]
            ]
        );
    }
}
