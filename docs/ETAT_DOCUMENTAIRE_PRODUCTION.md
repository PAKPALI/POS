# État documentaire et préparation de production

Dernière mise à jour : 18 septembre 2026

## Décision de lecture

Le développement fonctionnel et la validation staging sont clôturés pour le périmètre SaaS actuel. Le propriétaire confirme également le fonctionnement en production du parcours de paiement et des abonnements. Cette confirmation valide le cœur de monétisation, mais ne remplace pas la recette d’exploitation complète : workers, cron, SMTP, sauvegardes, supervision, sécurité et procédures de reprise doivent rester vérifiés séparément.

La phase restante est la consolidation opérationnelle de production : conserver les preuves de paiement, vérifier les services périphériques, finaliser les smoke tests et activer progressivement les réglages sensibles.

## Niveau de préparation au 18 septembre 2026

| Périmètre | Niveau | État constaté |
| --- | --- | --- |
| Développement fonctionnel | 100 % | Clôturé pour le périmètre documenté. |
| Validation staging | 100 % | Parcours SaaS, abonnements, intégrations et recette responsive déclarés validés. |
| Monétisation production | Validée sur le parcours principal | Paiements et abonnements confirmés fonctionnels par le propriétaire. Conserver les références de transactions dans le journal de mise en production. |
| Exploitation production complète | En cours de clôture | Workers, cron, SMTP, sauvegarde-restauration, supervision, en-têtes, callbacks et plan de reprise doivent encore être attestés séparément. |

**Décision recommandée :** la plateforme peut poursuivre une ouverture contrôlée/pilote. La validation « exploitation production complète » ne doit être prononcée qu’après clôture des contrôles opérationnels restants.

## Matrice des documents

| Document | Rôle | Statut courant |
| --- | --- | --- |
| `FREEBUFF_HANDOFF.md` | Source de vérité de la reprise et des décisions récentes | Clôture fonctionnelle/staging et addendum production du 18 septembre |
| `RAPPORT_GLOBAL_SAAS.md` | État global du SaaS, performance et exploitation | Mis à jour au 18 septembre ; monétisation production confirmée, exploitation globale à clôturer |
| `RAPPORT_ADMINISTRATION_SAAS.md` | État de la console plateforme | Mis à jour au 18 septembre ; console validée et paiements/abonnements production confirmés |
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
| `DEPLOIEMENT_O2SWITCH.md` | Procédure de mise en production | Checklist opérationnelle encore applicable ; paiement/abonnement production déjà confirmés |

## Convention de conservation

Chaque sujet possède un seul document maître éditable. Les fichiers HTML/Markdown sont les sources à mettre à jour ; les PDF sont conservés comme exports de diffusion ou comme preuves historiques. Un export ne doit jamais devenir une seconde source de vérité.

## Règle pour la migration

En cas de divergence, lire dans cet ordre :

1. le présent fichier, pour savoir quel document fait foi ;
2. `FREEBUFF_HANDOFF.md`, section « État production — 18 septembre 2026 » puis « Clôture locale — 15 septembre 2026 » ;
3. `RAPPORT_GLOBAL_SAAS.md` et `RAPPORT_ADMINISTRATION_SAAS.md` ;
4. `DEPLOIEMENT_O2SWITCH.md` pour les opérations de production ;
5. les cahiers normatifs pour vérifier une règle métier ou visuelle.

Ne pas rouvrir un lot de développement uniquement parce qu’une ancienne entrée datée conserve son état de l’époque. Toute nouvelle évolution après production doit faire l’objet d’une décision séparée, d’un test ciblé et d’une mise à jour de cette matrice.
