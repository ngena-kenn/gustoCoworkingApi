<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240428203018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, activite_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, date DATETIME NOT NULL, adresse_ip VARCHAR(255) NOT NULL, user_agent VARCHAR(255) NOT NULL, INDEX IDX_B87555159B0F88B1 (activite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, sujet VARCHAR(255) DEFAULT NULL, messages LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, capacite VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, type_emplacement VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE espace_de_travail (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formule (id INT AUTO_INCREMENT NOT NULL, nom_formule VARCHAR(255) NOT NULL, description_formule VARCHAR(255) NOT NULL, prix VARCHAR(255) NOT NULL, description2 VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE new_letter (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, reservation_id INT NOT NULL, montant VARCHAR(255) NOT NULL, date DATE NOT NULL, mode_paiement VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B1DC7A1EB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, formule_id INT DEFAULT NULL, espacedetravail_id INT DEFAULT NULL, date VARCHAR(255) DEFAULT NULL, effectif VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, heure_de_debut VARCHAR(255) NOT NULL, heure_de_fin VARCHAR(255) NOT NULL, prix_reservation VARCHAR(255) DEFAULT NULL, INDEX IDX_42C84955A76ED395 (user_id), INDEX IDX_42C849552A68F4D1 (formule_id), INDEX IDX_42C8495593646672 (espacedetravail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salon_principal (id INT AUTO_INCREMENT NOT NULL, espace_de_travail_id INT NOT NULL, nom_salon_principal VARCHAR(255) NOT NULL, INDEX IDX_93BBB5BF65CEBD8E (espace_de_travail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salon_privee (id INT AUTO_INCREMENT NOT NULL, espace_de_travail_id INT DEFAULT NULL, nom_salon_privee VARCHAR(255) NOT NULL, capacite VARCHAR(255) NOT NULL, INDEX IDX_5A9645FF65CEBD8E (espace_de_travail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, emplacement_id INT DEFAULT NULL, non_table VARCHAR(255) DEFAULT NULL, capacite_table VARCHAR(255) DEFAULT NULL, INDEX IDX_F6298F46C4598A51 (emplacement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE verification_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, verification_code VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C1AFBBDBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555159B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849552A68F4D1 FOREIGN KEY (formule_id) REFERENCES formule (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495593646672 FOREIGN KEY (espacedetravail_id) REFERENCES espace_de_travail (id)');
        $this->addSql('ALTER TABLE salon_principal ADD CONSTRAINT FK_93BBB5BF65CEBD8E FOREIGN KEY (espace_de_travail_id) REFERENCES espace_de_travail (id)');
        $this->addSql('ALTER TABLE salon_privee ADD CONSTRAINT FK_5A9645FF65CEBD8E FOREIGN KEY (espace_de_travail_id) REFERENCES espace_de_travail (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE verification_tokens ADD CONSTRAINT FK_C1AFBBDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555159B0F88B1');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EB83297E7');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849552A68F4D1');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495593646672');
        $this->addSql('ALTER TABLE salon_principal DROP FOREIGN KEY FK_93BBB5BF65CEBD8E');
        $this->addSql('ALTER TABLE salon_privee DROP FOREIGN KEY FK_5A9645FF65CEBD8E');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F46C4598A51');
        $this->addSql('ALTER TABLE verification_tokens DROP FOREIGN KEY FK_C1AFBBDBA76ED395');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE espace_de_travail');
        $this->addSql('DROP TABLE formule');
        $this->addSql('DROP TABLE new_letter');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE salon_principal');
        $this->addSql('DROP TABLE salon_privee');
        $this->addSql('DROP TABLE `table`');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE verification_tokens');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
