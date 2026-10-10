import './stimulus_bootstrap.js';
import * as Turbo from '@hotwired/turbo';

/*
 * Point d'entrée JavaScript (chargé par importmap('app') dans base.html.twig).
 *
 * La navigation Turbo Drive est désactivée : les pages contiennent des scripts
 * (cartes Leaflet, graphique de saturation) qui s'initialisent au chargement
 * classique de la page. Les contrôleurs Stimulus restent actifs.
 */
Turbo.session.drive = false;
