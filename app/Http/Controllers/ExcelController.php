<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Config;
use App\Excel;
use App\Jobs\SendMail;
use App\Jobs\ProcessCSV;
use Storage;
use Illuminate\Support\Str;

class ExcelController extends Controller
{
    public function save(Request $request)
    {
    	try
    	{
			$rules 							= [
				'file' 						=> 'required|file',
			];
			$validator 						= Validator::make($request->all(), $rules);
			if ($validator->fails()) {
				return response()->json([
					'status'                => false,
					'message'               => 'Validation failed',
					'errors'                => $validator->errors()
				],400); 
			}
			$s 								= $request->file('file');
			$extension 						= $s->clientExtension();
			if($extension 					!= "csv")
			{
				return response()->json([
					'status'                => false,
					'message'               => 'Please upload a valid CSV file'
				],400);
			}

			$file_name                  	= Str::uuid()->toString().'.csv';
			$res 							= Storage::disk('public')->putFileAs('excel',$request->file,$file_name);
			$path 							= public_path().'/storage/excel/'.$file_name;
			ProcessCSV::dispatch($path);
			return response()->json([
    		    'status' 					=> true,
    		    'message'  					=> 'Your excel is processing. We will share a mail as soon as possible'
    		], 200);
			
    	}
    	catch(\Exception $e)
    	{
    		return response()->json([
    		    'status' 					=> false,
    		    'error'  					=> 'Something went wrong'
    		], 400);
    	}
    }

}
