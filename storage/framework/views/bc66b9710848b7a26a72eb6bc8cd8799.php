<div id="stripedRows" class="mb-5">

    <div class="card">
        <div class="card-body text-center">

            <!-- ICON TRANSACTION -->
            <div class="mb-4">
                <?php if($transaction->type == 'IN'): ?>
                    <i class="bi bi-arrow-left-right text-success" style="font-size: 60px;"></i>
                <?php elseif($transaction->type == 'OUT'): ?>
                    <i class="bi bi-arrow-left-right text-danger" style="font-size: 60px;"></i>
                <?php else: ?>
                    <i class="bi bi-arrow-left-right text-info" style="font-size: 60px;"></i>
                <?php endif; ?>
            </div>

            <!-- TABLE INFOS -->
            <table class="table table-striped border mb-0 text-center">
                <tbody>

                    <tr>
                        <th scope="row">1</th>
                        <td>Type :</td>
                        <td>
                            <?php if($transaction->type == 'IN'): ?>
                                <span class="badge bg-success">Entrée</span>
                            <?php elseif($transaction->type == 'OUT'): ?>
                                <span class="badge bg-danger">Sortie</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Transfert</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">2</th>
                        <td>Montant :</td>
                        <td>
                            <span class="badge bg-warning">
                                <?php echo e(number_format($transaction->amount, 0, ',', ' ')); ?> FCFA
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">3</th>
                        <td>Caisse source :</td>
                        <td>
                            <?php echo e($transaction->fromCash->name ?? '---'); ?>

                        </td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Caisse destination :</td>
                        <td>
                            <?php echo e($transaction->toCash->name ?? '---'); ?>

                        </td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Utilisateur :</td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo e($transaction->user->name); ?>

                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Date :</td>
                        <td>
                            <?php echo e($transaction->created_at->format('d-m-Y H:i:s')); ?>

                        </td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Description :</td>
                        <td>
                            <div style=" padding:15px; border-radius:8px; max-height:150px; overflow-y:auto;">
                                <strong><?php echo e($transaction->description ?? '---'); ?></strong> 
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>

        <!-- ARROWS STYLE -->
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>

    </div>
</div><?php /**PATH C:\POS\resources\views/ams/transaction/show.blade.php ENDPATH**/ ?>