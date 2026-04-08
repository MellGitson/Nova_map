import { Link } from "react-router-dom";
import { Button } from "../components";

export function HomePage() {
  return (
    <div className="page home-page">
      <section className="hero">
        <h1>Cartographiez et sécurisez votre infrastructure</h1>
        <p className="lead">
          NovaMap vous permet de visualiser vos composants sous forme de graphe,
          d'identifier les vulnérabilités connues (CVE) et de suivre le score
          de sécurité de chaque service en temps réel.
        </p>
        <div className="hero-actions">
          <Link to="/register">
            <Button>Commencer</Button>
          </Link>
          <Link to="/login">
            <Button variant="secondary">Se connecter</Button>
          </Link>
        </div>
      </section>

      <section className="features">
        <div className="feature-card">
          <h3>Graphe interactif</h3>
          <p>Visualisez les dépendances entre vos serveurs, bases de données, APIs et services cloud.</p>
        </div>
        <div className="feature-card">
          <h3>Analyse CVE</h3>
          <p>Détectez les vulnérabilités connues via Shodan, NVD/NIST et SSL Labs.</p>
        </div>
        <div className="feature-card">
          <h3>Score de sécurité</h3>
          <p>Chaque composant reçoit un score sur 100 basé sur 5 critères d'analyse.</p>
        </div>
      </section>
    </div>
  );
}
