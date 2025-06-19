<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619163038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE currency (id SERIAL NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, name_plural VARCHAR(255) NOT NULL, symbol VARCHAR(8) NOT NULL, symbol_native VARCHAR(8) NOT NULL, decimal_digits INT NOT NULL, rounding NUMERIC(10, 6) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX idx_currency_code ON currency (code)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX idx_currency_name ON currency (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX idx_currency_name_plural ON currency (name_plural)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE currency_pair (id SERIAL NOT NULL, from_currency_id INT NOT NULL, to_currency_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX idx_currency_pair_from_currency_to_currency ON currency_pair (from_currency_id, to_currency_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_currency_pair_from_currency ON currency_pair (from_currency_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE exchange_rate (id SERIAL NOT NULL, currency_pair_id INT NOT NULL, rate NUMERIC(20, 10) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX idx_exchange_rate_currency_pair ON exchange_rate (currency_pair_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE exchange_rate_history (id SERIAL NOT NULL, currency_pair_id INT NOT NULL, rate NUMERIC(20, 10) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_exchange_rate_history_currency_pair ON exchange_rate_history (currency_pair_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE currency_pair ADD CONSTRAINT FK_83ED5D1DA66BB013 FOREIGN KEY (from_currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE currency_pair ADD CONSTRAINT FK_83ED5D1D16B7BF15 FOREIGN KEY (to_currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate ADD CONSTRAINT FK_E9521FABA311484C FOREIGN KEY (currency_pair_id) REFERENCES currency_pair (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate_history ADD CONSTRAINT FK_51C18A99A311484C FOREIGN KEY (currency_pair_id) REFERENCES currency_pair (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE currency_pair DROP CONSTRAINT FK_83ED5D1DA66BB013
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE currency_pair DROP CONSTRAINT FK_83ED5D1D16B7BF15
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate DROP CONSTRAINT FK_E9521FABA311484C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate_history DROP CONSTRAINT FK_51C18A99A311484C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE currency
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE currency_pair
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE exchange_rate
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE exchange_rate_history
        SQL);
    }
}
