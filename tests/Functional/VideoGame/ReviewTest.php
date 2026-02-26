<?php

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReviewTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private ?User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->urlGenerator = $container->get('router');
        $this->user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user+0@email.com']);
    }

    public function testReviewSubmissionSuccess(): void
    {
        $this->client->loginUser($this->user);

        // Cherche un jeu qui n'a aucune review du tout pour être sûr.
        $game = $this->findGameNotRatedByUser($this->user);

        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $game->getSlug()]);
        $crawler = $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        // Sélectionne le bouton
        $buttonCrawler = $crawler->selectButton('Poster');
        $form = $buttonCrawler->form([
            'review[rating]' => 5,
            'review[comment]' => 'Super jeu !',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();

        // Vérifie que le formulaire disparaît bien après la redirection
        $this->client->followRedirect();
        $this->assertSelectorNotExists('button:contains("Poster")');
    }

    public function testReviewValidationErrors(): void
    {
        $this->client->loginUser($this->user);

        // Trouve un jeu non noté
        $game = $this->findGameNotRatedByUser($this->user);

        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $game->getSlug()]);
        $crawler = $this->client->request('GET', $url);

        // Remplit le commentaire avec 1001 caractères (au-dessus dela limite)
        $form = $crawler->selectButton('Poster')->form([
            'review[rating]' => 5,
            'review[comment]' => str_repeat('a', 1001),
        ]);

        $this->client->submit($form);

        // Vérifie qu'on reste sur la page (422) et qu'on voit l'erreur
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('#review_comment.is-invalid');
    }

    private function findGameNotRatedByUser(User $user): VideoGame
    {
        $allGames = $this->entityManager->getRepository(VideoGame::class)->findAll();
        foreach ($allGames as $game) {
            $alreadyRated = false;
            foreach ($game->getReviews() as $review) {
                if ($review->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
                    $alreadyRated = true;
                    break;
                }
            }
            if (!$alreadyRated) {
                return $game;
            }
        }

        $this->fail('Aucun jeu sans review trouvé pour cet utilisateur. Vérifiez vos fixtures !');
    }
    public function testAnonymousUserCannotReview(): void
    {
        $game = $this->entityManager->getRepository(VideoGame::class)->findOneBy([]);
        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $game->getSlug()]);

        // Vérifie que le bouton n'est pas là
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('button:contains("Poster")');

        // Teste la sécurité
        $this->client->request('POST', $url, [
            'review[rating]' => 5,
            'review[comment]' => 'Pirate !',
        ]);

        $this->assertResponseStatusCodeSame(200);

        // Vérifie qu'aucune review n'a été créée pour ce jeu par un anonyme
        $this->entityManager->clear();
        $reviews = $this->entityManager->getRepository(Review::class)->findBy(['videoGame' => $game]);
        foreach ($reviews as $rev) {
            $this->assertNotNull($rev->getUser(), "Un utilisateur anonyme a réussi à poster !");
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
