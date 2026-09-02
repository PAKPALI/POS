<?php

return [
    'pricing_note' => 'Offres prévisionnelles — bientôt disponibles. Les paiements d’abonnement ne sont pas encore activés.',
    'plans' => [
        [
            'key' => 'trial', 'name' => 'Essai', 'price' => 0, 'annual' => 0,
            'period' => '14 jours', 'description' => 'Pour découvrir tout le flux de vente.',
            'limits' => '1 entreprise · 1 utilisateur · 10 produits', 'quota' => '3 SMS · 3 WhatsApp',
            'featured' => false,
        ],
        [
            'key' => 'basic', 'name' => 'Basic', 'price' => 2500, 'annual' => 27500,
            'period' => 'mois', 'description' => 'Pour une activité qui démarre avec méthode.',
            'limits' => '1 entreprise · 2 utilisateurs · 50 produits', 'quota' => '10 SMS · 10 WhatsApp',
            'featured' => false,
        ],
        [
            'key' => 'bronze', 'name' => 'Bronze', 'price' => 5000, 'annual' => 55000,
            'period' => 'mois', 'description' => 'Le bon équilibre pour une boutique structurée.',
            'limits' => '1 entreprise · 3 utilisateurs · 150 produits', 'quota' => '20 SMS · 20 WhatsApp',
            'featured' => true,
        ],
        [
            'key' => 'silver', 'name' => 'Argent', 'price' => 10000, 'annual' => 110000,
            'period' => 'mois', 'description' => 'Pour plusieurs activités à piloter ensemble.',
            'limits' => '2 entreprises · 5 utilisateurs · 500 produits', 'quota' => '50 SMS · 50 WhatsApp',
            'featured' => false,
        ],
        [
            'key' => 'gold', 'name' => 'Gold', 'price' => 20000, 'annual' => 220000,
            'period' => 'mois', 'description' => 'Pour les équipes et réseaux en croissance.',
            'limits' => '5 entreprises · 15 utilisateurs · 1 000 produits', 'quota' => '100 SMS · 100 WhatsApp',
            'featured' => false,
        ],
    ],
    'pages' => [
        'fonctionnalites' => [
            'eyebrow' => 'Une seule vue pour avancer',
            'title' => 'Tout le commerce, du comptoir au suivi.',
            'intro' => 'POS, stock, caisse, clients et boutique en ligne restent reliés pour que chaque décision parte de données utiles.',
            'sections' => [
                ['icon' => 'cart', 'title' => 'Vendre sans ralentir', 'text' => 'Panier, remise, monnaie, client et encaissement dans un flux clair. Le total reste visible et le panier peut être retrouvé sur le même appareil.'],
                ['icon' => 'boxes', 'title' => 'Garder un stock fiable', 'text' => 'Catalogue, catégories, entrées, sorties, seuils, fournisseurs et inventaires suivent les mouvements de votre activité.'],
                ['icon' => 'wallet', 'title' => 'Piloter la caisse', 'text' => 'Caisse principale, caisse de taxe, transactions et historique donnent une lecture simple de ce qui est entré et sorti.'],
                ['icon' => 'people', 'title' => 'Connaître ses clients', 'text' => 'Retrouvez l’historique d’un client et envoyez un reçu lorsque les coordonnées et les canaux autorisés sont disponibles.'],
                ['icon' => 'team', 'title' => 'Faire travailler l’équipe', 'text' => 'Invitations, rôles et permissions s’appliquent par entreprise. Chacun voit uniquement les fonctions dont il a besoin.'],
                ['icon' => 'store', 'title' => 'Relier le web au comptoir', 'text' => 'Une boutique publique reçoit les commandes. Vous contrôlez ensuite leur passage en vente, sans prétendre réserver le stock au moment de la commande.'],
            ],
        ],
        'factures-sms-whatsapp' => [
            'eyebrow' => 'La vente continue après le paiement',
            'title' => 'La facture arrive sur le téléphone du client.',
            'intro' => 'Après une vente, le reçu peut être envoyé par SMS ou WhatsApp selon les coordonnées, les canaux configurés et les quotas disponibles.',
            'sections' => [
                ['icon' => 'cart', 'title' => '1. Encaissez au comptoir', 'text' => 'Ajoutez les produits, appliquez une remise si nécessaire, confirmez le montant reçu et la monnaie.'],
                ['icon' => 'file', 'title' => '2. Générez un reçu clair', 'text' => 'Le reçu reprend les informations de la vente et peut être consulté ou exporté selon vos droits.'],
                ['icon' => 'message', 'title' => '3. Choisissez le canal', 'text' => 'SMS et WhatsApp restent distincts. L’envoi dépend du réglage de l’entreprise et du quota disponible pour le canal choisi.'],
                ['icon' => 'shield', 'title' => 'Un cadre honnête', 'text' => 'La disponibilité de WhatsApp peut varier selon le pays et le fournisseur. Aucun numéro réel n’est utilisé dans cette démonstration.'],
            ],
        ],
        'secteurs' => [
            'eyebrow' => 'Un outil qui s’adapte au métier',
            'title' => 'Des gestes simples, pour des commerces différents.',
            'intro' => 'Commencez par le flux qui compte le plus aujourd’hui, puis ajoutez les outils qui vous font gagner du contrôle.',
            'sections' => [
                ['icon' => 'store', 'title' => 'Boutique', 'text' => 'Vendre vite, retrouver les produits et suivre les niveaux sans tenir plusieurs cahiers.'],
                ['icon' => 'basket', 'title' => 'Alimentation', 'text' => 'Classer un catalogue vivant, repérer les seuils et servir plusieurs clients à la suite.'],
                ['icon' => 'tag', 'title' => 'Mode et beauté', 'text' => 'Gérer les catégories, les clients et les reçus avec une présentation plus professionnelle.'],
                ['icon' => 'scissors', 'title' => 'Salon', 'text' => 'Conserver l’historique client et encaisser depuis un écran adapté au comptoir.'],
                ['icon' => 'truck', 'title' => 'Grossiste', 'text' => 'Suivre les entrées, fournisseurs, clients et exports quand les volumes grandissent.'],
                ['icon' => 'buildings', 'title' => 'Réseau de boutiques', 'text' => 'Piloter plusieurs entreprises dans un même compte avec des rôles séparés.'],
            ],
        ],
        'securite' => [
            'eyebrow' => 'La confiance se construit dans les détails',
            'title' => 'Vos données restent dans leur entreprise.',
            'intro' => 'Le produit est organisé pour limiter les accès, conserver l’historique utile et confirmer les opérations sensibles côté serveur.',
            'sections' => [
                ['icon' => 'layers', 'title' => 'Isolation par entreprise', 'text' => 'Les compagnies, utilisateurs et données métier sont séparés par le contexte actif. Un compte multi-entreprises ne mélange pas ses espaces.'],
                ['icon' => 'lock', 'title' => 'Permissions par rôle', 'text' => 'Les fonctions et les données financières sont protégées par les permissions du rôle dans l’entreprise concernée.'],
                ['icon' => 'check', 'title' => 'Transactions sûres', 'text' => 'Les ventes, stock et caisses sont traités dans le flux métier prévu. Les paiements externes sont vérifiés par le serveur.'],
                ['icon' => 'archive', 'title' => 'Historique et exports', 'text' => 'Les historiques et exports CSV, Excel ou PDF restent disponibles selon les droits, sans faire passer une limite de plan pour une suppression de données.'],
            ],
        ],
        'aide' => [
            'eyebrow' => 'Besoin d’un repère ?',
            'title' => 'Commencez par une question concrète.',
            'intro' => 'Les réponses ci-dessous couvrent les premiers usages. Pour une question liée à votre compte, utilisez le canal de support prévu par votre équipe.',
            'sections' => [
                ['icon' => 'book', 'title' => 'Première vente', 'text' => 'Ouvrez le point de vente, recherchez un produit, ajoutez-le au panier puis vérifiez le montant avant de confirmer.'],
                ['icon' => 'boxes', 'title' => 'Premier inventaire', 'text' => 'Commencez par le catalogue et les fournisseurs. Les mouvements de stock se font ensuite depuis l’espace Inventaire.'],
                ['icon' => 'message', 'title' => 'Premier reçu mobile', 'text' => 'Configurez le canal autorisé et vérifiez le quota disponible avant de proposer un envoi au client.'],
                ['icon' => 'mail', 'title' => 'Nous écrire', 'text' => 'Ajoutez ici l’adresse de support validée avant publication. En attendant, le parcours de connexion reste disponible pour les utilisateurs existants.'],
            ],
        ],
        'mentions-legales' => [
            'eyebrow' => 'Informations à compléter avant publication',
            'title' => 'Mentions, confidentialité et conditions.',
            'intro' => 'Cette structure est prête pour recevoir les textes validés par le propriétaire et le conseil juridique, pays par pays.',
            'sections' => [
                ['icon' => 'building', 'title' => 'Éditeur et pays d’exploitation', 'text' => 'À compléter : raison sociale, adresse, pays du lancement, responsable de publication et coordonnées officielles.'],
                ['icon' => 'lock', 'title' => 'Confidentialité', 'text' => 'À compléter : catégories de données, finalités, durées de conservation, droits des personnes et canal de demande.'],
                ['icon' => 'cookie', 'title' => 'Cookies et mesure', 'text' => 'À compléter après le choix de l’outil analytics. Aucun traceur marketing n’est activé par cette première version.'],
                ['icon' => 'file', 'title' => 'Conditions et tarifs', 'text' => 'Les plans affichés sont prévisionnels tant que le moteur d’abonnement et le paiement ne sont pas validés et activés.'],
            ],
        ],
    ],
];
