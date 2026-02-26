<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /**
     * @dataProvider tagFilterProvider
     */
    public function testFilterByTags(string $url, array $expectedTags): void
    {
        $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();

        $games = $this->client->getCrawler()->filter('article.game-card');

        self::assertGreaterThan(0, $games->count());

        foreach ($games as $game) {
            foreach ($expectedTags as $tag) {
                self::assertStringContainsString(
                    $tag,
                    $game->textContent
                );
            }
        }
    }

    public static function tagFilterProvider(): \Generator
    {

        yield 'pas de tag' => ['/', []];

        yield 'Action tag' => ['/?filter[tags][]=8', ['Action']];

        yield 'RPG tag' => ['/?filter[tags][]=9', ['RPG']];

        yield 'deux tags (Action + RPG)' => ['/?filter[tags][]=8&filter[tags][]=9', ['Action', 'RPG']];

        yield 'Tag qui n\'existe pas' => ['/?filter[tags][]=999', []];
    }
}
