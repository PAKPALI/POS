<div id="stripedRows" class="mb-5">

    <div class="card">
        <div class="card-body text-center">

            <!-- ICON CAISSE -->
            <div class="mb-4">
                <i class="bi bi-cash-stack" style="font-size: 60px; color: #28a745;"></i>
            </div>

            <!-- TABLE INFOS -->
            <table class="table table-striped border mb-0 text-center">

                <tbody>

                    <tr>
                        <th scope="row">1</th>
                        <td>Code :</td>
                        <td><strong><?php echo e($cashAccount->code); ?></strong></td>
                    </tr>

                    <tr>
                        <th scope="row">2</th>
                        <td>Nom :</td>
                        <td><?php echo e($cashAccount->name); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">3</th>
                        <td>Solde :</td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo e(number_format($cashAccount->balance, 0, ',', ' ')); ?> <?php echo e($cashAccount->currency); ?>

                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Devise :</td>
                        <td><?php echo e($cashAccount->currency); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Caisse principale :</td>
                        <td>
                            <?php if($cashAccount->is_default): ?>
                                <span class="badge bg-success">Oui</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Non</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Caisse de taxe :</td>
                        <td>
                            <?php if($cashAccount->is_tax): ?>
                                <span class="badge bg-success text-dark">Oui</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Non</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Statut :</td>
                        <td>
                            <?php if($cashAccount->status): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">8</th>
                        <td>Description :</td>
                        <td><?php echo e($cashAccount->description ?? '---'); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">9</th>
                        <td>Créé le :</td>
                        <td><?php echo e($cashAccount->created_at->format('d-m-Y H:i:s')); ?></td>
                    </tr>

                </tbody>
            </table>

        </div>

        <!-- ARROWS (ton style POS conservé) -->
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>

    </div>
</div><?php /**PATH C:\POS\resources\views/ams/cash/show.blade.php ENDPATH**/ ?>