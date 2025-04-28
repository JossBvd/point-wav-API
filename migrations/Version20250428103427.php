<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428103427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, image_id INT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_64C19C13DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, ranking INT NOT NULL, INDEX IDX_C53D045F4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, promotion_id INT DEFAULT NULL, total_price NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, registration_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F5299398139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_product (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, order_reference_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, order_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2530ADE64584665A (product_id), INDEX IDX_2530ADE612854AC3 (order_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(50) NOT NULL, brand VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, price NUMERIC(10, 2) NOT NULL, stock INT NOT NULL, registration_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, reduction_type VARCHAR(50) NOT NULL, reduction_value INT NOT NULL, start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_active TINYINT(1) NOT NULL, registration_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refund (id INT AUTO_INCREMENT NOT NULL, order_product_id INT NOT NULL, refunded_quantity INT NOT NULL, status VARCHAR(50) NOT NULL, refund_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', stripe_refund_id VARCHAR(255) NOT NULL, INDEX IDX_5B2C1458F65E9B0F (order_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_promotion (user_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_C1FDF035A76ED395 (user_id), INDEX IDX_C1FDF035139DF194 (promotion_id), PRIMARY KEY(user_id, promotion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT FK_64C19C13DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image ADD CONSTRAINT FK_C53D045F4584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE612854AC3 FOREIGN KEY (order_reference_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458F65E9B0F FOREIGN KEY (order_product_id) REFERENCES order_product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_promotion ADD CONSTRAINT FK_C1FDF035A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_promotion ADD CONSTRAINT FK_C1FDF035139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP FOREIGN KEY FK_64C19C13DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398139DF194
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE64584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE612854AC3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund DROP FOREIGN KEY FK_5B2C1458F65E9B0F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_promotion DROP FOREIGN KEY FK_C1FDF035A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_promotion DROP FOREIGN KEY FK_C1FDF035139DF194
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE promotion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refund
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_promotion
        SQL);
    }
}
