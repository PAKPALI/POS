<?php

return [
    'roles' => [
        'super_admin' => [
            'label' => 'Super-administrateur',
            'description' => 'Accès complet à la plateforme et aux opérations sensibles.',
            'permissions' => ['*'],
        ],
        'support' => [
            'label' => 'Support',
            'description' => 'Consultation des entreprises et utilisateurs pour l’assistance.',
            'permissions' => ['platform.dashboard.view', 'platform.companies.view', 'platform.users.view'],
        ],
        'finance' => [
            'label' => 'Finance',
            'description' => 'Consultation des paiements, quotas et rentabilité, avec réconciliation.',
            'permissions' => ['platform.dashboard.view', 'platform.payments.view', 'platform.payments.reconcile'],
        ],
        'technical' => [
            'label' => 'Technique',
            'description' => 'Supervision du système, audit et relance des jobs échoués.',
            'permissions' => ['platform.dashboard.view', 'platform.health.view', 'platform.health.jobs.retry', 'platform.audit.view', 'platform.communications.view', 'platform.communications.retry'],
        ],
    ],
];
