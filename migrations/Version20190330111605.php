<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20190330111605.
 */
final class Version20190330111605 extends AbstractMigration
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
            'CREATE TABLE app_client (id INT UNSIGNED AUTO_INCREMENT NOT NULL, address_primary_id INT UNSIGNED NOT NULL, code VARCHAR(10) DEFAULT NULL, name VARCHAR(255) NOT NULL, external_reference VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, account_number VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_224769D577153098 (code), UNIQUE INDEX UNIQ_224769D5D17F50A6 (uuid), UNIQUE INDEX UNIQ_224769D5594384F2 (address_primary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_payment_invoice (id INT AUTO_INCREMENT NOT NULL, payment_id INT UNSIGNED NOT NULL, invoice_id INT UNSIGNED DEFAULT NULL, amount NUMERIC(15, 2) NOT NULL, INDEX IDX_D3F63C274C3A3BB (payment_id), INDEX IDX_D3F63C272989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_search (id INT AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, route VARCHAR(255) NOT NULL, page INT NOT NULL, filter LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', orderby LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', results_per_page INT DEFAULT NULL, INDEX IDX_51F3B627A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_payment (id INT UNSIGNED AUTO_INCREMENT NOT NULL, method_id INT NOT NULL, operation_date DATE NOT NULL, value_date DATE DEFAULT NULL, amount NUMERIC(15, 2) NOT NULL, currency VARCHAR(3) NOT NULL, locked TINYINT(1) NOT NULL, bank_name VARCHAR(255) DEFAULT NULL, third_party_name VARCHAR(255) NOT NULL, comment LONGTEXT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_80750440D17F50A6 (uuid), INDEX IDX_8075044019883967 (method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_invoice_detail (id INT UNSIGNED AUTO_INCREMENT NOT NULL, invoice_id INT UNSIGNED NOT NULL, designation LONGTEXT NOT NULL, quantity DOUBLE PRECISION NOT NULL, amount_unit NUMERIC(15, 2) NOT NULL, amount_total NUMERIC(15, 2) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_4ACA703ED17F50A6 (uuid), INDEX IDX_4ACA703E2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_project (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, external_reference VARCHAR(255) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C2EE50A3D17F50A6 (uuid), INDEX IDX_C2EE50A319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_option_payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_invoice (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED NOT NULL, address_id INT UNSIGNED NOT NULL, number VARCHAR(7) NOT NULL, type VARCHAR(10) NOT NULL, issue_date DATE NOT NULL, due_date DATE NOT NULL, amount_excluding_tax NUMERIC(15, 2) NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, tax_amount NUMERIC(15, 2) NOT NULL, amount_including_tax NUMERIC(15, 2) NOT NULL, amount_paid NUMERIC(15, 2) NOT NULL, currency VARCHAR(3) NOT NULL, comment LONGTEXT DEFAULT NULL, comment_internal LONGTEXT DEFAULT NULL, closed TINYINT(1) NOT NULL, locked TINYINT(1) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7D389709D17F50A6 (uuid), INDEX IDX_7D38970919EB6921 (client_id), UNIQUE INDEX UNIQ_7D389709F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_address (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address LONGTEXT DEFAULT NULL, postcode VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(2) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E013EFCCD17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_project_rate (id INT UNSIGNED AUTO_INCREMENT NOT NULL, project_id INT UNSIGNED NOT NULL, hourly_rate_on_site DOUBLE PRECISION DEFAULT NULL, hourly_rate_off_site DOUBLE PRECISION DEFAULT NULL, started_at DATE NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_24F3EDA0D17F50A6 (uuid), INDEX IDX_24F3EDA0166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_task (id INT UNSIGNED AUTO_INCREMENT NOT NULL, project_id INT UNSIGNED DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, start DATETIME NOT NULL, stop DATETIME DEFAULT NULL, on_site TINYINT(1) NOT NULL, expected TINYINT(1) NOT NULL, external_reference VARCHAR(255) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_5750FE85D17F50A6 (uuid), INDEX IDX_5750FE85166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE app_user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(180) NOT NULL, role VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_88BDF3E9D17F50A6 (uuid), UNIQUE INDEX UNIQ_88BDF3E9F85E0677 (username), INDEX IDX_88BDF3E919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE app_client ADD CONSTRAINT FK_224769D5594384F2 FOREIGN KEY (address_primary_id) REFERENCES app_address (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment_invoice ADD CONSTRAINT FK_D3F63C274C3A3BB FOREIGN KEY (payment_id) REFERENCES app_payment (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment_invoice ADD CONSTRAINT FK_D3F63C272989F1FD FOREIGN KEY (invoice_id) REFERENCES app_invoice (id)'
        );
        $this->addSql(
            'ALTER TABLE app_search ADD CONSTRAINT FK_51F3B627A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)'
        );
        $this->addSql(
            'ALTER TABLE app_payment ADD CONSTRAINT FK_8075044019883967 FOREIGN KEY (method_id) REFERENCES app_option_payment_method (id)'
        );
        $this->addSql(
            'ALTER TABLE app_invoice_detail ADD CONSTRAINT FK_4ACA703E2989F1FD FOREIGN KEY (invoice_id) REFERENCES app_invoice (id)'
        );
        $this->addSql(
            'ALTER TABLE app_project ADD CONSTRAINT FK_C2EE50A319EB6921 FOREIGN KEY (client_id) REFERENCES app_client (id)'
        );
        $this->addSql(
            'ALTER TABLE app_invoice ADD CONSTRAINT FK_7D38970919EB6921 FOREIGN KEY (client_id) REFERENCES app_client (id)'
        );
        $this->addSql(
            'ALTER TABLE app_invoice ADD CONSTRAINT FK_7D389709F5B7AF75 FOREIGN KEY (address_id) REFERENCES app_address (id)'
        );
        $this->addSql(
            'ALTER TABLE app_project_rate ADD CONSTRAINT FK_24F3EDA0166D1F9C FOREIGN KEY (project_id) REFERENCES app_project (id)'
        );
        $this->addSql(
            'ALTER TABLE app_task ADD CONSTRAINT FK_5750FE85166D1F9C FOREIGN KEY (project_id) REFERENCES app_project (id)'
        );
        $this->addSql(
            'ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E919EB6921 FOREIGN KEY (client_id) REFERENCES app_client (id)'
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

        $this->addSql('ALTER TABLE app_project DROP FOREIGN KEY FK_C2EE50A319EB6921');
        $this->addSql('ALTER TABLE app_invoice DROP FOREIGN KEY FK_7D38970919EB6921');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E919EB6921');
        $this->addSql('ALTER TABLE app_payment_invoice DROP FOREIGN KEY FK_D3F63C274C3A3BB');
        $this->addSql('ALTER TABLE app_project_rate DROP FOREIGN KEY FK_24F3EDA0166D1F9C');
        $this->addSql('ALTER TABLE app_task DROP FOREIGN KEY FK_5750FE85166D1F9C');
        $this->addSql('ALTER TABLE app_payment DROP FOREIGN KEY FK_8075044019883967');
        $this->addSql('ALTER TABLE app_payment_invoice DROP FOREIGN KEY FK_D3F63C272989F1FD');
        $this->addSql('ALTER TABLE app_invoice_detail DROP FOREIGN KEY FK_4ACA703E2989F1FD');
        $this->addSql('ALTER TABLE app_client DROP FOREIGN KEY FK_224769D5594384F2');
        $this->addSql('ALTER TABLE app_invoice DROP FOREIGN KEY FK_7D389709F5B7AF75');
        $this->addSql('ALTER TABLE app_search DROP FOREIGN KEY FK_51F3B627A76ED395');
        $this->addSql('DROP TABLE app_client');
        $this->addSql('DROP TABLE app_payment_invoice');
        $this->addSql('DROP TABLE app_search');
        $this->addSql('DROP TABLE app_payment');
        $this->addSql('DROP TABLE app_invoice_detail');
        $this->addSql('DROP TABLE app_project');
        $this->addSql('DROP TABLE app_option_payment_method');
        $this->addSql('DROP TABLE app_invoice');
        $this->addSql('DROP TABLE app_address');
        $this->addSql('DROP TABLE app_project_rate');
        $this->addSql('DROP TABLE app_task');
        $this->addSql('DROP TABLE app_user');
    }
}
