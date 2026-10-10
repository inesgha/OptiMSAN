import { Controller } from '@hotwired/stimulus';

/*
 * Recherche et filtre côté client dans un tableau (sans rechargement de page).
 *
 *   <div data-controller="table-filter">
 *     <input data-table-filter-target="query" data-action="input->table-filter#filter">
 *     <select data-table-filter-target="etat" data-action="change->table-filter#filter">…</select>
 *     <span data-table-filter-target="count"></span>
 *     <tr data-table-filter-target="row" data-etat="ouvert">…</tr>
 *     <tr data-table-filter-target="empty" hidden>Aucun résultat</tr>
 *   </div>
 *
 * La recherche ignore les majuscules et les accents ; plusieurs mots doivent tous être présents.
 */
export default class extends Controller {
    static targets = ['query', 'etat', 'row', 'count', 'empty'];

    connect() {
        this.textes = new Map();
        this.filter();
    }

    filter() {
        const mots = this.hasQueryTarget ? normaliser(this.queryTarget.value).split(' ').filter(Boolean) : [];
        const etat = this.hasEtatTarget ? this.etatTarget.value : '';
        let visibles = 0;

        this.rowTargets.forEach((row) => {
            const texte = this.texteDe(row);
            const correspond = mots.every((mot) => texte.includes(mot))
                && ('' === etat || row.dataset.etat === etat);

            row.hidden = !correspond;
            if (correspond) {
                visibles++;
            }
        });

        if (this.hasCountTarget) {
            const total = this.rowTargets.length;
            this.countTarget.textContent = visibles === total
                ? `${total} élément${total > 1 ? 's' : ''}`
                : `${visibles} sur ${total}`;
        }

        if (this.hasEmptyTarget) {
            this.emptyTarget.hidden = visibles > 0;
        }
    }

    reset() {
        if (this.hasQueryTarget) {
            this.queryTarget.value = '';
        }
        if (this.hasEtatTarget) {
            this.etatTarget.value = '';
        }
        this.filter();
        if (this.hasQueryTarget) {
            this.queryTarget.focus();
        }
    }

    texteDe(row) {
        if (!this.textes.has(row)) {
            this.textes.set(row, normaliser(row.textContent));
        }

        return this.textes.get(row);
    }
}

function normaliser(texte) {
    return texte
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}
