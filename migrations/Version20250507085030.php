<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507085030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD stripe_session_id VARCHAR(255) DEFAULT NULL, ADD stripe_payment_id VARCHAR(255) DEFAULT NULL, ADD is_paid TINYINT(1) NOT NULL, ADD paid_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE registration_date create_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP stripe_session_id, DROP stripe_payment_id, DROP is_paid, DROP paid_at, CHANGE create_at registration_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }
}
