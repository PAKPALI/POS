<?php

namespace App\Http\Controllers\CodePromo;

use App\Models\Action;
use App\Models\CodePromo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CodePromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = CodePromo::with('user:id,name')->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a data-id="'.$row->id.'" data-toggle="modal" data-target="#updateModal" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-toggle="modal" data-target="#pdf" class="btn btn-info btn-sm pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-toggle="modal" data-target="#pdf" class="btn btn-info btn-sm pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->expires_at?->isPast()){
                        $btn = '<span class="saas-status-badge is-inactive">Expiré</span>';
                    }elseif($Object->status==1){
                        $btn = '<span class="saas-status-badge is-active">Actif</span>';
                    }else{
                        $btn = '<span class="saas-status-badge is-inactive">Inactif</span>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->editColumn('expires_at', function ($Object) {
                    return $Object->expires_at?->format('d-m-Y H:i') ?? 'Sans expiration';
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        return view('code.index');
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
    // public function store(Request $request)
    // {
    //     $error_messages = [
    //         "name.required" => "Remplir le champ Nom!",
    //         "percents.required" => "Remplir le champ Pourcentage!",
    //         "code.required" => "Générez le code!",
    //         'code.unique' => 'Le code généré de ce code promo existe déjà',
    //         "comments.required" => "Remplir le champ Description!",
    //     ];

    //     $validator = Validator::make($request->all(),[
    //         'name' => ['required'],
    //         'percents' => ['required'],
    //         'code' => 'required|unique:code_promos,code',
    //         'comments' => ['required'],
    //     ], $error_messages);

    //     if($validator->fails())
    //         return response()->json([
    //             "status" => false,
    //             "reload" => false,
    //             "title" => "AJOUT ECHOUE",
    //             "msg" => $validator->errors()->first()
    //         ]);

    //         // Generate the QR code as an image and save it in storage/app/public/qrcodes/
    //         $qrCodePath = 'qrcodes/' . $request->code . '.png';
    //         Storage::disk('public')->put($qrCodePath, QrCode::format('png')->size(200)->generate($request->code));

    //         $data = [
    //             'name' => $request-> name,
    //             'percents' => $request-> percents,
    //             'code' => $request-> code,
    //             'comments' => $request-> comments,
    //             'created_by' => Auth::user()->id,
    //             'qr_code' => $qrCodePath,
    //         ];
            
    //         CodePromo::create($data);

    //         Action::create([
    //             'user_id' => auth()->user()->id,
    //             'function' => 'AJOUT CODE PROMO',
    //             'text' => auth()->user()->name." a créer un nouveau code promo '".$request->name."'",
    //         ]);

    //         return response()->json([
    //             "status" => true,
    //             "reload" => true,
    //             // "redirect_to" => route('user'),
    //             "title" => "AJOUT REUSSI",
    //             "msg" => "Le code promo au nom de ".$request-> name." a bien été ajouté"
    //         ]);
    // }

    // composer require picqer/php-barcode-generator
    public function store(Request $request)
    {
        $normalizedCode = CodePromo::normalizeCode($request->input('code'));
        $request->merge(['normalized_code' => $normalizedCode, 'code' => $normalizedCode]);
        $companyId = app(\App\Services\CompanyContext::class)->getCompanyId();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'percents' => ['required', 'integer', 'min:1', 'max:100'],
            'code' => ['required', 'string', 'min:4', 'max:64', 'regex:/^[A-Z0-9_-]+$/'],
            'normalized_code' => [Rule::unique('code_promos')->where(fn ($query) => $query->where('company_id', $companyId))],
            'comments' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ], ['normalized_code.unique' => 'Ce code existe déjà dans votre entreprise.', 'expires_at.after' => "La date d’expiration doit être dans le futur."]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ÉCHOUÉ",
                "msg" => $validator->errors()->first(),
            ]);
        }

        // Générer le code-barres
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($request->code, $generator::TYPE_CODE_128);
        
        // Définir le chemin de stockage
        $barcodePath = 'barcodes/' . $companyId . '-' . $normalizedCode . '.png';
        Storage::disk('public')->put($barcodePath, $barcodeData);

        // Enregistrer en base de données
        $data = [
            'name' => $request->name,
            'percents' => $request->percents,
            'code' => $request->code,
            'normalized_code' => $normalizedCode,
            'comments' => $request->comments,
            'expires_at' => $request->date('expires_at'),
            'created_by' => Auth::user()->id,
            'qr_code' => $barcodePath, // Sauvegarde du chemin du code-barres
        ];

        CodePromo::create($data);

        // Enregistrement de l'action
        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'AJOUT CODE PROMO',
            'text' => auth()->user()->name . " a créé un nouveau code promo '" . $request->name . "'",
        ]);

        return response()->json([
            "status" => true,
            "reload" => true,
            "title" => "AJOUT RÉUSSI",
            "msg" => "Le code promo au nom de " . $request->name . " a bien été ajouté.",
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $CodePromo = CodePromo::findOrFail($id);
        return view('code.show', compact('CodePromo'));
    }

    public function verifyPromo(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:64']]);
        $promo = CodePromo::usable()
            ->where('normalized_code', CodePromo::normalizeCode($request->input('code')))
            ->first();

        if ($promo) {
            return response()->json(['valid' => true, 'percent' => (int) $promo->percents, 'expires_at' => $promo->expires_at?->toIso8601String()]);
        } else {
            return response()->json(['valid' => false]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $CodePromo = CodePromo::findOrFail($id);
        return view('code.edit', compact('CodePromo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Code = CodePromo::findOrFail($id);
        $normalizedCode = CodePromo::normalizeCode($request->input('code'));
        $request->merge(['normalized_code' => $normalizedCode, 'code' => $normalizedCode]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'percents' => ['required', 'integer', 'min:1', 'max:100'],
            'code' => ['required', 'string', 'min:4', 'max:64', 'regex:/^[A-Z0-9_-]+$/'],
            'normalized_code' => [Rule::unique('code_promos')->where(fn ($query) => $query->where('company_id', $Code->company_id))->ignore($Code->id)],
            'comments' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ], ['normalized_code.unique' => 'Ce code existe déjà dans votre entreprise.', 'expires_at.after' => "La date d’expiration doit être dans le futur."]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MISE A JOUR  ÉCHOUÉE",
                "msg" => $validator->errors()->first(),
            ]);
        }

        // Vérifier si le code a changé
        if ($Code->code !== $request->code) {
            // Supprimer l'ancien code-barres s'il existe
            if ($Code->qr_code && Storage::disk('public')->exists($Code->qr_code)) {
                Storage::disk('public')->delete($Code->qr_code);
            }

            // Générer le nouveau code-barres
            $generator = new BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode($request->code, $generator::TYPE_CODE_128);

            // Définir le chemin du nouveau code-barres
            $barcodePath = 'barcodes/' . $Code->company_id . '-' . $normalizedCode . '.png';
            Storage::disk('public')->put($barcodePath, $barcodeData);

            // Mettre à jour le chemin dans l'objet
            $Code->qr_code = $barcodePath;
        }

        $Code->update([
            'name' => $request->name,
            'percents' => $request->percents,
            'code' => $request->code,
            'normalized_code' => $normalizedCode,
            'comments' => $request->comments,
            'expires_at' => $request->date('expires_at'),
            'created_by' => Auth::user()->id,
        ]);

        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'MODIFIER CODE PROMO',
            'text' => auth()->user()->name . " a modifié le code promo '" . $request->name . "'",
        ]);

        return response()->json([
            "status" => true,
            "reload" => true,
            "title" => "MISE A JOUR RÉUSSIE",
            "msg" => "Le code promo au nom de " . $request->name . " a bien été mis à jour.",
        ]);
    }

    public function code()
    {
        $code = '';
        for ($i = 0; $i < 10; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    public function generatePDF($id)
    {
        $codePromo = CodePromo::findOrFail($id);
        $jokeCode = $this->code();

        $pdf = Pdf::loadView('code.pdf', compact('codePromo', 'jokeCode'));
        
        return $pdf->download('CodePromo_' . $codePromo->code . '.pdf');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = CodePromo::findOrFail($id);
        if($Object->status ==1){
            // update code status
            $Object->update(['status' => 0,]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN CODE PROMO',
                'text' => auth()->user()->name." a désactivé le code promo : ".$Object->name,
            ]);
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "ARCHIVAGE REUSSIE",
                "msg" => "Le code promo ".$Object->name." a bien été désactivée"
            ]);
        }else{
            if ($Object->expires_at?->isPast()) {
                return response()->json([
                    'status' => false,
                    'reload' => false,
                    'title' => 'CODE EXPIRÉ',
                    'msg' => "Modifiez d’abord la date d’expiration avant de réactiver ce code.",
                ]);
            }
            // update code status
            $Object->update(['status' => 1,]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN CODE PROMO',
                'text' => auth()->user()->name." a restauré  le code promo : ".$Object->name,
            ]); 
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "Le code promo ".$Object->name." a bien été restauré"
            ]);
        }
    }
}
