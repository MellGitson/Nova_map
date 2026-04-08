<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408140536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte CHANGE type type ENUM(\'cve_critique\',\'score_baisse\',\'port_expose\',\'ssl_expire\') NOT NULL, CHANGE severite severite ENUM(\'critique\',\'warning\',\'info\') NOT NULL DEFAULT \'warning\'');
        $this->addSql('ALTER TABLE analyse CHANGE declencheur declencheur ENUM(\'manuel\',\'cron\',\'alerte\') NOT NULL DEFAULT \'manuel\', CHANGE statut statut ENUM(\'en_cours\',\'termine\',\'erreur\') NOT NULL DEFAULT \'en_cours\'');
        $this->addSql('ALTER TABLE composant CHANGE type type ENUM(\'serveur\',\'bdd\',\'api\',\'cdn\',\'cloud\') NOT NULL, CHANGE environnement environnement ENUM(\'prod\',\'staging\',\'dev\') NOT NULL DEFAULT \'prod\'');
        $this->addSql('ALTER TABLE composant_cve CHANGE statut statut ENUM(\'active\',\'resolue\',\'ignoree\') NOT NULL DEFAULT \'active\'');
        $this->addSql('ALTER TABLE cve CHANGE severite severite ENUM(\'critique\',\'eleve\',\'moyen\',\'faible\') NOT NULL DEFAULT \'moyen\'');
        $this->addSql('ALTER TABLE journal_audit CHANGE niveau niveau ENUM(\'info\',\'warning\',\'critical\') NOT NULL DEFAULT \'info\'');
        $this->addSql('ALTER TABLE lien_composant CHANGE type_lien type_lien ENUM(\'http\',\'tcp\',\'grpc\',\'amqp\',\'ssh\',\'autre\') NOT NULL DEFAULT \'http\'');
        $this->addSql('ALTER TABLE membre CHANGE role role ENUM(\'user\',\'manager\',\'admin\') NOT NULL DEFAULT \'user\'');
        $this->addSql('ALTER TABLE notification CHANGE statut statut ENUM(\'non_lue\',\'lue\',\'resolue\') NOT NULL DEFAULT \'non_lue\'');
        $this->addSql('ALTER TABLE rapport CHANGE type type ENUM(\'audit\',\'conformite\') NOT NULL DEFAULT \'audit\'');
        $this->addSql('ALTER TABLE resultat_api CHANGE api_source api_source ENUM(\'shodan\',\'nvd\',\'ssllabs\',\'secheaders\',\'ipinfo\') NOT NULL');
        $this->addSql('ALTER TABLE webhook CHANGE type_service type_service ENUM(\'slack\',\'teams\',\'discord\',\'custom\') NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte CHANGE type type ENUM(\'cve_critique\', \'score_baisse\', \'port_expose\', \'ssl_expire\') NOT NULL, CHANGE severite severite ENUM(\'critique\', \'warning\', \'info\') DEFAULT \'warning\' NOT NULL');
        $this->addSql('ALTER TABLE analyse CHANGE declencheur declencheur ENUM(\'manuel\', \'cron\', \'alerte\') DEFAULT \'manuel\' NOT NULL, CHANGE statut statut ENUM(\'en_cours\', \'termine\', \'erreur\') DEFAULT \'en_cours\' NOT NULL');
        $this->addSql('ALTER TABLE composant CHANGE type type ENUM(\'serveur\', \'bdd\', \'api\', \'cdn\', \'cloud\') NOT NULL, CHANGE environnement environnement ENUM(\'prod\', \'staging\', \'dev\') DEFAULT \'prod\' NOT NULL');
        $this->addSql('ALTER TABLE composant_cve CHANGE statut statut ENUM(\'active\', \'resolue\', \'ignoree\') DEFAULT \'active\' NOT NULL');
        $this->addSql('ALTER TABLE cve CHANGE severite severite ENUM(\'critique\', \'eleve\', \'moyen\', \'faible\') DEFAULT \'moyen\' NOT NULL');
        $this->addSql('ALTER TABLE journal_audit CHANGE niveau niveau ENUM(\'info\', \'warning\', \'critical\') DEFAULT \'info\' NOT NULL');
        $this->addSql('ALTER TABLE lien_composant CHANGE type_lien type_lien ENUM(\'http\', \'tcp\', \'grpc\', \'amqp\', \'ssh\', \'autre\') DEFAULT \'http\' NOT NULL');
        $this->addSql('ALTER TABLE membre CHANGE role role ENUM(\'user\', \'manager\', \'admin\') DEFAULT \'user\' NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE statut statut ENUM(\'non_lue\', \'lue\', \'resolue\') DEFAULT \'non_lue\' NOT NULL');
        $this->addSql('ALTER TABLE rapport CHANGE type type ENUM(\'audit\', \'conformite\') DEFAULT \'audit\' NOT NULL');
        $this->addSql('ALTER TABLE resultat_api CHANGE api_source api_source ENUM(\'shodan\', \'nvd\', \'ssllabs\', \'secheaders\', \'ipinfo\') NOT NULL');
        $this->addSql('ALTER TABLE webhook CHANGE type_service type_service ENUM(\'slack\', \'teams\', \'discord\', \'custom\') NOT NULL');
    }
}
