<?php

namespace App\Http\Controllers\CodePromo;

use App\Models\Action;
use App\Models\CodePromo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;

class CodePromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = CodePromo::latest()->get();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Inactif</a>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
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
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            "percents.required" => "Remplir le champ Pourcentage!",
            "code.required" => "Générez le code!",
            "code.unique" => "Le code généré de ce code promo existe déjà",
            "comments.required" => "Remplir le champ Description!",
        ];

        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'percents' => ['required'],
            'code' => 'required|unique:code_promos,code',
            'comments' => ['required'],
        ], $error_messages);

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
        $barcodePath = 'barcodes/' . $request->code . '.png';
        Storage::disk('public')->put($barcodePath, $barcodeData);

        // Enregistrer en base de données
        $data = [
            'name' => $request->name,
            'percents' => $request->percents,
            'code' => $request->code,
            'comments' => $request->comments,
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
        $code = $request->input('code');
        $promo = CodePromo::where('code', $code)->first();

        if ($promo) {
            return response()->json(['valid' => true, 'promo' => $promo]);
        } else {
            return response()->json(['valid' => false]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
