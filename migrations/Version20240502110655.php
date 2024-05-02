<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240502110655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE playlist_has_song (id INT AUTO_INCREMENT NOT NULL, download TINYINT(1) DEFAULT NULL, position SMALLINT DEFAULT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE song_playlist DROP FOREIGN KEY FK_7C5E47656BBD148');
        $this->addSql('ALTER TABLE song_playlist DROP FOREIGN KEY FK_7C5E4765A0BDB2F3');
        $this->addSql('DROP TABLE song_playlist');
        $this->addSql('ALTER TABLE album CHANGE categ categ VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE artist ADD date_begin DATETIME NOT NULL, ADD date_end DATETIME DEFAULT NULL, ADD active INT DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE playlist ADD playlist_has_song_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE playlist ADD CONSTRAINT FK_D782112DE2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('CREATE INDEX IDX_D782112DE2815C07 ON playlist (playlist_has_song_id)');
        $this->addSql('ALTER TABLE song ADD playlist_has_song_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1E2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1E2815C07 ON song (playlist_has_song_id)');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD date_birth DATE NOT NULL, ADD sexe INT DEFAULT NULL, ADD disable INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496B3CA4B ON user (id_user)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playlist DROP FOREIGN KEY FK_D782112DE2815C07');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1E2815C07');
        $this->addSql('CREATE TABLE song_playlist (song_id INT NOT NULL, playlist_id INT NOT NULL, INDEX IDX_7C5E4765A0BDB2F3 (song_id), INDEX IDX_7C5E47656BBD148 (playlist_id), PRIMARY KEY(song_id, playlist_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE song_playlist ADD CONSTRAINT FK_7C5E47656BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE song_playlist ADD CONSTRAINT FK_7C5E4765A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE playlist_has_song');
        $this->addSql('ALTER TABLE album CHANGE categ categ VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX IDX_33EDEEA1E2815C07 ON song');
        $this->addSql('ALTER TABLE song DROP playlist_has_song_id');
        $this->addSql('ALTER TABLE artist DROP date_begin, DROP date_end, DROP active, DROP created_at');
        $this->addSql('DROP INDEX IDX_D782112DE2815C07 ON playlist');
        $this->addSql('ALTER TABLE playlist DROP playlist_has_song_id');
        $this->addSql('DROP INDEX UNIQ_8D93D6496B3CA4B ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP date_birth, DROP sexe, DROP disable');
    }
}
