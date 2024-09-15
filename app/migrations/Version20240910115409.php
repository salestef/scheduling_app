<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910115409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Product table creation';
    }

    public function up(Schema $schema): void
    {
        // Kreiranje tabele product
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_desc LONGTEXT NOT NULL, long_desc LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, img VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Brisanje tabele product
        $this->addSql('DROP TABLE product');
    }
}
