# Rapport de charge des exports PDF

Date : 26 août 2026  
Moteur : DomPDF, PHP 8.2.24, limite mémoire 512 Mo.  
Base : `pos_testing` exclusivement.

## Risque confirmé

Un premier export non borné contenant plusieurs milliers de lignes a épuisé les **512 Mo** disponibles dans `Dompdf\Cellmap`. Un second essai avec des seuils intermédiaires a produit le même résultat. Les anciens contrôleurs chargeaient toutes les lignes puis demandaient à DomPDF de construire un tableau unique, ce qui pouvait faire tomber le processus PHP et affecter les autres utilisateurs.

## Correction

Les trois exports sont maintenant protégés avant le chargement complet des modèles et avant le démarrage de DomPDF :

- Produits : maximum **300 lignes** par PDF.
- Inventaire : maximum **500 mouvements** par PDF.
- Historique : maximum **100 ventes** par PDF.

Si la sélection dépasse le plafond, l’utilisateur revient à l’écran précédent avec un message lui demandant d’affiner la catégorie, la période, le statut ou la recherche. PHP ne tente plus de générer le document dangereux.

Les seuils sont configurables sans modifier le code :

```env
PDF_PRODUCTS_MAX_ROWS=300
PDF_INVENTORIES_MAX_ROWS=500
PDF_SALES_MAX_ROWS=100
```

## Benchmark validé

La base de référence contenait 3 000 produits, 5 000 mouvements, 1 000 ventes et 2 000 lignes de vente. Les exports globaux ont été refusés proprement, puis les sélections filtrées suivantes ont été générées :

| Export | Durée | Requêtes | Temps SQL | Mémoire supplémentaire | Taille PDF |
|---|---:|---:|---:|---:|---:|
| 300 produits | 1,34 s | 7 | 13 ms | 56 Mo | 44 Ko |
| 500 mouvements | 2,54 s | 8 | 37 ms | 46 Mo | 73 Ko |
| 100 ventes / 200 détails | 3,16 s | 8 | 17 ms | 12 Mo | 975 Ko |

Pic mémoire total observé : **162 Mo**, largement sous la limite de 512 Mo.

Le benchmark passe **23 assertions**. Un test quotidien supplémentaire vérifie avec **9 assertions** que les trois contrôleurs refusent les volumes dangereux avant DomPDF.

## Recommandation

Le PDF reste adapté aux documents lisibles et bornés. Pour exporter plusieurs milliers de lignes, la prochaine évolution fonctionnelle devra proposer CSV/Excel, qui peut être généré en flux avec une consommation mémoire faible. Il ne faut pas relever arbitrairement les plafonds PDF sur l’hébergement mutualisé.

## Validation globale

- Suite quotidienne : **128 tests, 703 assertions, 0 échec**.
- Benchmarks lourds : 5 scénarios disponibles uniquement sur demande.
- Performance et qualité locale : **95 %**.
- Préparation production : **70 %**, inchangée sans préproduction O2switch.
