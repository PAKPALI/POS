<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body text-center">
            <table class="table table-striped border mb-0 text-center">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Image</th>
                        <th scope="col">Produit</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">prix unitaire</th>
                        <th scope="col">prix total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $n = 1;
                    ?>
                    <?php $__currentLoopData = $Sale->saleDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr style="text-align: center;">
                            <th scope="row"><?php echo e($n++); ?></th>
                            <th scope="row">
                                <?php if($detail->product->image): ?>
                                    <img class="mb-0" src="<?php echo e(asset('images/' . $detail->product->image)); ?>" alt="Image du produit" style="width: 75px; height: auto;">
                                <?php else: ?>
                                    Pas d'image
                                <?php endif; ?>
                            </th>
                            <td><?php echo e($detail->product->name); ?></td>
                            <td><?php echo e($detail->quantity); ?></td>
                            <td><?php echo e($detail->unit_price); ?></td>
                            <td><?php echo e($detail->total_price); ?></td>
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
</div><?php /**PATH C:\POS\resources\views/pos/sale/show_detail.blade.php ENDPATH**/ ?>