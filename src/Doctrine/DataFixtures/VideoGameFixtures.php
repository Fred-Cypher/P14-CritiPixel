<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        $tags = [];
        foreach (TagFixtures::TAGS as $tagName) {
            $tags[] = $this->getReference('tag-' . $tagName, Tag::class);
        }

        $videoGames = array_fill_callback(0, 50, function (int $index) use ($tags): VideoGame {
            $game = (new VideoGame())
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription($this->faker->paragraphs(10, true))
                ->setReleaseDate(new \DateTimeImmutable())
                ->setTest($this->faker->paragraphs(6, true))
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872);

            // Puisqu'il n'y a pas de addTag(), on manipule directement la Collection
            $randomTags = $this->faker->randomElements($tags, rand(2, 3));
            foreach ($randomTags as $tag) {
                $game->getTags()->add($tag);
            }
            $this->addReference('video-game-' . $index, $game);

            return $game;
        });

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class
        ];
    }
}
