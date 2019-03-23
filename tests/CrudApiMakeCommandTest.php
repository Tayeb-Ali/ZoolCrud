<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * Class CrudApiMakeCommandTest
 * @package Tests
 */
class CrudApiMakeCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_api_crud_files()
    {
        $this->artisan('make:crud-api', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertNotContains("{$this->model_name} model already exists.", app(Kernel::class)->output());

        $this->assertFileExists(app_path($this->model_name.'.php'));
        $this->assertFileExists(app_path("Http/Controllers/Api/{$this->model_name}Controller.php"));

        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$this->table_name.'_table.php');
        $this->assertFileExists($migrationFilePath);

        $this->assertFileNotExists(resource_path("views/{$this->table_name}/index.blade.php"));
        $this->assertFileNotExists(resource_path("views/{$this->table_name}/forms.blade.php"));

        $localeConfig = config('app.locale');
        $this->assertFileExists(resource_path("lang/{$localeConfig}/{$this->lang_name}.php"));

        $this->assertFileExists(base_path("routes/api.php"));
        $this->assertFileExists(app_path("Policies/{$this->model_name}Policy.php"));
        $this->assertFileExists(database_path("factories/{$this->model_name}Factory.php"));
        $this->assertFileExists(base_path("tests/Unit/Models/{$this->model_name}Test.php"));
        $this->assertFileExists(base_path("tests/Feature/Api/Manage{$this->model_name}Test.php"));
    }

    /** @test */
    public function it_generate_api_crud_files_even_if_model_exists()
    {
        $this->artisan('make:model', ['name' => $this->model_name, '--no-interaction' => true]);
        $this->artisan('make:crud-api', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertContains("We will use existing {$this->model_name} model.", app(Kernel::class)->output());

        $this->assertFileExists(app_path($this->model_name.'.php'));
        $this->assertFileExists(app_path("Http/Controllers/Api/{$this->model_name}Controller.php"));

        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$this->table_name.'_table.php');
        $this->assertFileNotExists($migrationFilePath);

        $localeConfig = config('app.locale');
        $this->assertFileNotExists(resource_path("lang/{$localeConfig}/{$this->lang_name}.php"));

        $this->assertFileNotExists(base_path("routes/web.php"));
        $this->assertFileNotExists(app_path("Policies/{$this->model_name}Policy.php"));
        $this->assertFileNotExists(database_path("factories/{$this->model_name}Factory.php"));
        $this->assertFileNotExists(base_path("tests/Unit/Models/{$this->model_name}Test.php"));
        $this->assertFileExists(base_path("tests/Feature/Api/Manage{$this->model_name}Test.php"));
    }

    /** @test */
    public function it_cannot_generate_crud_files_if_namespaced_model_exists()
    {
        $this->artisan('make:model', ['name' => 'Entities/Projects/Problem', '--no-interaction' => true]);
        $this->artisan('make:crud-api', ['name' => 'Entities/Projects/Problem', '--no-interaction' => true]);

        $this->assertContains("We will use existing Problem model.", app(Kernel::class)->output());

        $this->assertFileExists(app_path('Entities/Projects/Problem.php'));
        $this->assertFileExists(app_path("Http/Controllers/Api/ProblemController.php"));

        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_problems_table.php');
        $this->assertFileNotExists($migrationFilePath);

        $this->assertFileNotExists(resource_path("views/problems/index.blade.php"));
        $this->assertFileNotExists(resource_path("views/problems/forms.blade.php"));

        $localeConfig = config('app.locale');
        $this->assertFileNotExists(resource_path("lang/{$localeConfig}/{$this->lang_name}.php"));

        $this->assertFileNotExists(app_path("Policies/ProblemPolicy.php"));
        $this->assertFileNotExists(database_path("factories/ProblemFactory.php"));
        $this->assertFileNotExists(base_path("tests/Unit/Models/ProblemTest.php"));
        $this->assertFileExists(base_path("tests/Feature/Api/ManageProblemTest.php"));

        $this->removeFileOrDir(app_path('Entities/Projects'));
        $this->removeFileOrDir(resource_path('views/problems'));
        $this->removeFileOrDir(resource_path("lang/en/problem.php"));
    }

    /** @test */
    public function it_can_generate_crud_files_for_namespaced_model()
    {
        $inputName = 'Entities/References/Category';
        $modelName = 'Category';
        $pluralModelName = 'Categories';
        $tableName = 'categories';
        $langName = 'category';
        $modelPath = 'Entities/References';

        $this->artisan('make:crud-api', ['name' => $inputName, '--no-interaction' => true]);

        $this->assertNotContains("{$modelName} model already exists.", app(Kernel::class)->output());

        $this->assertFileExists(app_path($modelPath.'/'.$modelName.'.php'));
        $this->assertFileExists(app_path("Http/Controllers/Api/{$modelName}Controller.php"));

        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$tableName.'_table.php');
        $this->assertFileExists($migrationFilePath);

        $this->assertFileNotExists(resource_path("views/{$tableName}/index.blade.php"));
        $this->assertFileNotExists(resource_path("views/{$tableName}/forms.blade.php"));

        $localeConfig = config('app.locale');
        $this->assertFileExists(resource_path("lang/{$localeConfig}/{$langName}.php"));

        $this->assertFileExists(app_path("Policies/{$modelName}Policy.php"));
        $this->assertFileExists(database_path("factories/{$modelName}Factory.php"));
        $this->assertFileExists(base_path("tests/Unit/Models/{$modelName}Test.php"));
        $this->assertFileExists(base_path("tests/Feature/Api/Manage{$modelName}Test.php"));
    }

    /** @test */
    public function it_can_generate_crud_files_with_parent_option()
    {
        $inputName = 'Entities/References/Category';
        $modelName = 'Category';
        $parentName = 'Projects';
        $pluralModelName = 'Categories';
        $tableName = 'categories';
        $langName = 'category';
        $modelPath = 'Entities/References';

        $this->artisan('make:crud-api', ['name' => $inputName, '--parent' => $parentName, '--no-interaction' => true]);

        $this->assertNotContains("{$modelName} model already exists.", app(Kernel::class)->output());

        $this->assertFileExists(app_path($modelPath.'/'.$modelName.'.php'));
        $this->assertFileExists(app_path("Http/Controllers/Api/{$parentName}/{$modelName}Controller.php"));

        $migrationFilePath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$tableName.'_table.php');
        $this->assertFileExists($migrationFilePath);

        $this->assertFileNotExists(resource_path("views/{$tableName}/index.blade.php"));
        $this->assertFileNotExists(resource_path("views/{$tableName}/forms.blade.php"));

        $localeConfig = config('app.locale');
        $this->assertFileExists(resource_path("lang/{$localeConfig}/{$langName}.php"));

        $this->assertFileExists(database_path("factories/{$modelName}Factory.php"));
        $this->assertFileExists(base_path("tests/Unit/Models/{$modelName}Test.php"));
        $this->assertFileExists(app_path("Policies/{$parentName}/{$modelName}Policy.php"));
        $this->assertFileExists(base_path("tests/Feature/Api/Manage{$modelName}Test.php"));
    }
}
