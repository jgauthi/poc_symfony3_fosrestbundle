<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924123036 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802D07ECCB6');
        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802F675F31B');
        $this->addSql('ALTER TABLE advert_application CHANGE advert_id advert_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advert_skill DROP FOREIGN KEY FK_5619F91B5585C142');
        $this->addSql('ALTER TABLE advert_skill ADD CONSTRAINT FK_5619F91B5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802D07ECCB6');
        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802F675F31B');
        $this->addSql('ALTER TABLE advert_application CHANGE advert_id advert_id INT NOT NULL');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id)');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE advert_skill DROP FOREIGN KEY FK_5619F91B5585C142');
        $this->addSql('ALTER TABLE advert_skill ADD CONSTRAINT FK_5619F91B5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
    }
}
