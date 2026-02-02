<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202135921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Init database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE battle (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, date_time DATETIME NOT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE battle_participant (id INT AUTO_INCREMENT NOT NULL, score INT DEFAULT 0 NOT NULL, is_knockout TINYINT NOT NULL, battle_id INT NOT NULL, robot_id INT NOT NULL, INDEX IDX_B133D84BC9732719 (battle_id), INDEX IDX_B133D84BD5AA10AC (robot_id), UNIQUE INDEX unique_battle_robot (battle_id, robot_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE robot (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, owner VARCHAR(100) NOT NULL, weapon VARCHAR(50) NOT NULL, locomotion VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE battle_participant ADD CONSTRAINT FK_B133D84BC9732719 FOREIGN KEY (battle_id) REFERENCES battle (id)');
        $this->addSql('ALTER TABLE battle_participant ADD CONSTRAINT FK_B133D84BD5AA10AC FOREIGN KEY (robot_id) REFERENCES robot (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE battle_participant DROP FOREIGN KEY FK_B133D84BC9732719');
        $this->addSql('ALTER TABLE battle_participant DROP FOREIGN KEY FK_B133D84BD5AA10AC');
        $this->addSql('DROP TABLE battle');
        $this->addSql('DROP TABLE battle_participant');
        $this->addSql('DROP TABLE robot');
    }
}
