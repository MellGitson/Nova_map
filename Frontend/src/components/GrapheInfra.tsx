import { useEffect, useRef } from "react";
import * as d3 from "d3";
import type { NoeudGraphe, LienGraphe, TypeComposant } from "../types";

type PropsGraphe = {
  noeuds: NoeudGraphe[];
  liens: LienGraphe[];
  onClicNoeud?: (noeud: NoeudGraphe) => void;
};

/** Couleur de fond du nœud selon le type de composant. */
const COULEUR_PAR_TYPE: Record<TypeComposant, string> = {
  serveur : "#4fc3f7",
  bdd     : "#a5d6a7",
  api     : "#ffb74d",
  cdn     : "#ce93d8",
  cloud   : "#ef9a9a",
};

/** Icône (emoji) affichée dans le nœud. */
const ICONE_PAR_TYPE: Record<TypeComposant, string> = {
  serveur : "🖥",
  bdd     : "🗄",
  api     : "⚡",
  cdn     : "🌐",
  cloud   : "☁",
};

const RAYON_NOEUD = 28;

/**
 * Composant graphe D3 Force Layout.
 *
 * Affiche les composants d'un projet sous forme de nœuds reliés par des arêtes.
 * Les nœuds sont déplaçables par glisser-déposer.
 * Un clic sur un nœud déclenche onClicNoeud.
 */
