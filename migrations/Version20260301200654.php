<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301200654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_rating_reason ADD CONSTRAINT FK_5BFCD18F456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_EF20C9A67ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_EF20C9A6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE choix ADD CONSTRAINT FK_4F4880911E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE cours CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course_summary ADD CONSTRAINT FK_C6EB638F7ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv_info ADD CONSTRAINT FK_AAC7D18BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5757ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A6897ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A689A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE participation_event ADD CONSTRAINT FK_3472872CFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE participation_event ADD CONSTRAINT FK_3472872CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE portfolio ADD CONSTRAINT FK_A9ED1062A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9B96B5643 FOREIGN KEY (portfolio_id) REFERENCES portfolio (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ressource_pedagogique ADD CONSTRAINT FK_56F6BCD67ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bad_rating_reason DROP FOREIGN KEY FK_5BFCD18F456C5646');
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_EF20C9A67ECF78B0');
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_EF20C9A6A76ED395');
        $this->addSql('ALTER TABLE choix DROP FOREIGN KEY FK_4F4880911E27F6BF');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CA76ED395');
        $this->addSql('ALTER TABLE cours CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_summary DROP FOREIGN KEY FK_C6EB638F7ECF78B0');
        $this->addSql('ALTER TABLE cv_info DROP FOREIGN KEY FK_AAC7D18BA76ED395');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5757ECF78B0');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575A76ED395');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EA76ED395');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A6897ECF78B0');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A689A76ED395');
        $this->addSql('ALTER TABLE participation_event DROP FOREIGN KEY FK_3472872CFD02F13');
        $this->addSql('ALTER TABLE participation_event DROP FOREIGN KEY FK_3472872CA76ED395');
        $this->addSql('ALTER TABLE portfolio DROP FOREIGN KEY FK_A9ED1062A76ED395');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9B96B5643');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE ressource_pedagogique DROP FOREIGN KEY FK_56F6BCD67ECF78B0');
    }
}
