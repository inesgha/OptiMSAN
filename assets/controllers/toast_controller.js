import { Controller } from '@hotwired/stimulus';

/*
 * Toast de message flash (voir templates/_flashes.html.twig).
 * Se ferme seul après « délai » millisecondes (0 = reste affiché).
 * Le minuteur est suspendu tant que la souris ou le focus clavier est sur le message.
 */
export default class extends Controller {
    static values = { delai: { type: Number, default: 6000 } };

    connect() {
        this.suspendre = this.suspendre.bind(this);
        this.reprendre = this.reprendre.bind(this);
        this.element.addEventListener('mouseenter', this.suspendre);
        this.element.addEventListener('focusin', this.suspendre);
        this.element.addEventListener('mouseleave', this.reprendre);
        this.element.addEventListener('focusout', this.reprendre);
        this.reprendre();
    }

    disconnect() {
        clearTimeout(this.minuteur);
        this.element.removeEventListener('mouseenter', this.suspendre);
        this.element.removeEventListener('focusin', this.suspendre);
        this.element.removeEventListener('mouseleave', this.reprendre);
        this.element.removeEventListener('focusout', this.reprendre);
    }

    suspendre() {
        clearTimeout(this.minuteur);
    }

    reprendre() {
        clearTimeout(this.minuteur);
        if (this.delaiValue > 0) {
            this.minuteur = setTimeout(() => this.fermer(), this.delaiValue);
        }
    }

    fermer() {
        if (window.bootstrap?.Toast) {
            window.bootstrap.Toast.getOrCreateInstance(this.element).hide();
        } else {
            this.element.remove();
        }
    }
}
