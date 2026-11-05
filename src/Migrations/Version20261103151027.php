<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103151027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(180) NOT NULL, slug VARCHAR(200) NOT NULL, joueur VARCHAR(30) NOT NULL, extrait LONGTEXT NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, auteur_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E6660BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_image (id INT AUTO_INCREMENT NOT NULL, fichier VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, principale TINYINT NOT NULL, date_creation DATETIME NOT NULL, article_id INT NOT NULL, INDEX IDX_B28A764E7294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, nom VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6660BB6FE6');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764E7294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_image');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
