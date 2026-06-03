<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
        <?php echo $__env->make('emails.design.emailStyle', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo e($company->name ?? config('app.name')); ?></h1>
            <h4 style="color:red;">(Alerte de stock)</h4>
        </div>
        <div class="content text-center">
            <p>
                <h3>
                    <?php echo e($text); ?><br>
                    <?php echo e($text2); ?>

                </h3>
            </p>
            <p style="color:red;"><strong> NB: Veuillez faire une mise à jour du stock afin d'éviter une rupture totale ! </strong></p>
            <!-- <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p> -->
            <!-- <a href="#" class="btn">Contacter le support</a> -->
        </div>
    <?php echo $__env->make('emails.design.emailFooter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\POS\resources\views/emails/user/marginMail.blade.php ENDPATH**/ ?>