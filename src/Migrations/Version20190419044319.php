<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190419044319 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE app_invoice ADD credit_id INT UNSIGNED DEFAULT NULL, CHANGE order_number order_number VARCHAR(255) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE app_invoice ADD CONSTRAINT FK_7D389709CE062FF9 FOREIGN KEY (credit_id) REFERENCES app_invoice (id)'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D389709CE062FF9 ON app_invoice (credit_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE app_invoice DROP FOREIGN KEY FK_7D389709CE062FF9');
        $this->addSql('DROP INDEX UNIQ_7D389709CE062FF9 ON app_invoice');
        $this->addSql(
            'ALTER TABLE app_invoice DROP credit_id, CHANGE order_number order_number VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_general_ci'
        );
    }
}
