<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\MigrationPackage\Migration\Schematics;

class CreateCategorizablesTable extends Migration {

    use Schematics;

    protected $name;
    private   $installableConfig;


    public function __construct() {
        $this->installableConfig = InstallableConfig::config('Larangular\Categories\CategoriesServiceProvider');
        $this->connection = $this->installableConfig->getConnection('categorizables');
        $this->name = $this->installableConfig->getName('categorizables');
    }

    public function up(): void {
        $categoryTable = $this->installableConfig->getName('categories');
        $this->create(function (Blueprint $table) use ($categoryTable) {
            $table->integer('category_id')
                  ->unsigned();
            $table->morphs('categorizable');

            // Indexes
            $table->unique([
                'category_id',
                'categorizable_id',
                'categorizable_type',
            ], 'categorizables_ids_type_unique');
            $table->foreign('category_id')
                  ->references('id')
                  ->on($categoryTable)
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            if ($this->installableConfig->getTimestamp('categorizables')) {
                $table->softDeletes();
                $table->timestamps();
            }
        });
    }

    public function down(): void {
        $this->drop();
    }

}

