<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403180207 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advert ADD author_id INT NOT NULL, DROP author');
        $this->addSql('ALTER TABLE advert ADD CONSTRAINT FK_54F1F40BF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_54F1F40BF675F31B ON advert (author_id)');
        $this->addSql('ALTER TABLE advert_application ADD author_id INT NOT NULL, DROP author');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_625E2802F675F31B ON advert_application (author_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advert DROP FOREIGN KEY FK_54F1F40BF675F31B');
        $this->addSql('DROP INDEX IDX_54F1F40BF675F31B ON advert');
        $this->addSql('ALTER TABLE advert ADD author VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP author_id');
        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802F675F31B');
        $this->addSql('DROP INDEX IDX_625E2802F675F31B ON advert_application');
        $this->addSql('ALTER TABLE advert_application ADD author VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP author_id');
    }
}
