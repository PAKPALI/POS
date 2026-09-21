# Documentation essentielle

Cette arborescence distingue les références actives, les procédures d’exploitation et les archives utiles à la maintenance ; les archives ne constituent pas des sources d’avancement.

## Références à consulter selon le besoin

- `FREEBUFF_HANDOFF.md` : état de reprise, décisions récentes, risques et contrôles avant modification.
- `ETAT_DOCUMENTAIRE_PRODUCTION.md` : matrice de statut, niveau de préparation et ordre de lecture pendant la clôture de production.
- `RAPPORT_GLOBAL_SAAS.md` : état fonctionnel et opérationnel consolidé du SaaS.
- `RAPPORT_ADMINISTRATION_SAAS.md` : console plateforme, sécurité et suivi de l’administration centrale.
- `GUIDE_KPRIMEPAY.md` : intégration, rapprochement et exploitation des paiements.
- `CAHIER_ARCHITECTURE_PLATEFORME_PARTENAIRES.md` : référence normative de l’architecture, des règles financières, du schéma de données, des flux KPrimePay payout et de la sécurité partenaires ; l’implémentation fonctionnelle est livrée.
- `DEPLOIEMENT_O2SWITCH.md` : déploiement et exploitation de production.
- `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` : document maître des règles UI/UX, de l’accessibilité et des attentes des actions serveur ; le PDF est un export de référence.
- `CONVENTION_EMAILS.md` : contrat visuel et règles de sécurité pour les e-mails HTML applicatifs.
- `STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.html` et `.pdf` : source éditable et export PDF de la référence commerciale normative des plans.
- `documentation-saas-pos.html` : documentation de migration historique conservée comme archive ; ne pas utiliser ses anciens pourcentages comme état courant.
- `AUDIT_ISOLATION_TENANT_2026-08-24.md` et `AUDIT_SECURITE_OFFENSIF_2026-08-25.md` : résultats d’audits de sécurité encore utiles comme preuves de référence.

Les anciens cahiers des charges terminés, leurs prompts et leurs scripts de génération ne sont plus des documents actifs. Les informations opérationnelles qu’ils apportaient sont consolidées dans les rapports permanents ci-dessus. Les fichiers conservés servent de références fonctionnelles, d’exports ou de preuves historiques ; ils ne constituent pas des rapports d’avancement et leurs anciens pourcentages ou statuts ne doivent pas être lus comme l’état courant.

## Documents maîtres et exports

Un seul document éditable fait foi par sujet. Les PDF conservés sont des exports de diffusion ou des preuves historiques : ils ne doivent pas être modifiés séparément ni utilisés comme rapport d’avancement.

| Sujet | Document maître | Export ou archive associée |
| --- | --- | --- |
| État avant production | `ETAT_DOCUMENTAIRE_PRODUCTION.md` | `FREEBUFF_HANDOFF.md`, rapports consolidés |
| Règles UI/UX | `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md` | `CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.pdf` |
| Tarification | `STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.html` | `STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.pdf` |
| Migration historique | `documentation-saas-pos.html` | Aucun export conservé |

## Source de vérité pour l’état courant

Pour connaître l’avancement réel, consulter d’abord `ETAT_DOCUMENTAIRE_PRODUCTION.md`, puis la section « État production — 18 septembre 2026 » de `FREEBUFF_HANDOFF.md`, `RAPPORT_GLOBAL_SAAS.md`, `RAPPORT_ADMINISTRATION_SAAS.md` et `DEPLOIEMENT_O2SWITCH.md`. La section « Clôture locale — 15 septembre 2026 » reste la référence pour le développement et le staging. Les entrées datées et les fixtures locales du handoff sont historiques ou mutables, sauf indication contraire.
