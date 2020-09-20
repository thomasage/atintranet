<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190423070703 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_payment DROP FOREIGN KEY FK_8075044019883967');
        $this->addSql('ALTER TABLE app_payment_invoice DROP FOREIGN KEY FK_D3F63C274C3A3BB');
        $this->addSql('DROP TABLE app_invoice_payment');
        $this->addSql('DROP TABLE app_option_payment_method');
        $this->addSql('DROP TABLE app_payment');
        $this->addSql('DROP TABLE app_payment_invoice');
    }

    /**
     * @param Schema $schema
     *
     * @throws DBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE app_invoice_payment (id INT UNSIGNED AUTO_INCREMENT NOT NULL, invoice_id INT UNSIGNED NOT NULL, value_date DATE NOT NULL, amount NUMERIC(10, 2) NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:uuid)\', INDEX IDX_C52B97572989F1FD (invoice_id), UNIQUE INDEX UNIQ_C52B9757D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'CREATE TABLE app_option_payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'CREATE TABLE app_payment (id INT UNSIGNED AUTO_INCREMENT NOT NULL, method_id INT NOT NULL, operation_date DATE NOT NULL, value_date DATE DEFAULT \'NULL\', amount NUMERIC(15, 2) NOT NULL, currency VARCHAR(3) NOT NULL COLLATE utf8mb4_general_ci, bank_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, third_party_name VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_general_ci, uuid CHAR(36) NOT NULL COLLATE utf8mb4_general_ci COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8075044019883967 (method_id), UNIQUE INDEX UNIQ_80750440D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'CREATE TABLE app_payment_invoice (id INT AUTO_INCREMENT NOT NULL, payment_id INT UNSIGNED NOT NULL, invoice_id INT UNSIGNED DEFAULT NULL, amount NUMERIC(15, 2) NOT NULL, INDEX IDX_D3F63C272989F1FD (invoice_id), INDEX IDX_D3F63C274C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'ALTER TABLE app_invoice_payment ADD CONSTRAINT FK_C52B97572989F1FD FOREIGN KEY (invoice_id) REFERENCES app_invoice (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment ADD CONSTRAINT FK_8075044019883967 FOREIGN KEY (method_id) REFERENCES app_option_payment_method (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment_invoice ADD CONSTRAINT FK_D3F63C272989F1FD FOREIGN KEY (invoice_id) REFERENCES app_invoice (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment_invoice ADD CONSTRAINT FK_D3F63C274C3A3BB FOREIGN KEY (payment_id) REFERENCES app_payment (id)'
        );
    }
}
