<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\EcommerceManager;
use App\Models\User;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $company = CompanySetting::first();
        $users = User::where('user_type', 2)->get();
        $managers = collect();
        if ($company) {
            $managers = $company->managerUsers;
        }
        return view('ecommerce.admin.settings', compact('company', 'users', 'managers'));
    }

    public function updateSettings(Request $request)
    {
        $company = CompanySetting::first();
        if (!$company) {
            return response()->json([
                'status' => false,
                'title' => 'ERREUR',
                'msg' => 'Aucune compagnie trouvée. Configurez d\'abord la compagnie.'
            ]);
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

        $company->update($data);

        return response()->json([
            'status' => true,
            'title' => 'MISE A JOUR REUSSIE',
            'msg' => 'Les parametres ecommerce ont ete mis a jour.'
        ]);
    }

    public function addManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:company_settings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'ERREUR',
                'msg' => $validator->errors()->first()
            ]);
        }

        $exists = EcommerceManager::where('company_id', $request->company_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'title' => 'ERREUR',
                'msg' => 'Cet utilisateur est deja manager.'
            ]);
        }

        EcommerceManager::create([
            'company_id' => $request->company_id,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'status' => true,
            'title' => 'AJOUT REUSSI',
            'msg' => 'Le manager a ete ajoute.'
        ]);
    }

    public function removeManager($id)
    {
        $manager = EcommerceManager::findOrFail($id);
        $manager->delete();

        return response()->json([
            'status' => true,
            'title' => 'SUPPRESSION REUSSIE',
            'msg' => 'Le manager a ete retire.'
        ]);
    }

    public function managersList($companyId)
    {
        $managers = EcommerceManager::with('user')
            ->where('company_id', $companyId)
            ->get();

        return DataTables::of($managers)
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                return $row->user->name;
            })
            ->addColumn('user_email', function ($row) {
                return $row->user->email;
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-danger btn-sm remove-manager" data-id="'.$row->id.'"><i class="fas fa-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
