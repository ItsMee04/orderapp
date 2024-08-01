<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::select('id', 'name', 'price', 'images')->get();
        return response()->json(["data" => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          =>  'required|max:255',
            'price'         =>  'required|integer',
            'image_file'    =>  'nullable|mimes:png,jpg'
        ]);

        if ($request->file('image_file')) {
            $file = $request->file('image_file');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '-' . $fileName;

            Storage::disk('public')->putFileAs('items', $file, $newName);

            $request['images'] = $newName;
        }

        $item = Item::create($request->all());

        return response()->json(["data" => $item]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          =>  'required|max:255',
            'price'         =>  'required|integer',
            'image_file'    =>  'nullable|mimes:png,jpg'
        ]);

        if ($request->file('image_file')) {
            $file = $request->file('image_file');
            $fileName = $file->getClientOriginalName();
            $newName = Carbon::now()->timestamp . '-' . $fileName;

            Storage::disk('public')->putFileAs('items', $file, $newName);

            $request['images'] = $newName;
        }

        $item = Item::findOrFail($id);
        $item->update($request->all());

        return response()->json(["data" => $item]);
    }
}
