<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma NautiLog MVP : utilisateur, port, bateau, reservation, trajet, refresh_token';
    }

    public function up(Schema $schema): void
    {
        // Utilisateur (remplace l'ancien schéma cybersec)
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (
                id                  VARCHAR(36)  NOT NULL,
                email               VARCHAR(255) NOT NULL,
                password            VARCHAR(255) NOT NULL,
                nom                 VARCHAR(100) NOT NULL,
                roles               JSON         NOT NULL,
                telephone           VARCHAR(255)  DEFAULT NULL,
                numero_permis       VARCHAR(512)  DEFAULT NULL,
                email_verifie       TINYINT(1)   NOT NULL DEFAULT 0,
                token_confirmation  VARCHAR(255)  DEFAULT NULL,
                tentatives_login    INT          NOT NULL DEFAULT 0,
                bloque_jusqu_a      DATETIME     DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                consentement_rgpd   DATETIME     DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_suppression    DATETIME     DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_creation       DATETIME     NOT NULL   COMMENT '(DC2Type:datetime_immutable)',
                date_modification   DATETIME     NOT NULL   COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                UNIQUE INDEX UNIQ_EMAIL (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Refresh token
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_token (
                id            VARCHAR(36)  NOT NULL,
                utilisateur_id VARCHAR(36) NOT NULL,
                token         VARCHAR(255) NOT NULL,
                famille_id    VARCHAR(36)  NOT NULL,
                expire_a      DATETIME     NOT NULL  COMMENT '(DC2Type:datetime_immutable)',
                consomme      TINYINT(1)   NOT NULL DEFAULT 0,
                revoque       TINYINT(1)   NOT NULL DEFAULT 0,
                date_creation DATETIME     NOT NULL  COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                UNIQUE INDEX UNIQ_TOKEN (token),
                INDEX IDX_USER (utilisateur_id),
                INDEX IDX_FAMILLE (famille_id),
                CONSTRAINT FK_RT_USER FOREIGN KEY (utilisateur_id)
                    REFERENCES utilisateur (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Port
        $this->addSql(<<<'SQL'
            CREATE TABLE port (
                id                VARCHAR(36)      NOT NULL,
                nom               VARCHAR(150)     NOT NULL,
                ville             VARCHAR(255)     DEFAULT NULL,
                pays              VARCHAR(100)     DEFAULT NULL,
                latitude          DECIMAL(10,7)    NOT NULL,
                longitude         DECIMAL(10,7)    NOT NULL,
                capacite          INT              DEFAULT NULL,
                description       LONGTEXT         DEFAULT NULL,
                actif             TINYINT(1)       NOT NULL DEFAULT 1,
                date_creation     DATETIME         NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_modification DATETIME         NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                INDEX idx_geo (latitude, longitude)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Bateau
        $this->addSql(<<<'SQL'
            CREATE TABLE bateau (
                id                  VARCHAR(36)     NOT NULL,
                proprietaire_id     VARCHAR(36)     NOT NULL,
                port_id             VARCHAR(36)     DEFAULT NULL,
                nom                 VARCHAR(150)    NOT NULL,
                type                VARCHAR(100)    DEFAULT NULL,
                marque              VARCHAR(100)    DEFAULT NULL,
                annee               INT             DEFAULT NULL,
                longueur            DECIMAL(5,2)    DEFAULT NULL,
                capacite_personnes  INT             DEFAULT NULL,
                prix_par_jour       DECIMAL(10,2)   DEFAULT NULL,
                statut              VARCHAR(20)     NOT NULL DEFAULT 'DISPONIBLE',
                description         LONGTEXT        DEFAULT NULL,
                photos              JSON            DEFAULT NULL,
                date_creation       DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_modification   DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                INDEX idx_statut (statut),
                INDEX IDX_PROPRIO (proprietaire_id),
                INDEX IDX_PORT (port_id),
                CONSTRAINT FK_BATEAU_USER FOREIGN KEY (proprietaire_id)
                    REFERENCES utilisateur (id) ON DELETE CASCADE,
                CONSTRAINT FK_BATEAU_PORT FOREIGN KEY (port_id)
                    REFERENCES port (id) ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Réservation
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (
                id                VARCHAR(36)     NOT NULL,
                bateau_id         VARCHAR(36)     NOT NULL,
                locataire_id      VARCHAR(36)     NOT NULL,
                date_debut        DATE            NOT NULL COMMENT '(DC2Type:date_immutable)',
                date_fin          DATE            NOT NULL COMMENT '(DC2Type:date_immutable)',
                statut            VARCHAR(20)     NOT NULL DEFAULT 'EN_ATTENTE',
                montant_total     DECIMAL(10,2)   DEFAULT NULL,
                commentaire       LONGTEXT        DEFAULT NULL,
                date_creation     DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_modification DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                INDEX idx_resa_statut (statut),
                INDEX idx_resa_dates (date_debut, date_fin),
                INDEX IDX_BATEAU (bateau_id),
                INDEX IDX_LOCATAIRE (locataire_id),
                CONSTRAINT FK_RESA_BATEAU FOREIGN KEY (bateau_id)
                    REFERENCES bateau (id) ON DELETE CASCADE,
                CONSTRAINT FK_RESA_USER FOREIGN KEY (locataire_id)
                    REFERENCES utilisateur (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Trajet (LogBook)
        $this->addSql(<<<'SQL'
            CREATE TABLE trajet (
                id                VARCHAR(36)     NOT NULL,
                bateau_id         VARCHAR(36)     NOT NULL,
                capitaine_id      VARCHAR(36)     NOT NULL,
                port_depart       VARCHAR(150)    DEFAULT NULL,
                port_arrivee      VARCHAR(150)    DEFAULT NULL,
                date_depart       DATETIME        NOT NULL  COMMENT '(DC2Type:datetime_immutable)',
                date_arrivee      DATETIME        DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                distance_milles   DECIMAL(8,2)    DEFAULT NULL,
                nombre_passagers  INT             DEFAULT NULL,
                notes             LONGTEXT        DEFAULT NULL,
                conditions_meteo  JSON            DEFAULT NULL,
                est_weekend       TINYINT(1)      NOT NULL DEFAULT 0,
                date_creation     DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                date_modification DATETIME        NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id),
                INDEX idx_trajet_date (date_depart),
                INDEX IDX_BATEAU_T (bateau_id),
                INDEX IDX_CAPITAINE (capitaine_id),
                CONSTRAINT FK_TRAJET_BATEAU FOREIGN KEY (bateau_id)
                    REFERENCES bateau (id) ON DELETE CASCADE,
                CONSTRAINT FK_TRAJET_USER FOREIGN KEY (capitaine_id)
                    REFERENCES utilisateur (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS trajet');
        $this->addSql('DROP TABLE IF EXISTS reservation');
        $this->addSql('DROP TABLE IF EXISTS bateau');
        $this->addSql('DROP TABLE IF EXISTS port');
        $this->addSql('DROP TABLE IF EXISTS refresh_token');
        $this->addSql('DROP TABLE IF EXISTS utilisateur');
    }
}
