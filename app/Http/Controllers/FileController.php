<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    //
    public function upload(Request $request){
        $request->validate([
           'file' => 'required|mimes:jpg,jpeg,png|max:204'
        ]);

        if($request->file()) {
            $file_name = time().'_'.$request->file->getClientOriginalName();
            $request->file('file')->storeAs('uploads', $file_name, 'public');
            return response()->json(['success'=>'File uploaded successfully.']);
        }
    }
}
