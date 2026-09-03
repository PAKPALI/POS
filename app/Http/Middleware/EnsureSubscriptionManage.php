<?php
namespace App\Http\Middleware;
use App\Services\CompanyContext; use Closure; use Illuminate\Http\Request;
class EnsureSubscriptionManage { public function __construct(private CompanyContext $context){} public function handle(Request $request,Closure $next){$m=$this->context->getMembership(); abort_unless(in_array($m->role?->key,['owner','admin'],true)&&$m->hasPermission('subscription.manage'),403); return $next($request);} }
