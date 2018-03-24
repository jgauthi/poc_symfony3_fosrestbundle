<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180324113326 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE advert (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, date DATETIME NOT NULL, published TINYINT(1) NOT NULL, updated_at DATETIME DEFAULT NULL, nb_applications INT NOT NULL, UNIQUE INDEX UNIQ_54F1F40B2B36786B (title), UNIQUE INDEX UNIQ_54F1F40B989D9B62 (slug), UNIQUE INDEX UNIQ_54F1F40B3DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advert_category (advert_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_84EEA340D07ECCB6 (advert_id), INDEX IDX_84EEA34012469DE2 (category_id), PRIMARY KEY(advert_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advert_skill (id INT AUTO_INCREMENT NOT NULL, advert_id INT NOT NULL, skill_id INT NOT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_5619F91BD07ECCB6 (advert_id), INDEX IDX_5619F91B5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advert_application (id INT AUTO_INCREMENT NOT NULL, advert_id INT NOT NULL, author VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, date DATETIME NOT NULL, INDEX IDX_625E2802D07ECCB6 (advert_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advert ADD CONSTRAINT FK_54F1F40B3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE advert_category ADD CONSTRAINT FK_84EEA340D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advert_category ADD CONSTRAINT FK_84EEA34012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advert_skill ADD CONSTRAINT FK_5619F91BD07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id)');
        $this->addSql('ALTER TABLE advert_skill ADD CONSTRAINT FK_5619F91B5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE advert_application ADD CONSTRAINT FK_625E2802D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advert_category DROP FOREIGN KEY FK_84EEA340D07ECCB6');
        $this->addSql('ALTER TABLE advert_skill DROP FOREIGN KEY FK_5619F91BD07ECCB6');
        $this->addSql('ALTER TABLE advert_application DROP FOREIGN KEY FK_625E2802D07ECCB6');
        $this->addSql('ALTER TABLE advert_category DROP FOREIGN KEY FK_84EEA34012469DE2');
        $this->addSql('ALTER TABLE advert DROP FOREIGN KEY FK_54F1F40B3DA5256D');
        $this->addSql('ALTER TABLE advert_skill DROP FOREIGN KEY FK_5619F91B5585C142');
        $this->addSql('DROP TABLE advert');
        $this->addSql('DROP TABLE advert_category');
        $this->addSql('DROP TABLE advert_skill');
        $this->addSql('DROP TABLE advert_application');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE user');
    }
}
