<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            <?php if($Product->image): ?>
                <img class="mb-5" src="<?php echo e(asset('images/' . $Product->image)); ?>" alt="Image du produit" style="width: 300px; height: auto;">
            <?php else: ?>
                Pas d'image
            <?php endif; ?>
        
            <table class="table table-striped border mb-0 text-center">
                <!-- <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">First</th>
                        <th scope="col">Last</th>
                        <th scope="col">Handle</th>
                    </tr>
                </thead> -->
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Catégorie : </td>
                        <td><?php echo e($Product->category->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Nom : </td>
                        <td><?php echo e($Product->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité : </td>
                        <td><?php echo e($Product->qte); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>Marge : </td>
                        <td><?php echo e($Product->margin); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Prix de vente : </td>
                        <td><?php echo e($Product->price); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Prix d'achat : </td>
                        <td><?php echo e($Product->purchase_price); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Prix TTC : </td>
                        <td><?php echo e($Product->price_ttc??'-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Bénefice: </td>
                        <td><?php echo e($Product->profit); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">8</th>
                        <td>Créer par :</td>
                        <td><?php echo e($Product->user->name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">9</th>
                        <td>Créer le :</td>
                        <td><?php echo e($Product->created_at->format('d-m-Y H:i:s')); ?></td>
                    </tr>
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
</div><?php /**PATH C:\POS\resources\views/component/product/show.blade.php ENDPATH**/ ?>