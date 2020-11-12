<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\MigrationPackage\Migration\Schematics;

class CreateCategoriesTable extends Migration {

    use Schematics;

    protected $name;
    private   $installableConfig;


    public function __construct() {
        $this->installableConfig = InstallableConfig::config('Larangular\Categories\CategoriesServiceProvider');
        $this->connection = $this->installableConfig->getConnection('categories');
        $this->name = $this->installableConfig->getName('categories');
    }

    public function up(): void {
        $this->create(function (Blueprint $table) {

            $table->increments('id');
            $table->string('slug');
            $table->{$this->jsonableColumnType()}('name');
            $table
                ->{$this->jsonableColumnType()}('description')
                ->nullable();
            NestedSet::columns($table);


            if ($this->installableConfig->getTimestamp('categories')) {
                $table->softDeletes();
                $table->timestamps();
            }
        });
    }

    public function down(): void {
        $this->drop();
    }

}

