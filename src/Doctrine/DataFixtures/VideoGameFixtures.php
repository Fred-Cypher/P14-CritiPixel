<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tags = [];
        foreach (TagFixtures::TAGS as $tagName) {
            $tags[] = $this->getReference('tag-' . $tagName, Tag::class);
        }

        for ($index = 0; $index < 50; $index++) {
            $game = (new VideoGame())
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription('Description du jeu')
                ->setReleaseDate(new \DateTimeImmutable())
                ->setTest('Test du jeu')
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2098872);

            $randomTags = array_rand($tags, rand(2, 3));

            if (is_array($randomTags)) {
                foreach ($randomTags as $tagIndex) {
                    $game->getTags()->add($tags[$tagIndex]);
                }
            } else {
                $game->getTags()->add($tags[$randomTags]);
            }

            $manager->persist($game);
            $this->addReference('video-game-' . $index, $game);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
