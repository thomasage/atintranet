<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190622170729 extends AbstractMigration
{
    /**
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE app_offer (id INT AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED NOT NULL, address_id INT UNSIGNED NOT NULL, issue_date DATE NOT NULL, number VARCHAR(3) NOT NULL, amount_excluding_tax NUMERIC(15, 2) NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, tax_amount NUMERIC(15, 2) NOT NULL, amount_including_tax NUMERIC(15, 2) NOT NULL, amount_paid NUMERIC(15, 2) NOT NULL, comment LONGTEXT DEFAULT NULL, comment_internal LONGTEXT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_FF050AF3D17F50A6 (uuid), INDEX IDX_FF050AF319EB6921 (client_id), UNIQUE INDEX UNIQ_FF050AF3F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_offer_detail (id INT UNSIGNED AUTO_INCREMENT NOT NULL, offer_id INT NOT NULL, designation LONGTEXT NOT NULL, quantity DOUBLE PRECISION NOT NULL, amount_unit NUMERIC(15, 2) NOT NULL, amount_total NUMERIC(15, 2) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_35DD12B3D17F50A6 (uuid), INDEX IDX_35DD12B353C674EE (offer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE app_offer ADD CONSTRAINT FK_FF050AF319EB6921 FOREIGN KEY (client_id) REFERENCES app_client (id)'
        );
        $this->addSql(
            'ALTER TABLE app_offer ADD CONSTRAINT FK_FF050AF3F5B7AF75 FOREIGN KEY (address_id) REFERENCES app_address (id)'
        );
        $this->addSql(
            'ALTER TABLE app_offer_detail ADD CONSTRAINT FK_35DD12B353C674EE FOREIGN KEY (offer_id) REFERENCES app_offer (id)'
        );
    }

    /**
     * @throws DBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_offer_detail DROP FOREIGN KEY FK_35DD12B353C674EE');
        $this->addSql('DROP TABLE app_offer');
        $this->addSql('DROP TABLE app_offer_detail');
    }
}
