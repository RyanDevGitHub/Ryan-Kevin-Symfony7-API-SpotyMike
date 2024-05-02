<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240502140034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE playlist_has_song (id INT AUTO_INCREMENT NOT NULL, download TINYINT(1) DEFAULT NULL, position SMALLINT DEFAULT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_1599687DE94BC09');
        $this->addSql('DROP INDEX UNIQ_1599687DE94BC09 ON artist');
        $this->addSql('ALTER TABLE artist ADD avatar VARCHAR(255) DEFAULT NULL, DROP date_begin, DROP date_end, DROP active, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE user_id_user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1599687A76ED395 ON artist (user_id)');
        $this->addSql('ALTER TABLE playlist ADD CONSTRAINT FK_D782112DE2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA11137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1E2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('ALTER TABLE song_artist ADD CONSTRAINT FK_722870DA0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE song_artist ADD CONSTRAINT FK_722870DB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playlist DROP FOREIGN KEY FK_D782112DE2815C07');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1E2815C07');
        $this->addSql('DROP TABLE playlist_has_song');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_1599687A76ED395');
        $this->addSql('DROP INDEX UNIQ_1599687A76ED395 ON artist');
        $this->addSql('ALTER TABLE artist ADD date_begin DATETIME NOT NULL, ADD date_end DATETIME DEFAULT NULL, ADD active INT DEFAULT NULL, DROP avatar, CHANGE created_at created_at DATETIME NOT NULL, CHANGE user_id user_id_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687DE94BC09 FOREIGN KEY (user_id_user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1599687DE94BC09 ON artist (user_id_user_id)');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA11137ABCF');
        $this->addSql('ALTER TABLE song_artist DROP FOREIGN KEY FK_722870DA0BDB2F3');
        $this->addSql('ALTER TABLE song_artist DROP FOREIGN KEY FK_722870DB7970CF8');
    }
}
