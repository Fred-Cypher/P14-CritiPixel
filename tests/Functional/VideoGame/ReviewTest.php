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
    private ?User $user;
    private UrlGeneratorInterface $urlGenerator;
    private ?EntityManagerInterface $entityManager;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->urlGenerator = $container->get('router');

        $this->user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user+0@email.com']);

        if ($this->user) {
            $this->client->loginUser($this->user);
        }
    }

    public function testReviewSubmissionSuccess(): void
    {
    // Ajout d'une note et éventuellement un commentaire par un utilisateur
        // Va sur la page d'un jeu
        $game = $this->entityManager->getRepository(VideoGame::class)->findOneBy([]);

        // Vérifie que l'utilisateur est connecté
        $this->assertTrue($this->client->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));

        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $game->getSlug()]);
        $crawler = $this->client->request('GET', $url);

        // Vérifie que le formulaire est présent :
        $this->assertSelectorExists('form[name="review"]');

        // Soumet le formulaire
        $form = $crawler->selectButton('Poster')->form([
            'review[rating]' => 5,
            'review[comment]' => 'Super jeu !',
        ]);
        $this->client->submit($form);

        // Vérifie la redirection (302)
        $this->assertResponseRedirects();

        // Vérifie en BDD
        $review = $this->entityManager->getRepository(Review::class)->findOneBy([
            'videoGame' => $game,
            'user' => $this->user,
            'rating' => 5
        ]);
        $this->assertNotNull($review);
        $this->assertSame(5, $review->getRating());
        $this->assertSame('Super jeu !', $review->getComment());

        // Vérifie que le formulaire a disparu de la page
        $this->client->followRedirects();
        $this->assertSelectorNotExists('form[name="review"]');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
