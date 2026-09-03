<?php
namespace App\Http\Middleware;
use App\Services\{CompanyContext,EntitlementService}; use Closure; use Illuminate\Http\Request;
class EnsurePlanFeature { public function __construct(private CompanyContext $context,private EntitlementService $entitlements){} public function handle(Request $request,Closure $next,string $feature){if(!$this->entitlements->feature($this->context->getCompany(),$feature))abort(403,'Cette fonctionnalité n’est pas incluse dans votre plan actif.');return $next($request);} }
