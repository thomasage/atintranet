<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190423062558 extends AbstractMigration
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
            'CREATE TABLE app_invoice_payment (id INT UNSIGNED AUTO_INCREMENT NOT NULL, invoice_id INT UNSIGNED NOT NULL, value_date DATE NOT NULL, amount NUMERIC(10, 2) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_C52B9757D17F50A6 (uuid), INDEX IDX_C52B97572989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE app_invoice_payment ADD CONSTRAINT FK_C52B97572989F1FD FOREIGN KEY (invoice_id) REFERENCES app_invoice (id)'
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

        $this->addSql('DROP TABLE app_invoice_payment');
    }
}
