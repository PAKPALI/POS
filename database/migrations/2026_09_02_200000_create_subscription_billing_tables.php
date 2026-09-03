<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('billing_company_id')->nullable()->constrained('company_settings')->nullOnDelete();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('trial_credited_at')->nullable();
            $table->timestamps();
            $table->unique('owner_id');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->foreignId('subscription_account_id')->nullable()->after('created_by')->constrained('subscription_accounts')->nullOnDelete();
            $table->index('subscription_account_id');
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('rank');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('monthly_price')->default(0);
            $table->unsignedBigInteger('annual_price')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->unsignedSmallInteger('company_limit')->default(1);
            $table->unsignedSmallInteger('user_limit')->default(1);
            $table->unsignedInteger('product_limit')->default(10);
            $table->unsignedInteger('sms_quota')->default(0);
            $table->unsignedInteger('whatsapp_quota')->default(0);
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['rank', 'version']);
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('feature_key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['subscription_plan_id', 'feature_key']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status')->index();
            $table->string('billing_period', 10);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->json('snapshot');
            $table->timestamps();
            $table->index(['subscription_account_id', 'status', 'ends_at']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('kpp_reference')->nullable()->index();
            $table->string('event_id')->nullable()->unique();
            $table->string('operation', 20);
            $table->string('billing_period', 10);
            $table->unsignedBigInteger('amount_ht');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF');
            $table->json('snapshot');
            $table->string('status')->default('created')->index();
            $table->text('checkout_url')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index(['subscription_account_id', 'created_at']);
        });

        Schema::create('subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('event_key');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['subscription_account_id', 'event_key']);
        });

        $plans = [
            ['trial','Essai',0,0,0,1,1,10,3,3,14], ['basic','Basic',1,2500,27500,1,2,50,10,10,0],
            ['bronze','Bronze',2,5000,55000,1,3,150,20,20,0], ['silver','Argent',3,10000,110000,2,5,500,50,50,0],
            ['gold','Gold',4,20000,220000,5,15,1000,100,100,0],
        ];
        foreach ($plans as [$key,$name,$rank,$monthly,$annual,$companies,$users,$products,$sms,$whatsapp,$trial]) {
            $id = DB::table('subscription_plans')->insertGetId(['key'=>$key,'name'=>$name,'rank'=>$rank,'monthly_price'=>$monthly,'annual_price'=>$annual,'company_limit'=>$companies,'user_limit'=>$users,'product_limit'=>$products,'sms_quota'=>$sms,'whatsapp_quota'=>$whatsapp,'trial_days'=>$trial,'created_at'=>now(),'updated_at'=>now()]);
            foreach (['suppliers'=> $key !== 'basic', 'ecommerce'=> $key !== 'basic'] as $feature=>$enabled) DB::table('plan_features')->insert(['subscription_plan_id'=>$id,'feature_key'=>$feature,'enabled'=>$enabled,'created_at'=>now(),'updated_at'=>now()]);
        }
        $permissionId = DB::table('permissions')->where('key', 'subscription.manage')->value('id') ?: DB::table('permissions')->insertGetId(['key'=>'subscription.manage','module'=>'subscription','description'=>'Gérer l’abonnement de la compagnie','created_at'=>now(),'updated_at'=>now()]);
        foreach (DB::table('roles')->whereIn('key', ['owner','admin'])->pluck('id') as $roleId) DB::table('permission_role')->insertOrIgnore(['permission_id'=>$permissionId,'role_id'=>$roleId]);
        foreach (DB::table('company_settings')->whereNotNull('created_by')->whereNull('subscription_account_id')->orderBy('id')->get() as $company) {
            $accountId = DB::table('subscription_accounts')->where('owner_id',$company->created_by)->value('id');
            if (!$accountId) $accountId=DB::table('subscription_accounts')->insertGetId(['owner_id'=>$company->created_by,'billing_company_id'=>$company->id,'created_at'=>now(),'updated_at'=>now()]);
            DB::table('company_settings')->where('id',$company->id)->update(['subscription_account_id'=>$accountId]);
        }
    }
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) { $table->dropConstrainedForeignId('subscription_account_id'); });
        Schema::dropIfExists('subscription_events'); Schema::dropIfExists('subscription_payments'); Schema::dropIfExists('subscriptions'); Schema::dropIfExists('plan_features'); Schema::dropIfExists('subscription_plans'); Schema::dropIfExists('subscription_accounts');
    }
};
