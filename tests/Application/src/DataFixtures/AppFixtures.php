<?php

/*
 * This file is part of the API Platform range header pagination Bundle.
 *
 * (c) Campings.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\DataFixtures;

use Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\Entity\Book;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const BOOKS_ITERATION = 100;

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < self::BOOKS_ITERATION; ++$i) {
            $book = new Book();
            $book->author = $faker->name();
            $book->description = $faker->text();
            $book->isbn = $faker->isbn10();
            $book->publicationDate = \DateTimeImmutable::createFromMutable($faker->dateTime());
            $book->title = $faker->sentence();

            $manager->persist($book);

            for ($j = 0; $j < $faker->numberBetween(0, 5); ++$j) {
                $review = new Review();
                $review->book = $book;
                $review->publicationDate = \DateTimeImmutable::createFromMutable($faker->dateTime());
                $review->author = $faker->name();
                $review->body = $faker->text();
                $review->rating = $faker->numberBetween(0, 5);
                $manager->persist($review);
            }
        }

        $manager->flush();
    }
}
