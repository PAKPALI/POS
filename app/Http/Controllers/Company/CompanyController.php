<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Services\CompanyContext;
use App\Services\CompanyProvisioner;
use App\Services\EntitlementService;
use App\Exceptions\SubscriptionLimitReached;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyContext $context,
        private CompanyProvisioner $provisioner,
        private EntitlementService $entitlements,
    ) {}

    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = Company::whereKey($this->context->getCompanyId())->get();
        $memberships = CompanyUser::where('user_id', Auth::id())
            ->where('status', 'active')
            ->whereHas('company', fn ($query) => $query->where('status', 'active'))
            ->with('company', 'role')
            ->orderByDesc('last_accessed_at')
            ->get();
        $activeCompanyId = $this->context->getCompanyId();
        $currentMembership = $memberships->firstWhere('company_id', $activeCompanyId);
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                     $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>';
                        return $btn;
                })
                ->editColumn('number2', function ($Object) {
                    return $Object->number2 ?? '--';
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('company.index', compact('Object', 'memberships', 'activeCompanyId', 'currentMembership'));
    }

    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            "email.required" => "Remplir le champ Email!",
            "adress.required" => "Remplir le champ Adresse!",
            "number1.required" => "Remplir le champ Numéro1!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            'email' => ['required'],
            'adress' => ['required'],
            'number1' => ['required'],
            'default_tax' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);
            // Action::create([
            //     'user_id' => auth()->user()->id,
            //     'function' => 'AJOUT CATEGORIE',
            //     'text' => auth()->user()->name." a créer une nouvelle catégorie '".$request->name."'",
            // ]);
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'adress' => $request->adress,
                'number1' => $request->number1,
                'number2' => $request->number2,
                'message' => $request->message,
                'description' => $request->description,
                'ecommerce_active' => $request->boolean('ecommerce_active'),
            ];

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = Str::uuid().'.'.$file->extension();
                $file->move(public_path('images'), $filename);
                $data['logo'] = 'images/'.$filename;
            }

            $data['created_by'] = Auth::id();
            try {
                $company = DB::transaction(function () use ($data, $request) {
                    $this->entitlements->assertCanAdd($this->context->getCompany(), 'company');
                    $company = Company::create($data);
                    $this->provisioner->provision(
                        $company,
                        Auth::user(),
                        $request->filled('default_tax') ? (float) $request->default_tax : null
                    );

                    return $company;
                });
            } catch (SubscriptionLimitReached $exception) {
                return response()->json([
                    'status' => false,
                    'reload' => false,
                    'title' => 'LIMITE DU PLAN ATTEINTE',
                    'msg' => 'Votre plan actuel ne permet pas de créer une nouvelle entreprise. Veuillez passer à un plan supérieur pour continuer.',
                ], 422);
            }

            return response()->json([
                "status" => true,
                "reload" => true,
                "company_id" => $company->id,
                "company_name" => $company->name,
                "switch_url" => route('companies.switch', $company->id),
                "selection_url" => route('companies.select'),
                "title" => "AJOUT REUSSI",
                "msg" => "La compagnie au nom de ".$request->name." a bien été ajoutée"
            ]);
    }

    public function show(string $id)
    {
        $Company = Company::whereKey($this->context->getCompanyId())->findOrFail($id);
        return view('company.show', compact('Company'));
    }

    public function edit($id)
    {
        $Company = Company::whereKey($this->context->getCompanyId())->findOrFail($id);
        return view('company.edit', compact('Company'));
    }

    public function update(Request $request, string $id)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            "email.required" => "Remplir le champ Email!",
            "number1.required" => "Remplir le champ Numéro1!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            'email' => ['required'],
            'number1' => ['required'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MISE A JOUR ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);

            $Company = Company::whereKey($this->context->getCompanyId())->findOrFail($id);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'adress' => $request->adress,
                'number1' => $request->number1,
                'number2' => $request->number2,
                'message' => $request->message,
                'description' => $request->description,
                'ecommerce_active' => $request->boolean('ecommerce_active'),
            ];

            if ($Company->created_by == NULL) {
                $data['created_by'] = Auth::user()->id;
            }

            if ($request->hasFile('logo')) {
                if ($Company->logo && file_exists(public_path($Company->logo))) {
                    unlink(public_path($Company->logo));
                }
                $file = $request->file('logo');
                $filename = Str::uuid().'.'.$file->extension();
                $file->move(public_path('images'), $filename);
                $data['logo'] = 'images/'.$filename;
            }

            $Company->update($data);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "La compagnie au nom de '".$request->name."' a bien été mise à jour"
            ]);
    }
}
