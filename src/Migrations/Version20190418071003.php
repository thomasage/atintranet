<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190418071003 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_invoice ADD order_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_client CHANGE code code VARCHAR(10) DEFAULT NULL, CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL, CHANGE account_number account_number VARCHAR(255) DEFAULT NULL, CHANGE vat_number vat_number VARCHAR(255) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_client_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT NULL, CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_payment CHANGE value_date value_date DATE DEFAULT NULL, CHANGE bank_name bank_name VARCHAR(255) DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE app_project CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_project_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT NULL, CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE app_search CHANGE results_per_page results_per_page INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_payment_invoice CHANGE invoice_id invoice_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_task CHANGE project_id project_id INT UNSIGNED DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE stop stop DATETIME DEFAULT NULL, CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE app_client CHANGE code code VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE account_number account_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE vat_number vat_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci'
        );
        $this->addSql(
            'ALTER TABLE app_client_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT \'NULL\', CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT \'NULL\''
        );
        $this->addSql('ALTER TABLE app_invoice DROP order_number');
        $this->addSql(
            'ALTER TABLE app_payment CHANGE value_date value_date DATE DEFAULT \'NULL\', CHANGE bank_name bank_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci'
        );
        $this->addSql('ALTER TABLE app_payment_invoice CHANGE invoice_id invoice_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_project CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci'
        );
        $this->addSql(
            'ALTER TABLE app_project_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT \'NULL\', CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT \'NULL\''
        );
        $this->addSql('ALTER TABLE app_search CHANGE results_per_page results_per_page INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_task CHANGE project_id project_id INT UNSIGNED DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci, CHANGE stop stop DATETIME DEFAULT \'NULL\', CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci'
        );
        $this->addSql('ALTER TABLE app_user CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
    }
}
