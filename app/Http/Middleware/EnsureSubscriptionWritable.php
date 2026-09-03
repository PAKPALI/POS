<?php
namespace App\Http\Middleware;
use App\Services\{CompanyContext,EntitlementService}; use Closure; use Illuminate\Http\Request;
class EnsureSubscriptionWritable { public function __construct(private CompanyContext $context,private EntitlementService $entitlements){} public function handle(Request $request,Closure $next){if(in_array($request->method(),['GET','HEAD','OPTIONS'],true))return $next($request); if($this->entitlements->readOnly($this->context->getCompany())) abort(403,"Votre abonnement n'est plus actif. Vous pouvez consulter et exporter vos données, mais les nouvelles opérations sont suspendues."); return $next($request);} }
