<?php


namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class RatingHandlerProviderTest extends TestCase
{
    /**
     * Ce test sera exécutée autant de fois qu'il y a de "yield" dans le provider.
     * * @dataProvider provideRatingScenarios
     */
    public function testCalculateAverage(array $ratings, ?int $expectedResult): void
    {
        // 1. Arrange
        $handler = new RatingHandler();
        $videoGame = $this->createVideoGameWithRatings($ratings);

        // 2. Act
        $handler->calculateAverage($videoGame);

        // 3. Assert
        $this->assertSame($expectedResult, $videoGame->getAverageRating());
    }

    /**
     * Le "fournisseur de données"
     * Chaque clé du tableau (ex : 'Moyenne simple') s'affichera dans la console si le test échoue.
     */
    public static function provideRatingScenarios(): iterable
    {
        yield 'Aucune note' => [[], null];
        yield 'Une seule note parfaite' => [[5], 5];
        yield 'Moyenne qui tombe pile' => [[2, 4], 3];
        yield 'Moyenne avec arrondi supérieur (3.5 -> 4)' => [[2, 5], 4];
        yield 'Moyenne avec arrondi supérieur (3.1 -> 4)' => [[3, 3, 4], 4];
    }

    /**
     * Méthode "Helper" pour éviter de répéter la création d'objets
     */
    private function createVideoGameWithRatings(array $ratings): VideoGame
    {
        $videoGame = new VideoGame();
        foreach ($ratings as $value) {
            $review = (new Review())->setRating($value);
            $videoGame->getReviews()->add($review);
        }
        return $videoGame;
    }
}
