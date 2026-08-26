<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Category;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Models\CompanyUser;
use App\Models\CompanyInvitation;
use App\Models\Company;
use App\Models\Role;
use App\Services\CompanyContext;
use App\Services\CompanyOnboardingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function dashboard()
    {
        $canViewFinancials = app(CompanyContext::class)->hasPermission('reports.view_margin');
        $Action = Action::whereDate('created_at', today())->latest()->paginate(10);
        $categoryCount = Category::count();
        $productCount = Product::count();
        $salesSummary = Sale::query()
            ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as total_revenue, COALESCE(SUM(discount), 0) as total_discount')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as total_profit')
            ->first();
        $clientCount = Client::count();
        $supplierCount = Supplier::count();
        $company = CompanySetting::first();

        $saleCount = (int) $salesSummary->sale_count;
        $sale_total_revenue = (float) $salesSummary->total_revenue;
        $sale_total_discount = (float) $salesSummary->total_discount;
        $sale_total_profit = $canViewFinancials ? (float) $salesSummary->total_profit : 0;

        $mostSoldProducts = SaleDetail::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->with('product:id,name,image,price')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->paginate(4);

        return view('dashboard', compact(
            'Action',
            'categoryCount',
            'productCount',
            'saleCount',
            'clientCount',
            'supplierCount',
            'sale_total_profit',
            'sale_total_revenue',
            'sale_total_discount',
            'mostSoldProducts',
            'company',
            'canViewFinancials'
        ));
    }

    public function index()
    {
        $companyId = app(CompanyContext::class)->getCompanyId();
        if(request()->ajax()){
            $users = User::query()
                ->join('company_user as active_membership', function ($join) use ($companyId) {
                    $join->on('active_membership.user_id', '=', 'users.id')
                        ->where('active_membership.company_id', $companyId)
                        ->where('active_membership.status', 'active');
                })
                ->leftJoin('roles as active_role', 'active_role.id', '=', 'active_membership.role_id')
                ->select('users.*', 'active_role.name as active_role_name')
                ->latest('users.created_at');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $clone = '<button type="button" data-id="'.$row->id.'" data-name="'.e($row->name).'" title="Intégrer dans une autre compagnie" class="btn btn-info btn-sm cloneUser"><i class="fas fa-lg fa-fw me-0 fa-clone"></i></button> ';
                    if($row->status==1){
                        $btn = $clone.'<a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = $clone.'<a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->addColumn('role_name', fn ($user) => $user->active_role_name ?? 'Non attribué')
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a  class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a  class="btn btn-danger btn-sm">Inactif</a>';
                    }
                    return $btn;
                })
                ->editColumn('created_at', function ($Category) {
                    return $Category->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        $roles = Role::where('company_id', $companyId)->where('key', '!=', 'owner')->orderBy('name')->get();
        $invitations = CompanyInvitation::where('company_id', $companyId)
            ->with('role', 'inviter')->latest()->get();
        return view('user.index', compact('roles', 'invitations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
    */

    public function code(){
        $code = Str::random(4);
        return $code;
    }
    
    public function store(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ nom!",
            "name.max" => "Le nombre de caractere du nom depasse les 255!",
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "email.unique" => "Ce mail existe deja",
            "phone.required" => "Remplir le champ Numéro de téléphone!",
            "phone.numeric" => "Le champ Numéro de téléphone doit être numérique!",
            "phone.digits" => "Le champ Numéro de téléphone doit comporter exactement 8 chiffres!",

            // "password.required" => "Remplir le champ mot de passe!",
            // "password.min" => "Le mot de passe doit comporter au moins 8 caracteres!",
            // "password.confirmed" => "Les mots de passe ne correspondent pas",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId()))],
            'phone' => ['required', 'numeric', 'digits:8'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $error_messages);

        if($validator->fails())
        {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "INSCRIPTION ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }else{
            $email = $request['email'];
            $name = $request['name'];
            $role = Role::where('company_id', app(CompanyContext::class)->getCompanyId())->findOrFail($request->role_id);
            abort_if($role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être attribué.');
            $phone = $request['phone'];
            $code = $this->code();
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'password' => Hash::make($code), 'status' => 1]
            );
            $companyId = app(CompanyContext::class)->getCompanyId();
            CompanyUser::updateOrCreate(
                ['company_id' => $companyId, 'user_id' => $user->id],
                ['role_id' => $role->id, 'status' => 'active', 'invited_by' => Auth::id(), 'joined_at' => now()]
            );
            if ($user->wasRecentlyCreated) {
                $this->sendEmail($email,$name,$code);
            }
            return response()->json([
                "status" => true,
                "reload" => true,
                "redirect_to" => '',
                "title" => "INSCRIPTION DE L'UTILISATEUR REUSSIE!",
                "msg" => "L'utilisateur '".$request-> name."' , est validé"
            ]);
        }
    }

    public function attachExisting(Request $request)
    {
        $companyId = app(CompanyContext::class)->getCompanyId();
        $validated = $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ], [
            'email.exists' => 'Aucun compte utilisateur ne correspond à cet e-mail.',
            'role_id.required' => 'Sélectionnez le rôle de l’utilisateur dans cette compagnie.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $role = Role::where('company_id', $companyId)->findOrFail($validated['role_id']);
        abort_if($role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être attribué.');

        $membership = CompanyUser::where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->first();

        if ($membership?->status === 'active') {
            return response()->json([
                'status' => false,
                'title' => 'UTILISATEUR DÉJÀ PRÉSENT',
                'msg' => $user->name.' appartient déjà à cette compagnie.',
            ]);
        }

        CompanyUser::updateOrCreate(
            ['company_id' => $companyId, 'user_id' => $user->id],
            [
                'role_id' => $role->id,
                'status' => 'active',
                'invited_by' => Auth::id(),
                'joined_at' => $membership?->joined_at ?? now(),
            ]
        );

        return response()->json([
            'status' => true,
            'title' => 'UTILISATEUR RATTACHÉ',
            'msg' => $user->name.' peut maintenant accéder à cette compagnie avec le rôle '.$role->name.'.',
        ]);
    }

    public function transferOptions(User $user)
    {
        $activeCompanyId = app(CompanyContext::class)->getCompanyId();
        abort_unless($user->memberships()->where('company_id', $activeCompanyId)->where('status', 'active')->exists(), 404);

        $memberships = CompanyUser::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('company_id', '!=', $activeCompanyId)
            ->with(['company', 'role.permissions'])
            ->get()
            ->filter(fn ($membership) => $membership->company?->isActive() && $membership->hasPermission('members.manage'));

        $companies = $memberships->map(function ($membership) use ($user) {
            $alreadyMember = CompanyUser::where('company_id', $membership->company_id)
                ->where('user_id', $user->id)->where('status', 'active')->exists();

            return [
                'id' => $membership->company_id,
                'name' => $membership->company->name,
                'already_member' => $alreadyMember,
                'roles' => Role::where('company_id', $membership->company_id)
                    ->where('key', '!=', 'owner')->orderBy('name')->get(['id', 'name']),
            ];
        })->values();

        return response()->json(['user' => ['id' => $user->id, 'name' => $user->name], 'companies' => $companies]);
    }

    public function transferToCompany(Request $request, User $user)
    {
        $activeCompanyId = app(CompanyContext::class)->getCompanyId();
        abort_unless($user->memberships()->where('company_id', $activeCompanyId)->where('status', 'active')->exists(), 404);

        $validated = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('company_settings', 'id')],
            'role_id' => ['required', 'integer'],
        ]);
        abort_if((int) $validated['company_id'] === $activeCompanyId, 422, 'Sélectionnez une autre compagnie.');

        $operatorMembership = CompanyUser::where('user_id', Auth::id())
            ->where('company_id', $validated['company_id'])->where('status', 'active')
            ->with('role.permissions')->firstOrFail();
        abort_unless($operatorMembership->hasPermission('members.manage'), 403, 'Vous ne pouvez pas gérer les utilisateurs de cette compagnie.');

        $company = Company::active()->findOrFail($validated['company_id']);
        $role = Role::where('company_id', $company->id)->findOrFail($validated['role_id']);
        abort_if($role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être attribué.');

        $membership = CompanyUser::where('company_id', $company->id)->where('user_id', $user->id)->first();
        if ($membership?->status === 'active') {
            return response()->json(['status' => false, 'title' => 'DÉJÀ INTÉGRÉ', 'msg' => $user->name.' appartient déjà à '.$company->name.'.']);
        }

        CompanyUser::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['role_id' => $role->id, 'status' => 'active', 'invited_by' => Auth::id(), 'joined_at' => $membership?->joined_at ?? now()]
        );

        return response()->json([
            'status' => true,
            'title' => 'INTÉGRATION APPROUVÉE',
            'msg' => $user->name.' a été intégré à '.$company->name.' avec le rôle '.$role->name.'.',
        ]);
    }

    public function sendEmail($email, $name, $code)
    {
        $company = app(CompanyContext::class)->getCompanyOrNull();
        $text = "Voici votre mot de passe ".$code."";
        // Envoyez l'e-mail avec le code généré
        Mail::send('emails.user.connectPass', ['text' => $text,'name' => $name, 'company' => $company], function($message) use ($email, $company){
            $message->to($email);
            $message->subject(($company?->name ?? config('app.name')).' — Accès utilisateur');
        });
    }

    public function register(Request $request, CompanyOnboardingService $onboarding)
    {
        $error_messages = [
            "name.required" => "Remplir le champ nom!",
            "name.max" => "Le nombre de caractere du nom depasse les 255!",
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "email.unique" => "Ce mail existe deja",
            "company_name.required" => "Renseignez le nom de votre entreprise",
            "password.required" => "Remplir le champ mot de passe!",
            "password.min" => "Le mot de passe doit comporter au moins 8 caracteres!",
            "password.confirmed" => "Les mots de passe ne correspondent pas",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'company_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'default_tax' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], $error_messages);

        if($validator->fails())
        {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "INSCRIPTION ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }else{
            $result = $onboarding->registerOwner($request->only([
                'name', 'email', 'password', 'company_name', 'company_email', 'company_phone', 'phone', 'default_tax',
            ]));
            Auth::login($result['user']);
            $request->session()->regenerate();
            $request->session()->put('active_company_id', $result['company']->id);
            $request->session()->put('active_company_name', $result['company']->name);

            return response()->json([
                "status" => true,
                "reload" => true,
                "redirect_to" => route('dashboard'),
                "title" => "INSCRIPTION DE L'ADMINISTRATION REUSSIE!",
                "msg" => "L'administrateur '".$request->name."' est validé"
            ]);
            
        }
    }

    public function updatePassword(Request $request)
    {
        $error_messages = [
            'AM.required' => 'Saisissez votre mot de passe actuel.',
            'NM.required' => 'Saisissez votre nouveau mot de passe.',
            'NM.min' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'NM.regex' => 'Le nouveau mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
            'NM.same' => 'Le nouveau mot de passe et sa confirmation sont différents.',
            'CM.required' => 'Confirmez votre nouveau mot de passe.',
        ];

        $validator = Validator::make($request->all(), [
            'AM' => ['required', 'string'],
            'NM' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', 'same:CM'],
            'CM' => ['required', 'string'],
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'reload' => false,
                'title' => 'MODIFICATION IMPOSSIBLE',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();
        if (! Hash::check($request->string('AM')->toString(), $user->password)) {
            return response()->json([
                'status' => false,
                'reload' => false,
                'title' => 'MODIFICATION IMPOSSIBLE',
                'msg' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->string('NM')->toString())]);

        return response()->json([
            'status' => true,
            'reload' => true,
            'redirect_to' => '0',
            'title' => 'MOT DE PASSE MODIFIÉ',
            'msg' => 'Votre mot de passe a été mis à jour.',
        ]);
    }

    public function updateEmail(Request $request)
    {
        $error_messages = [
            'NE.required' => 'Saisissez votre nouvelle adresse e-mail.',
            'NE.email' => 'Saisissez une adresse e-mail valide.',
            'NE.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'NE.same' => 'La nouvelle adresse e-mail et sa confirmation sont différentes.',
            'CE.required' => 'Confirmez votre nouvelle adresse e-mail.',
            'current_password.required' => 'Saisissez votre mot de passe actuel.',
        ];

        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'NE' => ['required', 'string', 'email', 'max:255', 'same:CE', Rule::unique('users', 'email')->ignore($user->id)],
            'CE' => ['required', 'string', 'email'],
            'current_password' => ['required', 'string'],
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'reload' => false,
                'title' => 'MODIFICATION IMPOSSIBLE',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return response()->json([
                'status' => false,
                'reload' => false,
                'title' => 'MODIFICATION IMPOSSIBLE',
                'msg' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->update(['email' => mb_strtolower($request->string('NE')->toString())]);

        return response()->json([
            'status' => true,
            'reload' => true,
            'redirect_to' => '0',
            'title' => 'ADRESSE E-MAIL MODIFIÉE',
            'msg' => 'Votre adresse e-mail a été mise à jour.',
        ]);
    }

    public function topSellingProducts(Request $request)
    {
        try {
            $daterange = $request->daterange; // Exemple : "01/10/2024 - 31/10/2024"
        
            if ($daterange) {
                [$startDate, $endDate] = explode(' - ', $daterange);
                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay()->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d 23:59:59');
            }
            // Log::error('Error in topProducts: ' . $endDate);

            $topProducts = DB::table('sale_details')
                ->join('products', 'sale_details.product_id', '=', 'products.id')
                ->select('products.name', DB::raw('SUM(sale_details.quantity) as total_quantity'))
                ->where('sale_details.company_id', app(CompanyContext::class)->getCompanyId())
                ->where('products.company_id', app(CompanyContext::class)->getCompanyId())
                ->groupBy('products.name')
                ->orderBy('total_quantity', 'desc')
                ->whereBetween('sale_details.created_at', [$startDate, $endDate])
                ->take(10)
                ->get();
            return response()->json($topProducts, 200);
    
        } catch (\Exception $e) {
            Log::error('Error in topProducts: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $User = $this->findCompanyUser($id);
        $companyId = app(CompanyContext::class)->getCompanyId();
        $membership = $User->getMembershipFor($companyId);
        $roles = Role::where('company_id', $companyId)
            ->when($membership->role?->key !== 'owner', fn ($query) => $query->where('key', '!=', 'owner'))
            ->orderBy('name')->get();
        return view('user.edit', compact('User', 'roles', 'membership'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            // "phone.required" => "Remplir le champ Numéro de téléphone!",
            // "phone.numeric" => "Le champ Numéro de téléphone doit être numérique!",
            // "phone.digits" => "Le champ Numéro de téléphone doit comporter exactement 8 chiffres!",
        ];

        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            // 'phone' => ['numeric', 'digits:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId()))],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $User = $this->findCompanyUser($id);
            $companyId = app(CompanyContext::class)->getCompanyId();
            $role = Role::where('company_id', $companyId)->findOrFail($request->role_id);
            $membership = $User->getMembershipFor($companyId);
            abort_if($membership->role?->key === 'owner' && $role->key !== 'owner', 403, 'Le propriétaire ne peut pas être rétrogradé.');
            abort_if($membership->role?->key !== 'owner' && $role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être attribué.');
            $User->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);
            $membership->update(['role_id' => $role->id]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "Le nom de '".$request-> name."' a bien été mis à jour"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $User = $this->findCompanyUser($id);
        $membership = $User->getMembershipFor(app(CompanyContext::class)->getCompanyId());
        if($membership->status === 'active'){
            $updating = $membership->update(['status' => 'inactive']);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN UTILISATEUR',
                'text' => auth()->user()->name." a désactivé l'utilisateur : ".$User->name,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "ARCHIVAGE REUSSIE",
                "msg" => "L'utilisateur a bien été désactivé"
            ]);
        }else{
            $updating2 = $membership->update(['status' => 'active']);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN UTILISATEUR',
                'text' => auth()->user()->name." a restaurer l'utilisateur : ".$User->name,
            ]); 

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "L'utilisateur a bien été restauré"
            ]);
        }
    }

    private function findCompanyUser(int|string $id): User
    {
        $companyId = app(CompanyContext::class)->getCompanyId();

        return User::whereHas('memberships', fn ($query) => $query->where('company_id', $companyId))
            ->findOrFail($id);
    }

    public function outUser(Request $request)
    {
        // Auth::logout($user);
        $request->session()->invalidate();

        return response()->json([
            "status" => true,
            "reload" => true,
            "redirect_to" => route('user_login'),
            "title" => "DECONNEXION REUSSIE",
            'check' => Auth::check(),
            "msg" => "Au revoir, a bientot"
        ]);
    }
}
