<?php

namespace ZeroConfig\FileCabinet\App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Mockery\Exception;
use ZeroConfig\FileCabinet\FileCabinet;

class UploadFileController extends Controller
{

    public static function validateRecord(Request $request, $checkOwner = true)
    {


        \Log::channel('file_cabinet')->info(' accessing : ' . URL::current() . PHP_EOL. " user info: " . Auth::user());
//        \Log::channel('file_cabinet')->warning( ' accessing : '  . URL::current()   . PHP_EOL .  Auth::user() );
//        \Log::channel('file_cabinet')->error( ' accessing : '  . URL::current()   . PHP_EOL .  Auth::user() );


        if (Auth::id() === null) self::errorExit(__CLASS__ . " :  works only with authenticated user. Please make sure to have authenticated users  to access this resource. ");
        // TODO make my own exception


        $modelNamePath        = null;
        $modelNameSpacesPaths = ['\\App\\', '\\App\\Models\\', '\\ZeroConfig\\FileCabinet\\']; // TODO add from .env
        $modelName            = \request('model_name');
        foreach ($modelNameSpacesPaths as $modelNameSpace) {
            $fQModelPath = $modelNameSpace . $modelName;
            if (class_exists($fQModelPath)) $modelNamePath = $fQModelPath;

        }
        if ($modelNamePath === null) self::errorExit($modelName . ':  model does not exist');


        $modelId = (int)\request('model_id');
        $id      = (int)\request('id');

        $modelResult = $modelNamePath::first(); // check if model exists Starts .. that's it for this
        if (!$modelResult) self::errorExit('No Result in : ' . $modelNamePath . ' for id ' . $modelId); //return ' no result found';
        // model exists ends


        $fileCabinet     = FileCabinet::find($id);
        $result['model'] = $fileCabinet;

        if ($id > 0 && !$fileCabinet) {
            self::errorExit(' No result in FileCabinet for ' . $id);
        }

        if ($id > 0 && $fileCabinet->model_id != $modelId) {
            self::errorExit(' Probably this file that you are trying to update does not belong to proper Model.');
        }

        if ($id > 0 && $checkOwner && Auth::id() !== $fileCabinet['user_id']) { // owner check
            self::errorExit('Current user and Record Owner does not match');
        }

        if( $id >0 && $fileCabinet['model_name'] !==$modelName ) self::errorExit('Improper Model Name. This record belongs to Model : <' . $fileCabinet['model_name']  . ">  NOT  <". $modelName . ">"  );



        $result['fileCabinet'] = $fileCabinet;

        return $result;
    }

    public static function upsert(Request $request, $checkOwner = true)
    {


        self::validateRecord($request, $checkOwner);

        $file = $request->file('image');

        //Display File Mime Type
        echo 'File Mime Type: ' . $file->getMimeType();


        $fileCabInfo = $_POST;


        //Move Uploaded File
        $destinationPath  = 'storage'; // this is symlink
        $move             = $file->move($destinationPath, rand(100, 999) . $file->getClientOriginalName());
        $fileNameWithPath = $move->getPath() . '/' . $move->getFilename();


        $insUpd = FileCabinet::updateOrCreate(
            ['id' => request('id')]// chance name here
            ,
            ['channel'      => (int)request('channel') ?? 1
             , 'is_public'  => request('is_public') == 1 ?? true
             , 'name'       => $fileCabInfo['name']
             , 'file_name'  => $fileNameWithPath
             , 'model_name' => request('model_name')
             , 'model_id'   => (int)request('model_id')

             , 'user_id'    => Auth::id()
            ]
        );


        $resp         = [];
        $resp['type'] = 'type';
        if ($insUpd->wasRecentlyCreated) {
            $resp['type']   = 'insert';
            $resp['insert'] = $insUpd;
        } else {
            $resp['type']    = 'update';
            $resp['changes'] = $insUpd->getChanges();
        }
        return $resp;


    }

    public static function destroy(Request $request, $checkOwner = true)
    {

        $record = self::validateRecord($request, $checkOwner);
        if (!$record['fileCabinet']) self::errorExit(' record does not exist');;
        // non static
//        $ins = FileCabinet::destroy($id);
        $destroy               = FileCabinet::destroy($record['fileCabinet']['id']);
        $response              = [];
        $response['type']      = 'destroy';
        $response['destroyed'] = true;
        $response['status']    = $destroy;
        $response['record']    = $record;


        return $response;
    }

    public static function errorExit($message = 'default message here')
    {
        \Log::channel('file_cabinet')->error(' accessing : ' . URL::current() . PHP_EOL . $message . PHP_EOL);
        throw new \Exception($message);
    }

}
