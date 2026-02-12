<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
public function __construct(private readonly Generator $faker)
{

}
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        for($i = 0; $i < 50; ++$i){
            $videoGame = $this->getReference('video-game-' . $i, VideoGame::class);

            $nbReviews = rand(2, 5);
            for($j = 0; $j < $nbReviews; ++$j){
                $reviews = (new Review())
                    ->setVideoGame($videoGame)
                    ->setUser($users[array_rand($users)])
                    ->setRating(rand(1, 5))
                    ->setComment($this->faker->boolean(70) ? $this->faker->sentence(10) : null);

                $manager->persist($reviews);
            }
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VideoGameFixtures::class
        ];
    }
}
