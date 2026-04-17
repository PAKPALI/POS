<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            <?php if($MenuProduct->image): ?>
                <img class="mb-5" src="<?php echo e(asset('images/' . $MenuProduct->image)); ?>" alt="Image du produit" style="width: 300px; height: auto;">
            <?php else: ?>
                Pas d'image
            <?php endif; ?>

            <div class="card">
                <div class="card-header text-center">
                    <h6>INFORMATIONS SUR LE MENU</h6>
                </div>
            </div>
            <table class="table table-striped border mb-0 text-center">
                
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Catégorie : </td>
                        <td><?php echo e($MenuProduct->category->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Nom : </td>
                        <td><?php echo e($MenuProduct->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité : </td>
                        <td><?php echo e($MenuProduct->qte); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>Marge : </td>
                        <td><?php echo e($MenuProduct->margin); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Prix unitaire : </td>
                        <td><?php echo e($MenuProduct->price); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Prix d'achat : </td>
                        <td><?php echo e($MenuProduct->purchase_price); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Bénefice: </td>
                        <td><?php echo e($MenuProduct->profit); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">8</th>
                        <td>Créer par :</td>
                        <td><?php echo e($MenuProduct->user->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">9</th>
                        <td>Créer le :</td>
                        <td><?php echo e($MenuProduct->created_at->format('d-m-Y H:i:s')); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="card">
                <div class="card-header text-center mt-4">
                    <h6>INFORMATIONS SUR LES PRODUITS DU MENU</h6>
                </div>
            </div>
            <table class="table table-striped border text-center">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Quantité</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $MenuProduct->MenuProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($item->product->image): ?>
                                    <img class="mb-0" src="<?php echo e(asset('images/' . $item->product->image)); ?>" alt="Image du produit" style="width: 40px; height: auto;">
                                <?php else: ?>
                                    Pas d'image
                                <?php endif; ?>
                            </td>
                            <td class="<?php if($item->product->qte<=$item->product->margin): ?> bg-danger <?php endif; ?>"><?php echo e($item->product->name); ?> (<?php echo e($item->product->qte); ?>)</td>
                            <td><?php echo e($item->quantity); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
        <div class="hljs-container">
            <pre><code class="xml" data-url="assets/data/table-elements/code-3.json"></code></pre>
        </div>
    </div>
</div><?php /**PATH C:\POS\resources\views/component/menu/show.blade.php ENDPATH**/ ?>