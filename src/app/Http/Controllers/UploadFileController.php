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

    public static function getFileType($key)
    {

        $typeArray = [
            'audio' => 'audio/*',
            'video' => 'video/*',
            'image' => 'image/*',
            'pdf'   => 'application/pdf',
            'xls'   => 'application/vnd.ms-excel'
        ];

        if (array_key_exists($key, $typeArray)) return $typeArray[$key];
        else self::errorExit(' Mime Type of ' . $key . ' is not allowed ');

    }

    public static function validateRecord(Request $request, $checkOwner = true)
    {

        \Log::channel('file_cabinet')->info(' accessing : ' . URL::current() . PHP_EOL . " user info: " . Auth::user());

        $validatedMimetype = \ZeroConfig\FileCabinet\App\Http\Controllers\UploadFileController::getFileType(request('mimetype'));  // check valid file type

        if (Auth::id() === null) self::errorExit(__CLASS__ . " :  works only with authenticated user. Please make sure to have authenticated users  to access this resource. ");
        //  make my own exception


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

        $modelResult = $modelNamePath::find($modelId); // check if model starts and check by modelId

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

        if ($id > 0 && $fileCabinet['model_name'] !== $modelName) self::errorExit('Improper Model Name. This record belongs to Model : <' . $fileCabinet['model_name'] . ">  NOT  <" . $modelName . ">");


        // FOR passed model and id exits check


        $result['fileCabinet']   = $fileCabinet;
        $result['validMimetype'] = $validatedMimetype;

        return $result;
    }

    public static function upsert(Request $request, $checkOwner = true)
    {
        // TODO move  mime and other check from template to controller
        // TODO use same logic for
        //  -   create ,
        //  -   update / upsert
        //  -   and destroy
        //  TODO keep same validation logic as in

        $validatorResponse = self::validateRecord($request, $checkOwner);

        // check mime type defined is same as posted file


        $file                     = $request->file('image');
        $fileMimeCategory         = explode('/', $file->getMimeType())[0];
        $uploadedFileMimeCategory = explode('/', \ZeroConfig\FileCabinet\App\Http\Controllers\UploadFileController::getFileType(request('mimetype')))[0];


        if ($fileMimeCategory != $uploadedFileMimeCategory) {
            self::errorExit(' Invalid file Format !  Expecting ' . $uploadedFileMimeCategory . ' received ' . $file->getMimeType());
        }


        //Display File Mime Type
//        echo 'File Mime Type: ' . $file->getMimeType();


        $fileCabInfo = $_POST;


        //Move Uploaded File
        $destinationPath  = 'storage'; // this is symlink
        $move             = $file->move($destinationPath, rand(100, 999) . $file->getClientOriginalName());
        $fileNameWithPath = '/' . $move->getPath() . '/' . $move->getFilename();
        $model_id         = (int)request('model_id');


        $insUpd = FileCabinet::updateOrCreate(
            ['id' => request('id')]// chance name here
            ,
            ['channel'      => (int)request('channel') ?? 1
             , 'is_public'  => request('is_public') == 1 ?? true
             , 'name'       => $fileCabInfo['name']
             , 'file_name'  => $fileNameWithPath
             , 'model_name' => request('model_name')
             , 'model_id'   => $model_id

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
        $imageSwapId = "image_" . $model_id;
        echo "<body onload='
                opener.document.getElementById( \"$imageSwapId\").src = \"$fileNameWithPath\";
                self.close();
                '>
</body><a href='#' onclick='self.close()'>Close this Window</a>";
        dump($resp);
        return " THIS SHOULD BE A POPUP elese it will not auto close.. ..  <BR> <h2> if this is a pop up and, you see see this >  Then parent  does not have id : image_{$model_id} </h2> updated image is auto updated.. if everything is setup up correct.. ";
//        return $resp;


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
