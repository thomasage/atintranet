<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190418072204 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_client ADD supplier_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE app_client SET supplier_number = account_number');
        $this->addSql('ALTER TABLE app_client DROP account_number');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_client ADD account_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE app_client SET account_number = supplier_number');
        $this->addSql('ALTER TABLE app_client DROP supplier_number');
    }
}
