<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileCabinetsTable extends Migration

{
public function up()
    {
    Schema::create('file_cabinets', function (Blueprint $table) {
        $table->increments('id');

        $table->integer('sortorder')->default(0)->nullable() ;
        $table->integer('channel')->default(0)->nullable() ;

        $table->char('name')->nullable() ;
        $table->char('file_name')->nullable() ;
        $table->char('model_name')->nullable() ;
        $table->unsignedInteger('model_id')->nullable() ;
        $table->boolean('is_public')->nullable() ;
        $table->text('notes')->nullable() ;


	 // for foreign key stuff // make sure of plural words below.. this may create weird issues

//	$table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');  // DISABLED

        $table->softDeletes();
        $table->unsignedInteger('user_id');

        $table->index(['id', 'channel', 'model_name', 'model_id', 'deleted_at' ]);

        $table->timestamps();
    });
}

    public function down()
    {
    Schema::dropIfExists('file_cabinets');
    }
}

 ?>
