<?php

declare(strict_types=1);

namespace VGirol\JsonApi\Tests\Tools\Factory;

/**
 * Add "routeName" member to a factory.
 */
trait HasRouteName
{
    /**
     * The "routeName" member
     *
     * @var string|null
     */
    public $routeName;

    /**
     * Set the "routeName" member.
     *
     * @param string|null $type
     *
     * @return static
     */
    public function setRouteName(?string $routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Fill the "routeName" member with a fake value.
     *
     * @return static
     */
    public function fakeRouteName()
    {
        $faker = \Faker\Factory::create();

        return $this->setRouteName($faker->word());
    }
}
