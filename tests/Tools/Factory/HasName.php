<?php

declare(strict_types=1);

namespace VGirol\JsonApi\Tests\Tools\Factory;

/**
 * Add "name" member to a factory.
 */
trait HasName
{
    /**
     * The "name" member
     *
     * @var string|null
     */
    public $name;

    /**
     * Set the "name" member.
     *
     * @param string|null $type
     *
     * @return static
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Fill the "name" member with a fake value.
     *
     * @return static
     */
    public function fakeName()
    {
        $faker = \Faker\Factory::create();

        return $this->setName($faker->word());
    }
}
