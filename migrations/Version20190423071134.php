<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190423071134 extends AbstractMigration
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

        $this->addSql('ALTER TABLE app_invoice DROP currency');
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

        $this->addSql('ALTER TABLE app_invoice ADD currency VARCHAR(3) NOT NULL COLLATE utf8mb4_general_ci');
    }
}
