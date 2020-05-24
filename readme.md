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
Route::get('/local_files/{model_name}/{model_id}:{channel}::{id}/', function (Request $request) {

    $info = UploadFileController::validateRecord($request);

    return view('local_upload', compact('info'));

});
Route::post('/local_files/{model_name}/{model_id}:{channel}::{id}/', function (Request $request) {
    return UploadFileController::upsert($request);
});

// for file_cabinet ENDS 

```



 ## create view in appropriate local path 
 
 - file name `local_upload.blade.php` location: `resources/views`

```
<html>
<body>
<?php

echo Form::open(array('url' => ($_SERVER['REQUEST_URI']), 'files' => 'true'));
echo 'Select the file to upload.';
echo Form::file('image',  ['required' => 'true']);
echo $info['fileCabinet']->file_name ?? '';



echo "<BR> name" . Form::text('name', $info['fileCabinet']->name ?? '');


echo "<BR>";


$uploadUpdate = $info['fileCabinet'] ? ' Update ' :  ' Upload ';

echo Form::submit( $uploadUpdate . ' File');
echo Form::close();

if( $info['fileCabinet'] ) dump ($info['fileCabinet']);
?> </body>
</html>


```


## make sure to load this template from route file : 
```
/local_files/FileCabinet/1:1::0
/{static_path}/{ModelName}/{modelId}:{channel}:{fileCabId}/
fileCabId = 0 ; // new file
fileCabId > 0 ; // will edit record based on this value 
```


eg: /local_files/FileCabinet/2:1::0
this will `CREATE` an entry for `FileCabinet` model's id= 2, set channel = 1 and `::0` will create  a new record 


e.g.: /local_files/FileCabinet/1:5::12
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

/local_files/destroy/User/2:1::11

see there is `destroy` between routes, rest is same 



# Restrict and relax ownership : 

`UploadFileController::upsert($request, false); // default 2nd param is true ` 
if you set it to false ownership is not checked
BE VERY CAREFUL this will transfer ownership as well.. 




