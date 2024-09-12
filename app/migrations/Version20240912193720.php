<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912193720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Slot and Reservation init';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('CREATE TABLE slot (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, is_available TINYINT(1) NOT NULL, INDEX IDX_slot_created_by (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_slot_user_id FOREIGN KEY (created_by) REFERENCES `user` (id)');

        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, slot_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_reservation_user_id (user_id), INDEX IDX_reservation_slot_id (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_reservation_user_id FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_reservation_slot_id FOREIGN KEY (slot_id) REFERENCES slot (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_reservation_user_id');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_reservation_slot_id');
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_slot_user_id');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE slot');
    }
}
