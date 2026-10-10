# OptiMSAN

**Gestion du réseau d'accès MSAN et du support technique** pour un opérateur télécom tunisien.

OptiMSAN centralise l'inventaire des équipements MSAN (Zone → Site → Shelf → Carte → Port),
le suivi des pannes signalées par les clients et la gestion des demandes de nouvelle ligne,
avec trois espaces distincts : **client**, **technicien** et **administrateur**.

![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Symfony 7.4](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony&logoColor=white)
![MariaDB 10.4](https://img.shields.io/badge/MariaDB-10.4-003545?logo=mariadb&logoColor=white)
![Bootstrap 5.3](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![Tests](https://img.shields.io/badge/tests-93%20PHPUnit-2EA44F)

---

## Sommaire

1. [Captures d'écran](#captures-décran)
2. [Fonctionnalités par rôle](#fonctionnalités-par-rôle)
3. [Règles métier](#règles-métier)
4. [Technologies](#technologies)
5. [Installation avec XAMPP (Windows)](#installation-avec-xampp-windows)
6. [Création des comptes](#création-des-comptes)
7. [Lancer les tests](#lancer-les-tests)
8. [Structure du projet](#structure-du-projet)
9. [Sécurité](#sécurité)

---

## Captures d'écran

| Tableau de bord administrateur | Fiche ticket et cycle de vie |
|---|---|
| ![Tableau de bord](docs/img/admin-tableau-de-bord.png) | ![Ticket](docs/img/admin-ticket.png) |

| Saturation du réseau | Carte du réseau |
|---|---|
| ![Saturation](docs/img/admin-saturation.png) | ![Carte](docs/img/admin-carte.png) |

| Espace technicien | Espace client |
|---|---|
| ![Technicien](docs/img/technicien-accueil.png) | ![Client](docs/img/client-reclamations.png) |

| Connexion | Version mobile |
|---|---|
| ![Connexion](docs/img/connexion.png) | ![Mobile](docs/img/mobile.png) |

---

## Fonctionnalités par rôle

### Client
- Création de compte en ligne (nom, prénom, e-mail, CIN, adresse).
- **Signaler une panne** : type de panne, description, priorité, port concerné → un ticket `TCK-…` est créé.
- **Suivre ses réclamations** avec une frise : ouvert → en cours → résolu → fermé.
- **Demander une nouvelle ligne** (ADSL, VDSL, fibre FTTH, téléphone) en plaçant l'adresse sur une carte.
- Suivre l'avancement de ses demandes : en attente → validée → en cours d'installation → installée (ou refusée).

### Technicien
- Tableau de bord : tickets à traiter, pannes à proximité, tickets traités, tests réalisés.
- **Mes tickets** : mise à jour de l'état et ajout de notes d'intervention (la description du client est conservée).
- **Pannes à proximité** : tickets ouverts dont le site MSAN est à moins de **15 km**, du plus proche au plus éloigné.
- **Qualification de ligne xDSL** : saisie des mesures (SNR, atténuation) ; distance au site et débit estimé calculés automatiquement.
- **Ma position GPS** : saisie manuelle, géolocalisation du navigateur ou clic sur la carte.
- Carte des sites MSAN et des demandes en attente.

### Administrateur
- **Tableau de bord** : tickets ouverts / en cours, demandes en attente, taux de saturation global,
  techniciens disponibles, derniers tickets, sites les plus chargés, répartition par état.
- **Tickets** : affectation d'un technicien et changement d'état (selon les transitions autorisées).
- **Demandes de ligne** : tests techniques, affectation d'un port libre, validation ou refus.
- **Inventaire réseau** : zones, sites MSAN (positionnés sur la carte), shelves, cartes et ports.
- **Saturation** : taux d'occupation par site (vert < 70 %, orange < 90 %, rouge au-delà) et graphique.
- **Carte du réseau** (Leaflet / OpenStreetMap).
- **Import / Export Excel** des ports (`.xlsx`, PhpSpreadsheet) avec contrôle ligne par ligne.
- **Équipe** : création de comptes administrateur et technicien, suppression d'un technicien sans historique.

Tous les tableaux disposent d'une **recherche instantanée** et d'un **filtre par état** côté navigateur.

---

## Règles métier

Les règles sont isolées dans des services testés unitairement (`src/Service`) :

| Service | Rôle |
|---|---|
| `ReclamationWorkflow` | Transitions d'un ticket. « En cours » et « résolu » exigent un technicien ; seul l'administrateur peut fermer ou rouvrir. |
| `DemandeLigneWorkflow` | Transitions d'une demande et affectation des ports : seul un port **libre** peut être affecté, il passe à « occupé » ; un refus libère le port. |
| `GeoDistanceService` | Distance entre deux points GPS (formule de Haversine). |
| `XdslEstimationService` | Débit estimé selon le standard (ADSL, ADSL2, ADSL2+, VDSL2) et la distance : décroissance exponentielle avec un débit plancher. |

Autres contrôles : capacité d'une carte (`CapaciteCarte`), unicité d'un numéro de port sur une carte,
impossibilité de supprimer un élément encore utilisé, jeton CSRF sur toutes les actions sensibles.

---

## Technologies

| Domaine | Outils |
|---|---|
| Back-end | PHP 8.3, Symfony 7.4, Doctrine ORM 3, Twig |
| Base de données | MariaDB 10.4 (XAMPP) |
| Front-end | Bootstrap 5.3, Bootstrap Icons, IBM Plex Sans / Mono, AssetMapper, Stimulus, Turbo |
| Cartographie | Leaflet 1.9 + OpenStreetMap |
| Graphiques | Chart.js 4 |
| Excel | PhpSpreadsheet |
| Tests et qualité | PHPUnit 12, `lint:container`, `lint:twig`, `lint:yaml`, GitHub Actions |

Aucun outil Node.js (npm, Webpack) n'est nécessaire : les fichiers JavaScript sont servis par AssetMapper.

---

## Installation avec XAMPP (Windows)

### 1. Prérequis

- **XAMPP** (pour MariaDB 10.4) : <https://www.apachefriends.org>
- **PHP 8.3** en ligne de commande, avec les extensions `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`
  et, conseillé, `intl` (formats de nombres en français).
  > PHPUnit 12 exige PHP 8.3. Si le PHP de XAMPP est en 8.2, installez PHP 8.3 à part
  > (par exemple dans `C:\PHP`) et placez-le dans le `PATH`.
- **Composer 2** : <https://getcomposer.org>
- **Git** : <https://git-scm.com>

Vérifiez dans un terminal :

```bash
php -v          # PHP 8.3.x
composer -V
git --version
```

### 2. Récupérer le projet

```bash
git clone https://github.com/<votre-compte>/OptiMSAN.git
cd OptiMSAN
composer install
```

`composer install` installe les dépendances, puis télécharge Stimulus et Turbo (`importmap:install`).

### 3. Configurer la base de données

1. Démarrez **MySQL** dans le panneau de contrôle XAMPP.
2. Créez un fichier `.env.local` (jamais publié sur GitHub) :

   ```dotenv
   DATABASE_URL="mysql://root:@127.0.0.1:3306/optimsan?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
   ```

   Adaptez l'utilisateur et le mot de passe si votre MariaDB n'utilise pas `root` sans mot de passe.

3. Créez la base et les tables :

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

> **Sans XAMPP :** `docker compose up -d` lance une base MariaDB 10.4 (voir `compose.yaml`),
> avec `DATABASE_URL="mysql://app:app@127.0.0.1:3306/optimsan?serverVersion=10.4.32-MariaDB&charset=utf8mb4"`.

### 4. Créer un premier compte administrateur

```bash
php bin/console app:create-admin
```

### 5. Lancer l'application

```bash
php -S 127.0.0.1:8000 -t public
```

Ouvrez <http://127.0.0.1:8000> dans le navigateur. Avec le [CLI Symfony](https://symfony.com/download),
vous pouvez aussi utiliser `symfony serve`.

---

## Création des comptes

| Compte | Comment le créer |
|---|---|
| Administrateur | `php bin/console app:create-admin` (nom, prénom, e-mail, téléphone, mot de passe, niveau d'accès) |
| Technicien | `php bin/console app:create-technicien` (… matricule, latitude et longitude) ou, connecté en administrateur, **Équipe → Nouveau technicien** |
| Client | `php bin/console app:create-client` (… CIN, adresse) ou le bouton **Créer un compte** de la page de connexion |

Les commandes posent leurs questions une par une ; le mot de passe est saisi sans être affiché.

---

## Lancer les tests

```bash
php vendor/bin/phpunit
```

Résultat attendu : `OK (93 tests, 136 assertions)`. Les tests n'utilisent pas la base de données :

| Fichier | Ce qui est testé |
|---|---|
| `tests/Entity/ClientValidationTest.php` | Règles du formulaire d'inscription (CIN, téléphone, e-mail…) |
| `tests/Entity/EquipementValidationTest.php` | Contraintes de l'inventaire (IP, code postal, capacité…) |
| `tests/Service/ReclamationWorkflowTest.php` | Cycle de vie des tickets |
| `tests/Service/DemandeLigneWorkflowTest.php` | Cycle de vie des demandes et affectation des ports |
| `tests/Service/GeoDistanceServiceTest.php` | Calcul de distance GPS |
| `tests/Service/XdslEstimationServiceTest.php` | Estimation du débit xDSL |

Vérifications complémentaires :

```bash
php bin/console lint:container
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console doctrine:schema:validate
```

Ces contrôles et les tests sont relancés automatiquement par **GitHub Actions** à chaque push
(`.github/workflows/ci.yml`).

---

## Structure du projet

```text
OptiMSAN/
├── assets/                  JavaScript (AssetMapper)
│   ├── app.js               point d'entrée (Stimulus, Turbo)
│   └── controllers/         contrôleurs Stimulus : table-filter, toast, csrf-protection
├── config/                  configuration Symfony (sécurité, Doctrine, Twig…)
├── docs/
│   ├── img/                 captures d'écran du README
│   └── dossier-de-test/     dossier de tests (Word)
├── migrations/              migrations Doctrine
├── public/
│   ├── css/app.css          design system (variables, composants)
│   └── image/               logos
├── src/
│   ├── Command/             app:create-admin, app:create-technicien, app:create-client
│   ├── Controller/          un contrôleur par espace + CRUD de l'inventaire
│   ├── Entity/              Utilisateur (Client, Technicien, Administrateur), inventaire, tickets…
│   ├── Form/                formulaires
│   ├── Repository/          requêtes (saturation, pannes proches, carte…)
│   ├── Security/            authentification par formulaire
│   ├── Service/             règles métier (workflows, distance, débit)
│   ├── Twig/                filtres |libelle et |ton (libellés et couleurs des états)
│   └── Validator/           contrainte CapaciteCarte
├── templates/
│   ├── layout/              squelettes : app, admin, technicien, client, auth
│   ├── _ui/                 composants : macros (badge, frise, jauge…), logo, zone de suppression
│   ├── admin/ client/ technicien/
│   └── zone/ site_msan/ shelf/ slot_card/ port_msan/
└── tests/                   tests PHPUnit
```

### Adresses principales

| Espace | URL |
|---|---|
| Accueil, connexion, inscription | `/`, `/login`, `/register` |
| Administrateur | `/admin/`, `/admin/reclamations`, `/admin/demandes-ligne`, `/admin/saturation`, `/admin/carte`, `/admin/equipe` |
| Inventaire | `/admin/zones`, `/admin/sites`, `/admin/shelves`, `/admin/cartes`, `/admin/ports`, `/admin/import-ports` |
| Technicien | `/technicien/`, `/technicien/mes-tickets`, `/technicien/pannes-proximite`, `/technicien/qualifications`, `/technicien/position` |
| Client | `/client/`, `/client/reclamations`, `/client/demandes-ligne` |

---

## Sécurité

- Trois rôles (`ROLE_CLIENT`, `ROLE_TECHNICIEN`, `ROLE_ADMIN`) contrôlés par `access_control` **et** `#[IsGranted]`.
- Jeton CSRF sur la connexion, les formulaires, les suppressions et l'import Excel.
- Un technicien ne peut modifier que les tickets qui lui sont assignés ; un administrateur ne peut pas supprimer un autre administrateur.
- Les textes affichés sur la carte sont échappés (protection XSS).
- Les fichiers `.env.local` et `vendor/` ne sont jamais publiés. `.env.dev` contient une clé `APP_SECRET`
  **de développement uniquement** : en production, définissez votre propre `APP_SECRET`
  (variable d'environnement ou `php bin/console secrets:set APP_SECRET --env=prod`).

---

## Auteur

Projet réalisé par *[votre nom]* — *[établissement, formation, année]*.
