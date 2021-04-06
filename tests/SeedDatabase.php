<?php

namespace VGirol\JsonApi\Tests;

use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;

trait SeedDatabase
{
    protected function seedDatabase()
    {
        // Creates a collection of primary objects with filled out fields
        $count = 5;
        factory(Photo::class, $count)->create();

        // Creates collections of related objects
        $countTags = 5;
        $tags = factory(Tags::class, $countTags)->create();
        $countAuthor = 5;
        $authors = factory(Author::class, $countAuthor)->create();

        // Attach related objects to main object : Populate the pivot table
        Photo::all()->each(
            function ($photo) use ($tags, $authors) {
                factory(Price::class)->create(['PHOTO_ID' => $photo->getKey()]);

                factory(Comment::class, rand(1, 5))->create([
                    'PHOTO_ID' => $photo->getKey(),
                    'AUTHOR_ID' => $authors->random(1)->first()->AUTHOR_ID
                ]);

                $photo->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('TAGS_ID')->toArray()
                );
            }
        );
    }
}
