<?php
namespace App\Http\Controllers;
use App\Models\{SubscriptionPayment,SubscriptionPlan}; use App\Services\{CompanyContext,EntitlementService,SubscriptionCheckoutService}; use Illuminate\Http\Request; use Throwable;
class SubscriptionController extends Controller
{
 public function index(CompanyContext $context,EntitlementService $entitlements){$company=$context->getCompany();$current=$entitlements->current($company);$plans=SubscriptionPlan::with('features')->where('is_active',true)->orderBy('rank')->get();$usage=$entitlements->usage($company);$payments=SubscriptionPayment::where('subscription_account_id',$company->subscription_account_id)->latest()->paginate(10);return view('subscription.index',compact('company','current','plans','usage','payments'));}
 public function checkout(Request $r,CompanyContext $context,SubscriptionCheckoutService $service){$r->validate(['plan'=>['required','string'],'months'=>['required','integer','min:1','max:12']]);try{$payment=$service->create($context->getCompanyId(),$r->user()->id,$r->string('plan')->toString(),(int)$r->input('months'));$data=$service->checkout($payment);return response()->json(['status'=>true,'checkout_url'=>$data['checkout_url'],'transaction_id'=>$payment->transaction_id,'amount'=>$payment->amount,'duration_months'=>$payment->duration_months,'expires_at'=>$payment->expires_at]);}catch(Throwable $e){report($e);return response()->json(['status'=>false,'title'=>'Paiement indisponible','msg'=>$e->getMessage()],422);}}
 public function status(CompanyContext $context,string $transactionId){$payment=SubscriptionPayment::where('subscription_account_id',$context->getCompany()->subscription_account_id)->where('transaction_id',$transactionId)->firstOrFail();return response()->json(['status'=>true,'payment_status'=>$payment->status]);}
 public function returned(){return redirect()->route('subscriptions.index')->with('info','Paiement reçu. Votre abonnement sera activé après confirmation sécurisée de KPrimePay.');}
}
