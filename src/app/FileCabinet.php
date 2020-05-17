<?php

//namespace App;
namespace ZeroConfig\FileCabinet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/** @mixin \Eloquent */

class FileCabinet extends Model
{
    use SoftDeletes;

    protected $table = 'file_cabinets';

    protected $dates = ['deleted_at'];

    protected $casts = [ 'sortorder' => 'integer', 'channel' => 'integer',  'model_id' => 'integer', 'is_public' => 'boolean',  'user_id' => 'integer'  ];

    //
     protected $fillable = [ 'sortorder', 'channel', 'name', 'file_name', 'model_name', 'model_id', 'is_public', 'notes',  'user_id' ]; // fillable ends here

     public static function validateRules() {
	 return  [
		'name' => 'nullable|min:0',
		'file_name' => 'required|min:5',
		'model_name' => 'required|min:3',
		'model_id' => 'required',
		'is_public' => 'nullable',
		'notes' => 'nullable|min:0',
		]  ;
	 }



	 public function model(){
		 return $this->belongsTo(Model::class);
	 }

	 /*
	 // Cut out below each commented stuffs and add it in it's related controllers delete / destroy section..

	 \App\FileCabinet::where('model_id', $id)->delete();


	 public function file_cabinets(){ // for Model
		 return $this->hasMany(FileCabinet::class);
	 }

	 // cut out ends
	 */
}


 ?>
