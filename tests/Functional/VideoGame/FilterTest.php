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
     * @param array<string> $tagIds
     *
     * @dataProvider provideTagFilters
     */
    public function testShouldFilterVideoGamesByTags(array $tagIds, int $expectedMinCount): void
    {
        $crawler = $this->get('/', ['filter' => ['tags' => $tagIds]]);
        self::assertResponseIsSuccessful();

        $count = $crawler->filter('article.game-card')->count();

        if (0 === $expectedMinCount) {
            self::assertSame(0, $count);
        } else {
            self::assertGreaterThanOrEqual(
                $expectedMinCount,
                $count,
                \sprintf(
                    'On attend au moins %d jeux pour les tags %s, mais %d trouvés.',
                    $expectedMinCount,
                    implode(',', $tagIds),
                    $count
                )
            );
        }
    }

    /**
     * @return iterable<string, array{0: array<string>, 1: int}>
     */
    public static function provideTagFilters(): iterable
    {
        yield 'aucun tag' => [
            [],
            10,
        ];

        yield 'tag RPG (id 1)' => [
            ['1'],
            1,
        ];

        yield 'tags RPG + Action (1,2)' => [
            ['1', '2'],
            1,
        ];

        yield 'tags Stratégie + Indépendant (7,5)' => [
            ['7', '5'],
            1,
        ];

        yield 'tag inexistant (id 999)' => [
            ['999'],
            10,
        ];
    }
}
