<?php

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class RatingCountTest extends TestCase
{
    /**
     * @dataProvider provideRatingCount
     */
    public function testCountRatingsPerValue(array $ratings, array $expectedMapping): void
    {
        $handler = new RatingHandler();
        $videoGame = $this->createVideoGameWithRatings($ratings);

        $handler->countRatingsPerValue($videoGame);

        $stats = $videoGame->getNumberOfRatingsPerValue();

        $this->assertEquals($expectedMapping['1'], $stats->getNumberOfOne(), "Erreur sur le compte des notes de 1");
        $this->assertEquals($expectedMapping['2'], $stats->getNumberOfTwo(), "Erreur sur le compte des notes de 2");
        $this->assertEquals($expectedMapping['3'], $stats->getNumberOfThree(), "Erreur sur le compte des notes de 3");
        $this->assertEquals($expectedMapping['4'], $stats->getNumberOfFour(), "Erreur sur le compte des notes de 4");
        $this->assertEquals($expectedMapping['5'], $stats->getNumberOfFive(), "Erreur sur le compte des notes de 5");
    }

    public static function provideRatingCount(): iterable
    {
        yield 'Aucune review' => ['ratings' => [], 'expected' => ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0]];

        yield 'Notes variées' => ['ratings' => [1, 2, 3, 5, 5, 5], 'expected' => ['1' => 1, '2' => 1, '3' => 1, '4' => 0, '5' => 3]];

        yield 'Uniquement des 5' => ['ratings' => [5, 5, 5, 5], 'expected' => ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 4]];
    }

    private function createVideoGameWithRatings(array $ratings):  VideoGame
    {
        $videoGame = new VideoGame();
        foreach ($ratings as $value) {
            $review = (new Review())->setRating($value);
            $videoGame->getReviews()->add($review);
        }
        return $videoGame;
    }
}
