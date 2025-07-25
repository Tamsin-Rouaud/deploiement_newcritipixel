<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users   = $manager->getRepository(User::class)->findAll();
        $allTags = $manager->getRepository(Tag::class)->findAll();

        $videoGames = array_map(function (int $index) {
            return (new VideoGame())
                ->setTitle(\sprintf('Jeu vidéo %d', $index))
                ->setDescription($this->faker->paragraphs(10, true))
                ->setReleaseDate(new \DateTimeImmutable())
                ->setTest($this->faker->paragraphs(6, true))
                ->setRating(($index % 5) + 1)
                ->setImageName(\sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872);
        }, range(0, 49));

        foreach ($videoGames as $index => $videoGame) {
            switch ($index) {
                case 0:
                    $tagIds = [1, 2, 3, 4, 5];
                    break;
                case 21:
                    $tagIds = [1];
                    break;
                case 22:
                    $tagIds = [1, 2];
                    break;
                case 23:
                    $tagIds = [1, 2, 3];
                    break;
                case 24:
                    $tagIds = [1, 2, 3, 4];
                    break;
                case 25:
                    $tagIds = [1, 2, 3, 4, 5];
                    break;
                case 46:
                    $tagIds = [1];
                    break;
                case 47:
                    $tagIds = [1, 2];
                    break;
                case 48:
                    $tagIds = [1, 2, 3];
                    break;
                case 49:
                    $tagIds = [1, 2, 3, 4];
                    break;
                default:
                    $tagIds = array_map(
                        fn (Tag $tag) => $tag->getId(),
                        $this->faker->randomElements($allTags, rand(1, 3))
                    );
            }

            foreach ($allTags as $tag) {
                if (\in_array($tag->getId(), $tagIds, true)) {
                    $videoGame->getTags()->add($tag);
                }
            }

            $reviewers = $this->faker->randomElements($users, rand(2, 5));
            foreach ($reviewers as $user) {
                $review = (new Review())
                    ->setVideoGame($videoGame)
                    ->setUser($user)
                    ->setRating($rating = rand(1, 5))
                    ->setComment($this->faker->optional(0.7)->paragraph());

                $videoGame->getReviews()->add($review);
                $manager->persist($review);
            }

            $this->calculateAverageRating->calculateAverage($videoGame);
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);

            $manager->persist($videoGame);
        }

        // Crée un jeu spécifique avec les tags Stratégie + Indépendant
$strategieTag = null;
$indepTag = null;

foreach ($allTags as $tag) {
    if ($tag->getName() === 'Stratégie') {
        $strategieTag = $tag;
    }
    if ($tag->getName() === 'Indépendant') {
        $indepTag = $tag;
    }
}

if ($strategieTag && $indepTag && count($users) > 0) {
    $specialGame = new VideoGame();
    $specialGame->setTitle('Jeu filtré spécial')
        ->setDescription('Jeu ayant les tags Stratégie et Indépendant.')
        ->setReleaseDate(new \DateTimeImmutable())
        ->setTest('Contenu test spécial')
        ->setRating(4)
        ->setImageName('special_game.png')
        ->setImageSize(2048000);

    $specialGame->getTags()->add($strategieTag);
    $specialGame->getTags()->add($indepTag);

    $review = (new Review())
        ->setVideoGame($specialGame)
        ->setUser($users[0])
        ->setRating(4)
        ->setComment('Super jeu pour tester les filtres.');

    $specialGame->getReviews()->add($review);
    $manager->persist($review);

    $this->calculateAverageRating->calculateAverage($specialGame);
    $this->countRatingsPerValue->countRatingsPerValue($specialGame);

    $manager->persist($specialGame);
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
