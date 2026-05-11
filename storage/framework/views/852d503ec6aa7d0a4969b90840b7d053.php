<div id="stripedRows" class="mb-5">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped border mb-0">
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
                        <td>Nom : </td>
                        <td><?php echo e($Inventory->product->name); ?></td>
                    </tr>
                    
                    <tr>
                        <th scope="row">2</th>
                        <td>Quantité avant :</td>
                        <td><?php echo e($Inventory->qte_before); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">3</th>
                        <td>Quantité ajoutée :</td>
                        <td><?php echo e($Inventory->qte_added); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">4</th>
                        <td>Quantité après :</td>
                        <td><?php echo e($Inventory->qte_after); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">5</th>
                        <td>Note :</td>
                        <td><?php echo e($Inventory->note); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">6</th>
                        <td>Créé par :</td>
                        <td><?php echo e($Inventory->user->name); ?></td>
                    </tr>

                    <tr>
                        <th scope="row">7</th>
                        <td>Créé le :</td>
                        <td><?php echo e($Inventory->created_at); ?></td>
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
</div><?php /**PATH C:\POS\resources\views/component/inventory/show.blade.php ENDPATH**/ ?>