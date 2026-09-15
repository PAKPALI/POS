# État documentaire avant production

Dernière mise à jour : 15 septembre 2026

## Décision de lecture

Le développement fonctionnel et la validation staging sont clôturés pour le périmètre SaaS actuel. Les mentions « à faire », « reste à valider » ou les pourcentages plus anciens présents dans des entrées datées décrivent un état intermédiaire et ne constituent pas des tâches ouvertes.

La seule étape restante est la migration production : sauvegarde, déploiement, secrets et URLs propres à la production, migrations, cache, workers, cron, smoke tests, supervision et activation progressive des réglages sensibles.

## Matrice des documents

| Document | Rôle | Statut courant |
| --- | --- | --- |
| `FREEBUFF_HANDOFF.md` | Source de vérité de la reprise et des décisions récentes | Clôture fonctionnelle et staging validés ; anciennes entrées explicitement historiques |
| `RAPPORT_GLOBAL_SAAS.md` | État global du SaaS, performance et exploitation | Mis à jour au 14 septembre ; développement terminé, bascule production restante |
| `RAPPORT_ADMINISTRATION_SAAS.md` | État de la console plateforme | Mis à jour au 14 septembre ; console et pilotage partenaires validés |
| `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` | Règles normatives UI/UX et critères d’acceptation | Référence active ; migration et recette staging validées |
| `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.pdf` | Export du cahier UI/UX | Référence de diffusion ; le Markdown est le document maître |
| `CAHIER_ARCHITECTURE_PLATEFORME_PARTENAIRES.md` | Architecture, règles financières et sécurité partenaires | Référence normative ; implémentation fonctionnelle livrée |
| `CAHIER_DES_CHARGES_ADMINISTRATION_SAAS.pdf` | Cahier fonctionnel de la console | Référence fonctionnelle ; ne pas utiliser pour l’avancement |
| `CAHIER_DES_CHARGES_SITE_VITRINE_POS_SAAS_AFRIQUE.pdf` | Cahier fonctionnel du site public | Référence fonctionnelle ; validation staging déclarée |
| `STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.html` | Source éditable de la stratégie tarifaire | Document maître commercial ; le PDF est son export |
| `STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.pdf` | Référence commerciale des plans | Normative ; ne pas modifier les règles sans nouvelle décision produit |
| `documentation-saas-pos.html` | Documentation de migration historique conservée comme archive | Aucun export associé ; ses anciens pourcentages ne représentent plus l’état courant |
| `AUDIT_ISOLATION_TENANT_2026-08-24.md` | Preuve d’audit tenant datée | Constats historiques avec addendum de statut ; pas une todo-list courante |
| `AUDIT_SECURITE_OFFENSIF_2026-08-25.md` | Preuve d’audit sécurité datée | Constats historiques avec addendum de statut ; configuration production à reproduire |
| `DEPLOIEMENT_O2SWITCH.md` | Procédure de mise en production | Checklist opérationnelle encore applicable |

## Convention de conservation

Chaque sujet possède un seul document maître éditable. Les fichiers HTML/Markdown sont les sources à mettre à jour ; les PDF sont conservés comme exports de diffusion ou comme preuves historiques. Un export ne doit jamais devenir une seconde source de vérité.

## Règle pour la migration

En cas de divergence, lire dans cet ordre :

1. le présent fichier, pour savoir quel document fait foi ;
2. `FREEBUFF_HANDOFF.md`, section « Clôture locale — 15 septembre 2026 » ;
3. `RAPPORT_GLOBAL_SAAS.md` et `RAPPORT_ADMINISTRATION_SAAS.md` ;
4. `DEPLOIEMENT_O2SWITCH.md` pour les opérations de production ;
5. les cahiers normatifs pour vérifier une règle métier ou visuelle.

Ne pas rouvrir un lot de développement uniquement parce qu’une ancienne entrée datée conserve son état de l’époque. Toute nouvelle évolution après production doit faire l’objet d’une décision séparée, d’un test ciblé et d’une mise à jour de cette matrice.
