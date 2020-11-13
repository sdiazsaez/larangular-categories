<?php

namespace Larangular\Categories;

use Larangular\Installable\{Contracts\HasInstallable,
    Contracts\Installable,
    Facades\InstallableConfig,
    Installer\Installer};
use Larangular\Installable\Support\{InstallableServiceProvider as ServiceProvider, PublisableGroups};

class CategoriesServiceProvider extends ServiceProvider implements HasInstallable {

    protected $defer = false;

    public function boot(): void {
        $this->loadMigrationsFrom([
            __DIR__ . '/database/migrations',
            database_path('migrations/categories'),
        ]);

        $this->declareMigrationGlobal();
        $this->declareMigrationCategories();
    }

    public function register(): void {
        $this->app->singleton('larangular.categories.installable', static function () {
            return InstallableConfig::config(__CLASS__);
        });
    }

    public function installer(): Installable {
        return new Installer(__CLASS__);
    }

    private function declareMigrationGlobal(): void {
        $this->declareMigration([
            'connection'   => 'mysql',
            'migrations'   => [
                'local_path' => base_path() . '/vendor/larangular/categories/database/migrations',
            ],
            'seeds'        => [
                'local_path' => __DIR__ . '/../database/seeds',
            ],
            'seed_classes' => [],
        ]);
    }

    private function declareMigrationCategories(): void {
        $this->declareMigration([
            'name'      => 'categories',
            'timestamp' => true,
        ]);

        $this->declareMigration([
            'name'      => 'categorizables',
            'timestamp' => true,
        ]);
    }
}
