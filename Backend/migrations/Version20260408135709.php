<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408135709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id VARCHAR(36) NOT NULL, type ENUM(\'cve_critique\',\'score_baisse\',\'port_expose\',\'ssl_expire\') NOT NULL, severite ENUM(\'critique\',\'warning\',\'info\') NOT NULL DEFAULT \'warning\', titre VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, donnees JSON DEFAULT NULL, date_creation DATETIME NOT NULL, projet_id VARCHAR(36) NOT NULL, composant_id VARCHAR(36) DEFAULT NULL, INDEX IDX_3AE753AC18272 (projet_id), INDEX IDX_3AE753A7F3310E7 (composant_id), INDEX idx_alerte_projet (projet_id, severite, date_creation), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE analyse (id VARCHAR(36) NOT NULL, declencheur ENUM(\'manuel\',\'cron\',\'alerte\') NOT NULL DEFAULT \'manuel\', statut ENUM(\'en_cours\',\'termine\',\'erreur\') NOT NULL DEFAULT \'en_cours\', score_avant NUMERIC(5, 2) DEFAULT NULL, score_apres NUMERIC(5, 2) DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, composant_id VARCHAR(36) NOT NULL, INDEX idx_composant (composant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE composant (id VARCHAR(36) NOT NULL, nom VARCHAR(150) NOT NULL, type ENUM(\'serveur\',\'bdd\',\'api\',\'cdn\',\'cloud\') NOT NULL, ip_ou_domaine VARCHAR(255) DEFAULT NULL, version_logicielle VARCHAR(100) DEFAULT NULL, environnement ENUM(\'prod\',\'staging\',\'dev\') NOT NULL DEFAULT \'prod\', port INT DEFAULT NULL, score NUMERIC(5, 2) DEFAULT NULL, derniere_analyse DATETIME DEFAULT NULL, position_x DOUBLE PRECISION NOT NULL, position_y DOUBLE PRECISION NOT NULL, date_creation DATETIME NOT NULL, projet_id VARCHAR(36) NOT NULL, INDEX idx_projet (projet_id), INDEX idx_score (score), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE composant_cve (id VARCHAR(36) NOT NULL, statut ENUM(\'active\',\'resolue\',\'ignoree\') NOT NULL DEFAULT \'active\', detecte_le DATETIME NOT NULL, resolu_le DATETIME DEFAULT NULL, composant_id VARCHAR(36) NOT NULL, cve_id VARCHAR(36) NOT NULL, INDEX IDX_A4A8CB947F3310E7 (composant_id), INDEX IDX_A4A8CB94DCC8D22B (cve_id), UNIQUE INDEX UNIQ_COMPOSANT_CVE (composant_id, cve_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cve (id VARCHAR(36) NOT NULL, cve_id VARCHAR(20) NOT NULL, score_cvss NUMERIC(3, 1) DEFAULT NULL, severite ENUM(\'critique\',\'eleve\',\'moyen\',\'faible\') NOT NULL DEFAULT \'moyen\', description LONGTEXT DEFAULT NULL, correctif_disponible TINYINT NOT NULL, date_publication DATE DEFAULT NULL, date_creation DATETIME NOT NULL, INDEX idx_cve_id (cve_id), UNIQUE INDEX UNIQ_CVE_ID (cve_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE journal_audit (id BIGINT AUTO_INCREMENT NOT NULL, action VARCHAR(100) NOT NULL, entite VARCHAR(50) NOT NULL, entite_id VARCHAR(36) DEFAULT NULL, niveau ENUM(\'info\',\'warning\',\'critical\') NOT NULL DEFAULT \'info\', ip_hash VARCHAR(64) NOT NULL, user_agent VARCHAR(500) DEFAULT NULL, donnees JSON DEFAULT NULL, date_creation DATETIME NOT NULL, utilisateur_id VARCHAR(36) DEFAULT NULL, INDEX IDX_71C3CC53FB88E14F (utilisateur_id), INDEX idx_audit_action (action), INDEX idx_audit_niveau (niveau), INDEX idx_audit_date (date_creation), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lien_composant (id VARCHAR(36) NOT NULL, type_lien ENUM(\'http\',\'tcp\',\'grpc\',\'amqp\',\'ssh\',\'autre\') NOT NULL DEFAULT \'http\', description VARCHAR(255) DEFAULT NULL, source_id VARCHAR(36) NOT NULL, cible_id VARCHAR(36) NOT NULL, INDEX IDX_E345EF1953C1C61 (source_id), INDEX IDX_E345EF1A96E5E09 (cible_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE membre (id VARCHAR(36) NOT NULL, role ENUM(\'user\',\'manager\',\'admin\') NOT NULL DEFAULT \'user\', date_ajout DATETIME NOT NULL, utilisateur_id VARCHAR(36) NOT NULL, organisation_id VARCHAR(36) NOT NULL, INDEX IDX_F6B4FB29FB88E14F (utilisateur_id), INDEX IDX_F6B4FB299E6B1585 (organisation_id), UNIQUE INDEX UNIQ_USER_ORG (utilisateur_id, organisation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id VARCHAR(36) NOT NULL, statut ENUM(\'non_lue\',\'lue\',\'resolue\') NOT NULL DEFAULT \'non_lue\', lue_le DATETIME DEFAULT NULL, date_creation DATETIME NOT NULL, utilisateur_id VARCHAR(36) NOT NULL, alerte_id VARCHAR(36) NOT NULL, INDEX IDX_BF5476CA2C9BA629 (alerte_id), INDEX idx_notif_user (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organisation (id VARCHAR(36) NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(150) NOT NULL, date_creation DATETIME NOT NULL, proprietaire_id VARCHAR(36) NOT NULL, INDEX IDX_E6E132B476C50E4A (proprietaire_id), UNIQUE INDEX UNIQ_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preference_alerte (id VARCHAR(36) NOT NULL, email_active TINYINT NOT NULL, seuil_score INT NOT NULL, utilisateur_id VARCHAR(36) NOT NULL, UNIQUE INDEX UNIQ_5F2FB0A5FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE projet (id VARCHAR(36) NOT NULL, nom VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, score_global NUMERIC(5, 2) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, organisation_id VARCHAR(36) NOT NULL, createur_id VARCHAR(36) NOT NULL, INDEX IDX_50159CA99E6B1585 (organisation_id), INDEX IDX_50159CA973A201E5 (createur_id), INDEX idx_score_global (score_global), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rapport (id VARCHAR(36) NOT NULL, type ENUM(\'audit\',\'conformite\') NOT NULL DEFAULT \'audit\', chemin_fichier VARCHAR(500) NOT NULL, donnees_snapshot JSON NOT NULL, date_creation DATETIME NOT NULL, projet_id VARCHAR(36) NOT NULL, generateur_id VARCHAR(36) NOT NULL, INDEX IDX_BE34A09CC18272 (projet_id), INDEX IDX_BE34A09C1267C9BB (generateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_token (id VARCHAR(36) NOT NULL, token VARCHAR(255) NOT NULL, famille_id VARCHAR(36) NOT NULL, consomme TINYINT NOT NULL, revoque TINYINT NOT NULL, expire_a DATETIME NOT NULL, date_creation DATETIME NOT NULL, utilisateur_id VARCHAR(36) NOT NULL, UNIQUE INDEX UNIQ_C74F21955F37A13B (token), INDEX IDX_C74F2195FB88E14F (utilisateur_id), INDEX idx_famille (famille_id), INDEX idx_token (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resultat_api (id VARCHAR(36) NOT NULL, api_source ENUM(\'shodan\',\'nvd\',\'ssllabs\',\'secheaders\',\'ipinfo\') NOT NULL, donnees_brutes JSON NOT NULL, penalite NUMERIC(5, 2) NOT NULL, erreur LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, analyse_id VARCHAR(36) NOT NULL, INDEX IDX_A4DE9EAA1EFE06BF (analyse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE snapshot_score (id VARCHAR(36) NOT NULL, score_global NUMERIC(5, 2) NOT NULL, nb_cve_actives INT NOT NULL, donnees JSON DEFAULT NULL, date_snapshot DATE NOT NULL, projet_id VARCHAR(36) NOT NULL, INDEX IDX_1C3F6A45C18272 (projet_id), INDEX idx_snapshot_projet (projet_id, date_snapshot), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id VARCHAR(36) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, roles JSON NOT NULL, oauth_google_id VARCHAR(255) DEFAULT NULL, email_verifie TINYINT NOT NULL, token_confirmation VARCHAR(255) DEFAULT NULL, tentatives_login INT NOT NULL, bloque_jusqua DATETIME DEFAULT NULL, consentement_rgpd DATETIME DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, UNIQUE INDEX UNIQ_EMAIL (email), UNIQUE INDEX UNIQ_OAUTH_GOOGLE (oauth_google_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE webhook (id VARCHAR(36) NOT NULL, url VARCHAR(500) NOT NULL, type_service ENUM(\'slack\',\'teams\',\'discord\',\'custom\') NOT NULL, actif TINYINT NOT NULL, secret VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, utilisateur_id VARCHAR(36) NOT NULL, INDEX IDX_8A741756FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AC18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A7F3310E7 FOREIGN KEY (composant_id) REFERENCES composant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E7F3310E7 FOREIGN KEY (composant_id) REFERENCES composant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE composant ADD CONSTRAINT FK_EC8486C9C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE composant_cve ADD CONSTRAINT FK_A4A8CB947F3310E7 FOREIGN KEY (composant_id) REFERENCES composant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE composant_cve ADD CONSTRAINT FK_A4A8CB94DCC8D22B FOREIGN KEY (cve_id) REFERENCES cve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE journal_audit ADD CONSTRAINT FK_71C3CC53FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lien_composant ADD CONSTRAINT FK_E345EF1953C1C61 FOREIGN KEY (source_id) REFERENCES composant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lien_composant ADD CONSTRAINT FK_E345EF1A96E5E09 FOREIGN KEY (cible_id) REFERENCES composant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membre ADD CONSTRAINT FK_F6B4FB29FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membre ADD CONSTRAINT FK_F6B4FB299E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2C9BA629 FOREIGN KEY (alerte_id) REFERENCES alerte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE organisation ADD CONSTRAINT FK_E6E132B476C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preference_alerte ADD CONSTRAINT FK_5F2FB0A5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA99E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA973A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_BE34A09CC18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_BE34A09C1267C9BB FOREIGN KEY (generateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat_api ADD CONSTRAINT FK_A4DE9EAA1EFE06BF FOREIGN KEY (analyse_id) REFERENCES analyse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE snapshot_score ADD CONSTRAINT FK_1C3F6A45C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webhook ADD CONSTRAINT FK_8A741756FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AC18272');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A7F3310E7');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E7F3310E7');
        $this->addSql('ALTER TABLE composant DROP FOREIGN KEY FK_EC8486C9C18272');
        $this->addSql('ALTER TABLE composant_cve DROP FOREIGN KEY FK_A4A8CB947F3310E7');
        $this->addSql('ALTER TABLE composant_cve DROP FOREIGN KEY FK_A4A8CB94DCC8D22B');
        $this->addSql('ALTER TABLE journal_audit DROP FOREIGN KEY FK_71C3CC53FB88E14F');
        $this->addSql('ALTER TABLE lien_composant DROP FOREIGN KEY FK_E345EF1953C1C61');
        $this->addSql('ALTER TABLE lien_composant DROP FOREIGN KEY FK_E345EF1A96E5E09');
        $this->addSql('ALTER TABLE membre DROP FOREIGN KEY FK_F6B4FB29FB88E14F');
        $this->addSql('ALTER TABLE membre DROP FOREIGN KEY FK_F6B4FB299E6B1585');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2C9BA629');
        $this->addSql('ALTER TABLE organisation DROP FOREIGN KEY FK_E6E132B476C50E4A');
        $this->addSql('ALTER TABLE preference_alerte DROP FOREIGN KEY FK_5F2FB0A5FB88E14F');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA99E6B1585');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA973A201E5');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09CC18272');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09C1267C9BB');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195FB88E14F');
        $this->addSql('ALTER TABLE resultat_api DROP FOREIGN KEY FK_A4DE9EAA1EFE06BF');
        $this->addSql('ALTER TABLE snapshot_score DROP FOREIGN KEY FK_1C3F6A45C18272');
        $this->addSql('ALTER TABLE webhook DROP FOREIGN KEY FK_8A741756FB88E14F');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('DROP TABLE composant');
        $this->addSql('DROP TABLE composant_cve');
        $this->addSql('DROP TABLE cve');
        $this->addSql('DROP TABLE journal_audit');
        $this->addSql('DROP TABLE lien_composant');
        $this->addSql('DROP TABLE membre');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE preference_alerte');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE rapport');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE resultat_api');
        $this->addSql('DROP TABLE snapshot_score');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE webhook');
    }
}
