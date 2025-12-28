<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\User;

class AssetsController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::orderBy('name')->get();
        $assets = Asset::with('category')->orderBy('created_at','desc')->paginate(20);
        $users = User::where('is_active', true)->orderBy('name')->get(['id','name']);
        return view('modules.assets.index', compact('categories','assets','users'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:100|unique:asset_categories,name','description'=>'nullable|string','is_active'=>'boolean']);
        $cat = AssetCategory::create($data);
        return response()->json(['success'=>true,'category'=>$cat]);
    }

    public function storeAsset(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $today = date('Ymd');
        $last = Asset::whereDate('created_at', today())->where('asset_code','like','AST'.$today.'-%')->orderBy('id','desc')->first();
        $seq = 1; if($last && preg_match('/AST\d{8}-(\d{3})/',$last->asset_code,$m)){ $seq = (int)$m[1]+1; }
        $code = 'AST'.$today.'-'.str_pad($seq,3,'0',STR_PAD_LEFT);
        $asset = Asset::create(array_merge($data,['asset_code'=>$code,'status'=>'active']));
        return response()->json(['success'=>true,'asset'=>$asset]);
    }

    public function assign(Request $request, Asset $asset)
    {
        $data = $request->validate(['user_id'=>'required|exists:users,id','assigned_at'=>'required|date','remarks'=>'nullable|string']);
        AssetAssignment::create(array_merge($data,['asset_id'=>$asset->id]));
        $asset->update(['status'=>'in_use']);
        return response()->json(['success'=>true]);
    }

    public function return(Request $request, AssetAssignment $assignment)
    {
        $data = $request->validate(['returned_at'=>'required|date','remarks'=>'nullable|string']);
        $assignment->update($data);
        $assignment->asset->update(['status'=>'active']);
        return response()->json(['success'=>true]);
    }
}








