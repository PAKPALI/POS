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
            <h1>Bonjour <?php echo e($name); ?></h1>
        </div>
        <div class="content text-center">
            <p><h3><?php echo e($text); ?></h3></p>
            <p style="color:red;"> NB: Le mot de passe reste confidentiel</p>
            <!-- <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p> -->
            <!-- <a href="#" class="btn">Contacter le support</a> -->
        </div>
        <?php echo $__env->make('emails.design.emailFooter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\POS\resources\views/emails/user/connectPass.blade.php ENDPATH**/ ?>