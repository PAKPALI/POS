<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px; /* Ajuster la taille de la police pour l'impression */
        }

        .receipt {
            width: 100%; /* Ajuster pour un format d'impression standard (80mm de large pour les imprimantes thermiques) */
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
        }

        .header p {
            font-size: 12px;
            margin: 3px 0;
        }

        .receipt-info {
            font-size: 12px;
            margin: 0px 0;
        }

        .items-table {
            width: 100%;
            font-size: 12px;
            margin-top: 5px;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 6px;
            text-align: left;
        }

        .items-table th {
            font-weight: bold;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Styles spécifiques pour l'impression */
        @media print {
            body {
                font-size: 12px;
            }
            .receipt {
                width: 80mm;
                margin: 0 auto;
            }
            .items-table th, .items-table td {
                font-size: 12px;
                padding: 6px;
            }
        }
    </style>
</head>
<body>

<div class="receipt">
    <div class="header">
        <h1><?php echo e(strtoupper($company->name ?? config('app.name'))); ?></h1>
        <p><strong>Email :</strong> <?php echo e($company->email); ?></p>
        <p><strong>Adresse :</strong> <?php echo e($company->adress); ?></p>
        <p><strong>Tél :</strong> <?php echo e($company->number1); ?><?php echo e($company->number2 ? ' / ' . $company->number2 : ''); ?></p>
    </div>

    <hr />

    <div class="receipt-info">
        <table class="items-table">
            <tbody>
                <tr>
                    <td class="item-details"> <strong>Date : <?php echo e($sale->created_at->format('d/m/Y')); ?></strong> </td>
                    <td class="item-details"> <strong>Réf : #<?php echo e($sale->code); ?></strong></td>
                    <td class="item-details"> <strong>Caissier : <?php echo e($sale->cashier ?? 'Nom du Caissier'); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <hr />

    <table class="items-table">
        <thead>
            <tr>
                <th class="item-details">Nom</th>
                <th class="item-details">Qté</th>
                <th class="item-details">P.U (FCFA)</th>
                <th class="item-details">P.T (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $saleDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="item-details"> 
                        <?php echo e($detail->product ? $detail->product->name : 'Produit non disponible'); ?>

                    </td>
                    <td class="item-details"><?php echo e($detail->quantity); ?></td>
                    <td class="item-details"><?php echo e(number_format($detail->unit_price, 2)); ?> </td>
                    <td class="item-details"><?php echo e(number_format($detail->total_price, 2)); ?> </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <hr />

    <div class="total">
        <?php if($sale->code_promo): ?>
            <p>Montant initial : <?php echo e(number_format($sale->amount_init)); ?> FCFA</p>
            <p>Réduction : <?php echo e(number_format($sale->discount)); ?> FCFA</p>
            <p>Montant payé : <?php echo e(number_format($sale->total_amount)); ?> FCFA</p>
            <p>Montant donné : <?php echo e(number_format($sale->received_amount)); ?> FCFA</p>
            <p>Monnaie rendue : <?php echo e(number_format($sale->remaining_amount)); ?> FCFA</p>
        <?php else: ?>
            <p>Montant payé : <?php echo e(number_format($sale->total_amount)); ?> FCFA</p>
            <p>Montant donné : <?php echo e(number_format($sale->received_amount)); ?> FCFA</p>
            <p>Monnaie rendue : <?php echo e(number_format($sale->remaining_amount)); ?> FCFA</p>
        <?php endif; ?>
    </div>

    <hr />

    <div class="footer">
        <!-- <h3>Les meilleurs wings de la capitale</h3> -->
        <h1><?php echo e(strtoupper($company->name ?? config('app.name'))); ?></h1>
        <p><?php echo e($company->message??''); ?></p>
    </div>
</div>

</body>
</html>
<?php /**PATH C:\POS\resources\views/pos/invoice.blade.php ENDPATH**/ ?>