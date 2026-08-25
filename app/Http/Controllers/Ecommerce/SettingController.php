<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\EcommerceManager;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SettingController extends Controller
{
    private const RESERVED_SLUGS = [
        'admin', 'api', 'app', 'boutique', 'checkout', 'connexion', 'login',
        'register', 'shop', 'support', 'systeme', 'www',
    ];

    public function __construct(private CompanyContext $context) {}

    public function index()
    {
        $companyId = $this->context->getCompanyId();
        $company = CompanySetting::findOrFail($companyId);
        $users = User::where('status', 1)
            ->whereHas('memberships', fn ($query) => $query
                ->where('company_id', $companyId)
                ->where('status', 'active'))
            ->orderBy('name')
            ->get();
        $managers = $company->managerUsers;

        return view('ecommerce.admin.settings', compact('company', 'users', 'managers'));
    }

    public function updateSettings(Request $request)
    {
        $company = CompanySetting::findOrFail($this->context->getCompanyId());
        $slug = $this->normalizeSlug($request->string('slug')->toString());
        $slugValidator = $this->slugValidator($slug, $company->id);

        if ($slugValidator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'ADRESSE INDISPONIBLE',
                'msg' => $slugValidator->errors()->first('slug'),
            ], 422);
        }

        $slugChanged = $slug !== $company->slug;
        if ($slugChanged && ! $request->boolean('confirm_slug_change')) {
            return response()->json([
                'status' => false,
                'requires_confirmation' => true,
                'title' => 'CONFIRMATION REQUISE',
                'msg' => 'Changer cette adresse désactivera immédiatement l’ancien lien déjà partagé.',
            ], 422);
        }

        $data = [
            'description' => $request->description,
            'ecommerce_active' => $request->boolean('ecommerce_active'),
        ];

        if ($request->hasFile('logo')) {
            if ($company->logo && file_exists(public_path($company->logo))) {
                unlink(public_path($company->logo));
            }
            $file = $request->file('logo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $data['logo'] = 'images/'.$filename;
        }

        $company->fill($data);
        $company->slug = $slug;

        try {
            $company->save();
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'status' => false,
                    'title' => 'ADRESSE INDISPONIBLE',
                    'msg' => 'Cette adresse vient d’être choisie par une autre entreprise. Essayez-en une autre.',
                ], 422);
            }

            throw $exception;
        }

        return response()->json([
            'status' => true,
            'title' => 'MISE À JOUR RÉUSSIE',
            'msg' => 'Les paramètres E-commerce ont été mis à jour.',
            'slug' => $company->slug,
            'storefront_url' => route('storefront.home', ['company' => $company->slug]),
        ]);
    }

    public function checkSlug(Request $request)
    {
        $companyId = $this->context->getCompanyId();
        $slug = $this->normalizeSlug($request->string('slug')->toString());
        $validator = $this->slugValidator($slug, $companyId);

        return response()->json([
            'status' => true,
            'available' => ! $validator->fails(),
            'slug' => $slug,
            'storefront_url' => $slug !== '' ? route('storefront.home', ['company' => $slug]) : null,
            'msg' => $validator->fails()
                ? $validator->errors()->first('slug')
                : 'Cette adresse est disponible.',
        ]);
    }

    public function addManager(Request $request)
    {
        $companyId = $this->context->getCompanyId();
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('company_user', 'user_id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('status', 'active')),
            ],
        ], [
            'user_id.required' => 'Sélectionnez un utilisateur.',
            'user_id.exists' => 'Cet utilisateur n’est pas un membre actif de cette entreprise.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'AJOUT IMPOSSIBLE',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        if (EcommerceManager::where('company_id', $companyId)
            ->where('user_id', $request->integer('user_id'))->exists()) {
            return response()->json([
                'status' => false,
                'title' => 'AJOUT IMPOSSIBLE',
                'msg' => 'Cet utilisateur est déjà manager de cette boutique.',
            ], 422);
        }

        EcommerceManager::create([
            'company_id' => $companyId,
            'user_id' => $request->integer('user_id'),
        ]);

        return response()->json([
            'status' => true,
            'title' => 'AJOUT RÉUSSI',
            'msg' => 'Le manager a été ajouté.',
        ]);
    }

    public function removeManager(int $id)
    {
        $manager = EcommerceManager::where('company_id', $this->context->getCompanyId())
            ->findOrFail($id);
        $manager->delete();

        return response()->json([
            'status' => true,
            'title' => 'RETRAIT RÉUSSI',
            'msg' => 'Le manager a été retiré.',
        ]);
    }

    public function managersList()
    {
        $companyId = $this->context->getCompanyId();
        $managers = EcommerceManager::with('user')
            ->where('company_id', $companyId)
            ->whereHas('user.memberships', fn ($query) => $query
                ->where('company_id', $companyId)
                ->where('status', 'active'))
            ->get();

        return DataTables::of($managers)
            ->addIndexColumn()
            ->addColumn('user_name', fn ($row) => $row->user->name)
            ->addColumn('user_email', fn ($row) => $row->user->email)
            ->addColumn('action', fn ($row) => '<button class="btn btn-danger btn-sm remove-manager" data-id="'.$row->id.'" data-no-server-loader><i class="fas fa-trash"></i></button>')
            ->rawColumns(['action'])
            ->make(true);
    }

    private function normalizeSlug(string $value): string
    {
        return Str::limit(Str::slug($value), 80, '');
    }

    private function slugValidator(string $slug, int $companyId)
    {
        return Validator::make(['slug' => $slug], [
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED_SLUGS),
                Rule::unique((new Company)->getTable(), 'slug')->ignore($companyId),
            ],
        ], [
            'slug.required' => 'Saisissez une adresse pour votre boutique.',
            'slug.min' => 'L’adresse doit contenir au moins 3 caractères.',
            'slug.regex' => 'Utilisez uniquement des lettres, chiffres et tirets.',
            'slug.not_in' => 'Cette adresse est réservée par la plateforme.',
            'slug.unique' => 'Cette adresse est déjà utilisée par une autre entreprise.',
        ]);
    }
}