export function GrapheInfra({ noeuds, liens, onClicNoeud }: PropsGraphe) {
  const svgRef = useRef<SVGSVGElement>(null);

  useEffect(() => {
    if (!svgRef.current || noeuds.length === 0) return;

    const largeur  = svgRef.current.clientWidth  || 900;
    const hauteur  = svgRef.current.clientHeight || 600;

    // Copie profonde pour que D3 puisse muter les objets (ajout x, y, vx, vy)
    const noeudsD3: NoeudGraphe[] = noeuds.map((n) => ({
      ...n,
      x: n.position_x || largeur / 2 + (Math.random() - 0.5) * 200,
      y: n.position_y || hauteur / 2 + (Math.random() - 0.5) * 200,
    }));

    // D3 forceLink exige les propriétés "source" et "target" (noms obligatoires)
    const liensD3 = liens.map((l) => ({
      ...l,
      source: l.source_id,
      target: l.cible_id,  // D3 utilise "target", pas "cible"
    }));

    // Vider le SVG avant de redessiner
    const svg = d3.select(svgRef.current);
    svg.selectAll("*").remove();

    // Groupe principal (permet le zoom/pan)
    const g = svg.append("g");

    // Zoom
    const zoom = d3.zoom<SVGSVGElement, unknown>()
      .scaleExtent([0.3, 3])
      .on("zoom", (event) => g.attr("transform", event.transform));
    svg.call(zoom);

    // Définition de la flèche pour les arêtes
    svg.append("defs").append("marker")
      .attr("id", "fleche")
      .attr("viewBox", "0 -5 10 10")
      .attr("refX", RAYON_NOEUD + 12)
      .attr("refY", 0)
      .attr("markerWidth", 8)
      .attr("markerHeight", 8)
      .attr("orient", "auto")
      .append("path")
      .attr("d", "M0,-5L10,0L0,5")
      .attr("fill", "#666");

    // Simulation de forces
    const simulation = d3.forceSimulation<NoeudGraphe>(noeudsD3)
      .force("lien", d3.forceLink(liensD3)
        .id((d: any) => d.id)
        .distance(160)
      )
      .force("repulsion", d3.forceManyBody().strength(-400))
      .force("centre",    d3.forceCenter(largeur / 2, hauteur / 2))
      .force("collision", d3.forceCollide(RAYON_NOEUD + 20));

    // Arêtes
    const aretes = g.append("g").attr("class", "aretes")
      .selectAll("line")
      .data(liensD3)
      .join("line")
      .attr("stroke", "#555")
      .attr("stroke-width", 1.5)
      .attr("stroke-dasharray", "6 3")
      .attr("marker-end", "url(#fleche)");

    // Libellés des arêtes
    const libellésAretes = g.append("g").attr("class", "libelles-aretes")
      .selectAll("text")
      .data(liensD3)
      .join("text")
      .text((d) => d.type_lien)
      .attr("font-size", "10px")
      .attr("fill", "#888")
      .attr("text-anchor", "middle");

    // Groupes de nœuds
    const groupesNoeuds = g.append("g").attr("class", "noeuds")
      .selectAll<SVGGElement, NoeudGraphe>("g")
      .data(noeudsD3)
      .join("g")
      .attr("cursor", "pointer")
      .call(
        d3.drag<SVGGElement, NoeudGraphe>()
          .on("start", (event, d) => {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
            d.fy = d.y;
          })
          .on("drag", (event, d) => {
            d.fx = event.x;
            d.fy = event.y;
          })
          .on("end", (event, d) => {
            if (!event.active) simulation.alphaTarget(0);
            d.fx = null;
            d.fy = null;
          })
      )
      .on("click", (_event, d) => onClicNoeud?.(d));

    // Cercle du nœud
    groupesNoeuds.append("circle")
      .attr("r", RAYON_NOEUD)
      .attr("fill", (d) => COULEUR_PAR_TYPE[d.type] ?? "#90a4ae")
      .attr("stroke", "#fff")
      .attr("stroke-width", 2)
      .attr("opacity", 0.9);

    // Icône dans le nœud
    groupesNoeuds.append("text")
      .text((d) => ICONE_PAR_TYPE[d.type] ?? "?")
      .attr("text-anchor", "middle")
      .attr("dominant-baseline", "central")
      .attr("font-size", "18px")
      .attr("dy", "-6px");

    // Score dans le nœud (petit cercle en bas à droite)
    groupesNoeuds.filter((d) => d.score !== null)
      .append("circle")
      .attr("r", 12)
      .attr("cx", RAYON_NOEUD - 4)
      .attr("cy", RAYON_NOEUD - 4)
      .attr("fill", (d) => scoreCouleur(d.score ?? 100))
      .attr("stroke", "#1a1a2e")
      .attr("stroke-width", 1.5);

    groupesNoeuds.filter((d) => d.score !== null)
      .append("text")
      .text((d) => String(d.score ?? ""))
      .attr("x", RAYON_NOEUD - 4)
      .attr("y", RAYON_NOEUD - 4)
      .attr("text-anchor", "middle")
      .attr("dominant-baseline", "central")
      .attr("font-size", "8px")
      .attr("font-weight", "bold")
      .attr("fill", "#fff");

    // Nom du composant sous le nœud
    groupesNoeuds.append("text")
      .text((d) => d.nom.length > 14 ? d.nom.slice(0, 12) + "…" : d.nom)
      .attr("text-anchor", "middle")
      .attr("dy", RAYON_NOEUD + 14)
      .attr("font-size", "12px")
      .attr("fill", "#e0e0e0");

    // Mise à jour des positions à chaque tick de la simulation
    simulation.on("tick", () => {
      aretes
        .attr("x1", (d: any) => d.source.x)
        .attr("y1", (d: any) => d.source.y)
        .attr("x2", (d: any) => d.target.x)
        .attr("y2", (d: any) => d.target.y);

      libellésAretes
        .attr("x", (d: any) => (d.source.x + d.target.x) / 2)
        .attr("y", (d: any) => (d.source.y + d.target.y) / 2);

      groupesNoeuds.attr("transform", (d) => `translate(${d.x ?? 0},${d.y ?? 0})`);
    });

    return () => {
      simulation.stop();
    };
  }, [noeuds, liens, onClicNoeud]);

  if (noeuds.length === 0) {
    return (
      <div className="graphe-vide">
        <p className="text-muted">
          Aucun composant dans ce projet. Ajoutez votre premier composant pour
          commencer à cartographier votre infrastructure.
        </p>
      </div>
    );
  }

  return (
    <svg
      ref={svgRef}
      className="graphe-svg"
      width="100%"
      height="100%"
      style={{ background: "#0d0d1a", borderRadius: "12px" }}
    />
  );
}

function scoreCouleur(score: number): string {
  if (score >= 80) return "#00c853";
  if (score >= 50) return "#ff6f00";
  return "#d32f2f";
}
