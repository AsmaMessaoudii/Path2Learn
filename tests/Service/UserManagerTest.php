<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\Question;
use App\Entity\Cours;
use App\Entity\Evenement;
use App\Entity\Portfolio;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    // ----------------- USER -----------------
    public function testValidUser()
    {
        $user = new User();
        $user->setNom('Hugo');
        $user->setPrenom('Salim');
        $user->setEmail('victor.hugo@gmail.com');

        $manager = new UserManager();
        $this->assertTrue($manager->validate($user));
    }

    public function testUserWithoutName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user->setEmail('test@gmail.com');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user->setNom('User Test');
        $user->setPrenom('Salim');
        $user->setEmail('email_invalide');

        $manager = new UserManager();
        $manager->validate($user);
    }

    // ----------------- QUESTION -----------------
    public function testValidQuestion()
    {
        $question = new Question();
        $question->setTitre('Titre valide')
                 ->setDuree(30);

        $this->assertEquals('Titre valide', $question->getTitre());
        $this->assertEquals(30, $question->getDuree());
    }

    public function testInvalidQuestion()
    {
        $this->expectException(\TypeError::class);
        $question = new Question();
        // Titre vide -> invalid
        $question->setTitre(null);
        $question->setDuree(-5); // durée négative -> invalid
    }

    // ----------------- COURS -----------------
    public function testValidCours()
    {
        $cours = new Cours();
        $cours->setTitre('Symfony Avancé')
              ->setDuree(120);

        $this->assertEquals('Symfony Avancé', $cours->getTitre());
        $this->assertEquals(120, $cours->getDuree());
    }

    public function testInvalidCours()
    {
        $this->expectException(\TypeError::class);
        $cours = new Cours();
        $cours->setTitre(null); // titre vide
        $cours->setDuree(-50); // durée négative
    }

    // ----------------- EVENEMENT -----------------
    public function testValidEvenement()
    {
        $event = new Evenement();
        $event->setTitre('Conférence PHP')
              ->setCapaciteMax(50);

        $this->assertEquals('Conférence PHP', $event->getTitre());
        $this->assertEquals(50, $event->getCapaciteMax());
    }

    public function testInvalidEvenement()
    {
        $this->expectException(\TypeError::class);
        $event = new Evenement();
        $event->setTitre(null);        // titre vide
        $event->setCapaciteMax(-10);   // capacité négative
    }

    // ----------------- PORTFOLIO -----------------
    public function testValidPortfolio()
    {
        $portfolio = new Portfolio();
        $portfolio->setTitre('Portfolio Test')
                  ->setDescription('Une description correcte du portfolio.');

        $this->assertEquals('Portfolio Test', $portfolio->getTitre());
        $this->assertEquals('Une description correcte du portfolio.', $portfolio->getDescription());
    }

    public function testInvalidPortfolio()
    {
        $this->expectException(\TypeError::class);
        $portfolio = new Portfolio();
        $portfolio->setTitre(null);           // titre vide
        $portfolio->setDescription(null);     // description vide
    }
}