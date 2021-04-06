<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Related;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class FetchingSingleRelatedTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{parentId}/{relationship}/{id}
     * Should return 200 with data
     *
     * @test
     */
    public function fetchingSingleRelated()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates a collection of related objects
        $tags = factory(Tags::class, 3)->create();
        $model->tags()->attach(
            $tags->pluck('TAGS_ID')->toArray()
        );
        $related = $model->tags()->first();

        // Sends request and gets response
        $url = route(
            'photos.related.show',
            [
                'parentId' => $model->getKey(),
                'relationship' => 'tags',
                'id' => $related->getKey()
            ]
        );
        $response = $this->jsonApi('GET', $url);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($related, 'tag', 'tags')
            ->addSelfLink()
            ->addAttribute('PIVOT_COMMENT', $related->getModel()->pivot->PIVOT_COMMENT);

        // Checks the response (status code, headers) and the fetched resource
        $response->assertJsonApiFetchedSingleResource($expected->toArray());

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }
}
