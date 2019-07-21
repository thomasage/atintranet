<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190721121853 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_search CHANGE results_per_page results_per_page INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_client CHANGE code code VARCHAR(10) DEFAULT NULL, CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL, CHANGE vat_number vat_number VARCHAR(255) DEFAULT NULL, CHANGE supplier_number supplier_number VARCHAR(255) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_task CHANGE project_id project_id INT UNSIGNED DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE stop stop DATETIME DEFAULT NULL, CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL, CHANGE expected unexpected TINYINT(1) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_invoice CHANGE credit_id credit_id INT UNSIGNED DEFAULT NULL, CHANGE order_number order_number VARCHAR(255) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_client_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT NULL, CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE app_project_rate DROP FOREIGN KEY FK_24F3EDA0166D1F9C');
        $this->addSql(
            'ALTER TABLE app_project_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT NULL, CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_project_rate ADD CONSTRAINT FK_24F3EDA0166D1F9C FOREIGN KEY (project_id) REFERENCES app_project (id)'
        );
        $this->addSql('ALTER TABLE app_project CHANGE external_reference external_reference VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE app_client CHANGE code code VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE supplier_number supplier_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE vat_number vat_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci'
        );
        $this->addSql(
            'ALTER TABLE app_client_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT \'NULL\', CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT \'NULL\''
        );
        $this->addSql(
            'ALTER TABLE app_invoice CHANGE credit_id credit_id INT UNSIGNED DEFAULT NULL, CHANGE order_number order_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci'
        );
        $this->addSql(
            'ALTER TABLE app_project CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci'
        );
        $this->addSql('ALTER TABLE app_project_rate DROP FOREIGN KEY FK_24F3EDA0166D1F9C');
        $this->addSql(
            'ALTER TABLE app_project_rate CHANGE hourly_rate_on_site hourly_rate_on_site DOUBLE PRECISION DEFAULT \'NULL\', CHANGE hourly_rate_off_site hourly_rate_off_site DOUBLE PRECISION DEFAULT \'NULL\''
        );
        $this->addSql(
            'ALTER TABLE app_project_rate ADD CONSTRAINT FK_24F3EDA0166D1F9C FOREIGN KEY (project_id) REFERENCES app_project (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE app_search CHANGE results_per_page results_per_page INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE app_task CHANGE project_id project_id INT UNSIGNED DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE stop stop DATETIME DEFAULT \'NULL\', CHANGE external_reference external_reference VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE unexpected expected TINYINT(1) NOT NULL'
        );
        $this->addSql('ALTER TABLE app_user CHANGE client_id client_id INT UNSIGNED DEFAULT NULL');
    }
}
