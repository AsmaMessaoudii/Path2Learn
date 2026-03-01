<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301134541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_rating_reason RENAME INDEX idx_bad_rating_evaluation TO IDX_5BFCD18F456C5646');
        $this->addSql('DROP INDEX IDX_DATE ON chat_messages');
        $this->addSql('ALTER TABLE chat_messages RENAME INDEX idx_cours TO IDX_EF20C9A67ECF78B0');
        $this->addSql('ALTER TABLE chat_messages RENAME INDEX idx_user TO IDX_EF20C9A6A76ED395');
        $this->addSql('ALTER TABLE choix CHANGE question_id question_id INT NOT NULL');
        $this->addSql('DROP INDEX idx_generated_at ON course_summary');
        $this->addSql('ALTER TABLE course_summary CHANGE is_auto_generated is_auto_generated TINYINT DEFAULT 1 NOT NULL, CHANGE summary_type summary_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE course_summary RENAME INDEX idx_cours_id TO IDX_C6EB638F7ECF78B0');
        $this->addSql('ALTER TABLE cv_info CHANGE langues langues JSON DEFAULT NULL, CHANGE centres_interet centres_interet JSON DEFAULT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE cv_info ADD CONSTRAINT FK_AAC7D18BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_AAC7D18BA76ED395 ON cv_info (user_id)');
        $this->addSql('ALTER TABLE participation_event CHANGE date_inscription date_inscription DATETIME NOT NULL, CHANGE statut statut VARCHAR(50) NOT NULL, CHANGE evenement_id evenement_id INT NOT NULL');
        $this->addSql('ALTER TABLE projet CHANGE description description LONGTEXT NOT NULL, CHANGE portfolio_id portfolio_id INT NOT NULL');
        $this->addSql('ALTER TABLE ressource_pedagogique CHANGE cours_id cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_rating_reason RENAME INDEX idx_5bfcd18f456c5646 TO IDX_BAD_RATING_EVALUATION');
        $this->addSql('CREATE INDEX IDX_DATE ON chat_messages (created_at)');
        $this->addSql('ALTER TABLE chat_messages RENAME INDEX idx_ef20c9a67ecf78b0 TO IDX_COURS');
        $this->addSql('ALTER TABLE chat_messages RENAME INDEX idx_ef20c9a6a76ed395 TO IDX_USER');
        $this->addSql('ALTER TABLE choix CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_summary CHANGE is_auto_generated is_auto_generated TINYINT DEFAULT 1, CHANGE summary_type summary_type VARCHAR(50) DEFAULT \'standard\'');
        $this->addSql('CREATE INDEX idx_generated_at ON course_summary (generated_at)');
        $this->addSql('ALTER TABLE course_summary RENAME INDEX idx_c6eb638f7ecf78b0 TO idx_cours_id');
        $this->addSql('ALTER TABLE cv_info DROP FOREIGN KEY FK_AAC7D18BA76ED395');
        $this->addSql('DROP INDEX IDX_AAC7D18BA76ED395 ON cv_info');
        $this->addSql('ALTER TABLE cv_info CHANGE langues langues LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE centres_interet centres_interet LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE participation_event CHANGE date_inscription date_inscription DATE NOT NULL, CHANGE statut statut VARCHAR(20) NOT NULL, CHANGE evenement_id evenement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projet CHANGE description description VARCHAR(255) NOT NULL, CHANGE portfolio_id portfolio_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ressource_pedagogique CHANGE cours_id cours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE prenom prenom VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(150) NOT NULL, CHANGE role role VARCHAR(50) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE date_creation date_creation DATE NOT NULL');
    }
}
