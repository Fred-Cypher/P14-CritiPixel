<?php

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class RatingHandlerTest extends TestCase
{
    private RatingHandler $handler;
    protected function setUp(): void
    {
        $this->handler = new RatingHandler();
    }

    public function testCalculateAverageRating(): void
    {
        $videoGame = new VideoGame();

        $review1 = (new Review())->setRating(2)->setVideoGame($videoGame);
        $review2 = (new Review())->setRating(4)->setVideoGame($videoGame);
        $review3 = (new Review())->setRating(0)->setVideoGame($videoGame);

        $videoGame->getReviews()->add($review1);
        $videoGame->getReviews()->add($review2);
        $videoGame->getReviews()->add($review3);

        $this->handler->calculateAverage($videoGame);

        $this->assertEquals(2, $videoGame->getAverageRating());
    }

    public function testCountRatingsPerValue(): void
    {
        $videoGame = new VideoGame();
        $videoGame->getReviews()->add((new Review())->setRating(5));
        $videoGame->getReviews()->add((new Review())->setRating(5));
        $videoGame->getReviews()->add((new Review())->setRating(1));

        $this->handler->countRatingsPerValue($videoGame);

        $stats = $videoGame->getNumberOfRatingsPerValue();
        $this->assertEquals(1, $stats->getNumberOfOne());
        $this->assertEquals(2, $stats->getNumberOfFive());
        $this->assertEquals(0, $stats->getNumberOfThree());
    }
}
