<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture
{
    public const TAGS = ['Action', 'RPG', 'Aventure', 'FPS', 'Indépendant', 'Stratégie', 'Sport'];
    public function load(ObjectManager $manager): void
    {
        foreach (self::TAGS as $tagName) {
            $tag = (new Tag())->setName($tagName);
            $manager->persist($tag);

            $this->addReference('tag-' . $tagName, $tag);
        }
        $manager->flush();
    }
}
