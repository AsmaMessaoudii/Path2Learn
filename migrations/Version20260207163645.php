<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207163645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE choix (id INT AUTO_INCREMENT NOT NULL, contenu VARCHAR(255) NOT NULL, est_correct TINYINT NOT NULL, question_id INT DEFAULT NULL, INDEX IDX_4F4880911E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, niveau VARCHAR(50) NOT NULL, matiere VARCHAR(50) NOT NULL, duree INT NOT NULL, date_creation DATE NOT NULL, email_prof VARCHAR(100) NOT NULL, statut VARCHAR(20) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_FDCA8C9CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, lieu VARCHAR(255) NOT NULL, capacite_max INT NOT NULL, image_url VARCHAR(255) DEFAULT NULL, statut VARCHAR(50) NOT NULL, categorie VARCHAR(100) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_B26681EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participation_event (id INT AUTO_INCREMENT NOT NULL, nom_participant VARCHAR(100) NOT NULL, prenom_participant VARCHAR(100) NOT NULL, email_participant VARCHAR(180) NOT NULL, telephone_participant VARCHAR(20) DEFAULT NULL, date_inscription DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, evenement_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_3472872CFD02F13 (evenement_id), INDEX IDX_3472872CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE portfolio (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, date_creation DATE NOT NULL, date_mise_ajour DATE NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_A9ED1062A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, titre_projet VARCHAR(150) NOT NULL, text VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, technologies VARCHAR(255) NOT NULL, date_realisation DATE NOT NULL, lien_demo VARCHAR(255) NOT NULL, portfolio_id INT DEFAULT NULL, INDEX IDX_50159CA9B96B5643 (portfolio_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, date_creation DATE NOT NULL, duree INT NOT NULL, note_max NUMERIC(10, 0) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_B6F7494EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ressource_pedagogique (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, date_ajout DATE NOT NULL, cours_id INT DEFAULT NULL, INDEX IDX_56F6BCD67ECF78B0 (cours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, date_creation DATE NOT NULL, statut VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE choix ADD CONSTRAINT FK_4F4880911E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation_event ADD CONSTRAINT FK_3472872CFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE participation_event ADD CONSTRAINT FK_3472872CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE portfolio ADD CONSTRAINT FK_A9ED1062A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9B96B5643 FOREIGN KEY (portfolio_id) REFERENCES portfolio (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ressource_pedagogique ADD CONSTRAINT FK_56F6BCD67ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choix DROP FOREIGN KEY FK_4F4880911E27F6BF');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CA76ED395');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EA76ED395');
        $this->addSql('ALTER TABLE participation_event DROP FOREIGN KEY FK_3472872CFD02F13');
        $this->addSql('ALTER TABLE participation_event DROP FOREIGN KEY FK_3472872CA76ED395');
        $this->addSql('ALTER TABLE portfolio DROP FOREIGN KEY FK_A9ED1062A76ED395');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9B96B5643');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('ALTER TABLE ressource_pedagogique DROP FOREIGN KEY FK_56F6BCD67ECF78B0');
        $this->addSql('DROP TABLE choix');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE participation_event');
        $this->addSql('DROP TABLE portfolio');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE ressource_pedagogique');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
