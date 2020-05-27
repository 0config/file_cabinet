# Enable logging 

add a log channel to `config/logging.php` like this  inside `'channels' => ` node

```php
'file_cabinet' => [
            'driver' => 'single',
            'path'   => storage_path('logs/file_cabinet.log'),
            'level'  => 'info',
], 
```

// Try writing log as below , from you application 
```php
Log::channel('file_cabinet')->info('Hello world!!');
```

and see : `tail -f storage/logs/file_cabinet.log`

If config is not done properly, log may end up in standard log 




# Migrate  : 

move migration folder to local migration `mv vendor/0config/file-cabinet/src/database/migrations/*.php database/migrations`

migrate up : `php artisan migrate`

#### Note: if migrate up does not work, Please run `php artisan migrate --path=vendor/0config/file-cabinet/src/database/migrations/`






## Route example from local / web.php : 

```php
// IMPORT BELOW two lines 
//use Illuminate\Http\Request;
//use ZeroConfig\FileCabinet\App\Http\Controllers\UploadFileController;

// for file_cabinet starts


Route::get('/local_files/{mimetype}/{model_name}/{model_id}:{channel}::{id}/', function (Request $request , $mimetype,  $model_name, $model_id, $channel, $id ) {

    $info = UploadFileController::validateRecord($request );

    return view('local_upload', compact('info',  'mimetype', 'model_id', 'model_name', 'channel' ));

});
Route::post('/local_files/{mimetype}/{model_name}/{model_id}:{channel}::{id}/', function (Request $request , $mimetype,  $model_name, $model_id, $channel, $id ) {
    return UploadFileController::upsert($request, $mimetype);
});

Route::get('/local_files/{mimetype}/destroy/{model_name}/{model_id}:{channel}::{id}/', function (Request $request) {
    return UploadFileController::destroy($request);
});


// for file_cabinet ENDS 

```



 ## create view in appropriate local path 
 
 - file name `local_upload.blade.php` location: `resources/views`

```
<html>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<title> File Cabinet : Universal File Manager </title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<body>
<div class="container">
    <br><br>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">

            <h1 class="text-center"> File Manager </h1> <br>
            <?php
            echo Form::open(array('url' => ($_SERVER['REQUEST_URI']), 'files' => 'true', 'data-toggle' => "validator", 'role' => "form", 'required' => 'true', "novalidate" => "true"));
            ?>
            <div class="form-group">
                <label for="inputImage" class="control-label">Image File</label>

                <?php
                echo Form::file('image', ['required' => 'true', 'class' => 'form-control', 'accept' => $info['validMimetype'] ]); // if $mimeType is not passed default will be image type
                echo $info['fileCabinet']->file_name ?? '';
                ?>
                <div class="help-block with-errors">
                    <ul class="list-unstyled">
                        <li></li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <label for="inputName" class="control-label">Name</label>
                <?php
                echo Form::text('name', $info['fileCabinet']->name ?? '', ['required' => 'true', 'class' => 'form-control']);
                ?>
                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                <div class="help-block with-errors">
                    <ul class="list-unstyled">
                        <li></li>
                    </ul>
                </div>
            </div>
            <br><br><br>
            <?php
            $uploadUpdate = $info['fileCabinet'] ? ' Update ' : ' Upload ';
            echo Form::submit($uploadUpdate . ' File', ['required' => 'true', 'class' => 'form-control btn btn-primary']);
            ?>

            <?php echo Form::close();
            //    if ($info['fileCabinet']) dump($info['fileCabinet']); ?>


            <hr/>


           @php( $allFiles =  ( \ZeroConfig\FileCabinet\FileCabinet::select(['id', 'model_name', 'model_id', 'name', 'file_name', 'channel'])
                            ->where('user_id',  \Illuminate\Support\Facades\Auth::id()) )
                            ->where('model_name', $model_name)
                            ->where('model_id', $model_id)
                            ->where('channel', $channel)
                            ->get()
            )

            @foreach($allFiles as $fileArr)
                model_id-{{ $fileArr->model_id }}  ::
                id-{{ $fileArr->id }}  ::
                channel-{{ $fileArr->channel }}  ::
                file_name-{{ $fileArr->file_name }} ::
                <BR>
                    <BR>
            @endforeach


        </div>


        <div class="col-md-2"></div>
    </div>


</div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://1000hz.github.io/bootstrap-validator/dist/validator.min.js"></script>
</body>
</html>




```


## make sure to load this template from route file : 
```
/local_files/image/FileCabinet/3:1::0

/{static_path}/{mimetype}/{ModelName}/{modelId}:{channel}:{fileCabId}/
fileCabId = 0 ; // new file
fileCabId > 0 ; // will edit record based on this value 
```


eg: /local_files/image/FileCabinet/3:1::0
this will `CREATE` an entry for `FileCabinet` model's id= 2, set channel = 1 and `::0` will create  a new record 


e.g.: /local_files/image/FileCabinet/1:5::12
this will `UPDATE`  entry for `FileCabinet` model's id= 1, set channel = 5 and `::12` will update FileCabinet.id = 12 




# Relationships : 

## in `User` model : add below 



```php
// import on top
// use ZeroConfig\FileCabinet\FileCabinet; 
    public function files()
    {
        return $this->hasMany(FileCabinet::class);
    }

    public function filesUser()
    {
        return $this->hasMany(FileCabinet::class)
            ->where('model_name', '=', 'User');
    }
    public function filesFileCabinet()
    {
        return $this->hasMany(FileCabinet::class)
            ->where('model_name', '=', 'FileCabinet');
    }

```

## in your web.php // add routes

```php
Route::get('users/{id?}', function ($id = 1 ) {
    return \App\User::with(['filesUser', 'filesFileCabinet', 'files'])->find($id);
});

```

## now create some records and browse :
: http://localhost:8000/users/1
`/users/1`
`/users/2`

you should get desired response.



# Destroy 

/local_files/{mimetype}/destroy/{model_name}/{model_id}:{channel}::{id}/

see there is `destroy` between routes, rest is same 



# Restrict and relax ownership : 

`UploadFileController::upsert($request, false); // default 2nd param is true ` 
if you set it to false ownership is not checked
BE VERY CAREFUL this will transfer ownership as well.. 




