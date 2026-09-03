<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\PlatformSetting;
use App\Models\PlatformSettingHistory;
use App\Services\PlatformConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GeneralSettingController extends Controller
{
    public const DEFAULTS=['identity.app_name'=>'PRO-SELLER','identity.logo_path'=>'','support.email'=>'','support.phone'=>'','support.hours'=>'','defaults.currency'=>'XOF','defaults.country'=>'TG','services.email.enabled'=>'1','services.sms.enabled'=>'1','services.whatsapp.enabled'=>'1','services.kprimepay.enabled'=>'1','subscriptions.enforcement_enabled'=>'0','security.invitation_expiry_hours'=>'48','security.two_factor_expiry_minutes'=>'10','security.payment_expiry_hours'=>'24','maintenance.enabled'=>'0','maintenance.message'=>'Une maintenance est en cours. Veuillez réessayer dans quelques instants.'];

    public function edit(Request $request, PlatformConfigurationService $configuration)
    {
        $filters = $request->validate(['history_search' => ['nullable', 'string', 'max:100'], 'history_per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])]]);
        $values=[]; foreach(self::DEFAULTS as $key=>$default)$values[$key]=$configuration->get($key,$default);
        $serviceStatus=['email'=>filled(config('mail.mailers.smtp.host')),'sms'=>filled(config('services.kprimesms.token'))&&filled(config('services.kprimesms.key')),'whatsapp'=>filled(config('services.kprimesms.token'))&&filled(config('services.kprimesms.key')),'kprimepay'=>filled(config('services.kprimepay.token'))];
        $history=PlatformSettingHistory::with('admin:id,name')
            ->when($filters['history_search'] ?? null, function ($query, $value) {
                $term = '%'.$value.'%';
                $query->where(function ($search) use ($term) {
                    $search->where('key', 'like', $term)->orWhere('old_value', 'like', $term)
                        ->orWhere('new_value', 'like', $term)->orWhere('reason', 'like', $term)
                        ->orWhereHas('admin', fn ($admin) => $admin->where('name', 'like', $term));
                });
            })
            ->latest()->paginate((int) ($filters['history_per_page'] ?? 20), ['*'], 'history_page')->withQueryString();
        return view('platform.settings.general',compact('values','serviceStatus','history','filters'));
    }

    public function update(Request $request,PlatformConfigurationService $configuration)
    {
        $admin=Auth::guard('platform')->user();
        $data=$request->validate(['app_name'=>['required','string','max:80'],'logo'=>['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],'support_email'=>['nullable','email','max:255'],'support_phone'=>['nullable','string','max:30'],'support_hours'=>['nullable','string','max:150'],'currency'=>['required','string','size:3'],'country'=>['required','string','size:2'],'invitation_expiry_hours'=>['required','integer','min:1','max:720'],'two_factor_expiry_minutes'=>['required','integer','min:2','max:60'],'payment_expiry_hours'=>['required','integer','min:1','max:720'],'maintenance_message'=>['required','string','min:10','max:500'],'reason'=>['required','string','min:5','max:500'],'current_password'=>['required','current_password:platform']]);
        $changes=['identity.app_name'=>$data['app_name'],'support.email'=>$data['support_email']??'','support.phone'=>$data['support_phone']??'','support.hours'=>$data['support_hours']??'','defaults.currency'=>strtoupper($data['currency']),'defaults.country'=>strtoupper($data['country']),'services.email.enabled'=>$request->boolean('email_enabled')?'1':'0','services.sms.enabled'=>$request->boolean('sms_enabled')?'1':'0','services.whatsapp.enabled'=>$request->boolean('whatsapp_enabled')?'1':'0','services.kprimepay.enabled'=>$request->boolean('kprimepay_enabled')?'1':'0','subscriptions.enforcement_enabled'=>$request->boolean('subscriptions_enforcement_enabled')?'1':'0','security.invitation_expiry_hours'=>(string)$data['invitation_expiry_hours'],'security.two_factor_expiry_minutes'=>(string)$data['two_factor_expiry_minutes'],'security.payment_expiry_hours'=>(string)$data['payment_expiry_hours'],'maintenance.enabled'=>$request->boolean('maintenance_enabled')?'1':'0','maintenance.message'=>$data['maintenance_message']];
        if($request->hasFile('logo'))$changes['identity.logo_path']=$request->file('logo')->store('platform','public');
        DB::transaction(function()use($request,$admin,$data,$changes){foreach($changes as $key=>$value){$old=PlatformSetting::where('key',$key)->value('value')??(self::DEFAULTS[$key]??'');if((string)$old===(string)$value)continue;PlatformSetting::updateOrCreate(['key'=>$key],['value'=>(string)$value,'type'=>'string','updated_by'=>$admin->id]);PlatformSettingHistory::create(['key'=>$key,'old_value'=>(string)$old,'new_value'=>(string)$value,'reason'=>$data['reason'],'platform_admin_id'=>$admin->id]);PlatformAuditLog::create(['platform_admin_id'=>$admin->id,'action'=>'platform.general_setting.updated','target_type'=>PlatformSetting::class,'target_id'=>$key,'old_values'=>['value'=>$key==='identity.logo_path'?'[fichier]':$old],'new_values'=>['value'=>$key==='identity.logo_path'?'[fichier]':$value],'reason'=>$data['reason'],'ip_address'=>$request->ip(),'user_agent'=>Str::limit((string)$request->userAgent(),1000,'')]);}});
        $configuration->forget(array_keys($changes));
        return back()->with('success','Les paramètres généraux ont été mis à jour.');
    }
}
