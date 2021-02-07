<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207131451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_D3D1D6932989F1FD');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice_line AS SELECT id, invoice_id, amount, description FROM invoice_line');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('CREATE TABLE invoice_line (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, invoice_id INTEGER NOT NULL, amount INTEGER NOT NULL, description VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO invoice_line (id, invoice_id, amount, description) SELECT id, invoice_id, amount, description FROM __temp__invoice_line');
        $this->addSql('DROP TABLE __temp__invoice_line');
        $this->addSql('CREATE INDEX IDX_D3D1D6932989F1FD ON invoice_line (invoice_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN roles CLOB DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_D3D1D6932989F1FD');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invoice_line AS SELECT id, invoice_id, amount, description FROM invoice_line');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('CREATE TABLE invoice_line (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, invoice_id INTEGER NOT NULL, amount INTEGER NOT NULL, description VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO invoice_line (id, invoice_id, amount, description) SELECT id, invoice_id, amount, description FROM __temp__invoice_line');
        $this->addSql('DROP TABLE __temp__invoice_line');
        $this->addSql('CREATE INDEX IDX_D3D1D6932989F1FD ON invoice_line (invoice_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, full_name, email, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, full_name, email, password) SELECT id, full_name, email, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
