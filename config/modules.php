<?php

use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Commands;

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */

    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */

    'stubs' => [
        'enabled' => false,
        'path' => base_path('stubs/workdo-stubs'),
        'files' => [
            'seeders/PermissionTableSeeder' => 'Database/Seeders/PermissionTableSeeder.php',
            'seeders/MarketPlaceSeederTableSeeder' => 'Database/Seeders/MarketPlaceSeederTableSeeder.php',
            'http/controllers/company/settingscontroller' => 'Http/Controllers/Company/SettingsController.php',
            'http/controllers/superadmin/settingscontroller' => 'Http/Controllers/SuperAdmin/SettingsController.php',
            'listener/companymenu' => 'Listeners/CompanyMenuListener.php',
            'listener/companysetting' => 'Listeners/CompanySettingListener.php',
            'listener/companysettingmenu' => 'Listeners/CompanySettingMenuListener.php',
            'listener/superadminmenu' => 'Listeners/SuperAdminMenuListener.php',
            'listener/superadminsetting' => 'Listeners/SuperAdminSettingListener.php',
            'listener/superadminsettingmenu' => 'Listeners/SuperAdminSettingMenuListener.php',
            'providers/eventserviceprovider' => 'Providers/EventServiceProvider.php',
            'routes/web' => 'Routes/web.php',
            'routes/api' => 'Routes/api.php',
            'views/company/settings/index' => 'Resources/views/company/settings/index.blade.php',
            'views/super-admin/settings/index' => 'Resources/views/super-admin/settings/index.blade.php',
            'views/marketplace/index' => 'Resources/views/marketplace/index.blade.php',
            'views/index' => 'Resources/views/index.blade.php',
            'views/master' => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'composer' => 'composer.json',
            'package' => 'package.json',
        ],
        'replacements' => [
            'seeders/PermissionTableSeeder' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'seeders/MarketPlaceSeederTableSeeder' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'http/controllers/company/settingscontroller' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'http/controllers/superadmin/settingscontroller' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/companymenu' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/companysetting' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/companysettingmenu' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/superadminmenu' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/superadminsetting' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'listener/superadminsettingmenu' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'providers/eventserviceprovider' =>  ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api' => ['LOWER_NAME'],
            'json' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/company/settings/index' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/super-admin/settings/index' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/marketplace/index' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index' => ['LOWER_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer' => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
            ],
        ],
        'gitkeep' => false,
    ],
    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path used for save the generated module. This path also will be added
        | automatically to list of scanned folders.
        |
        */

        'modules' => base_path('Modules'),
        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules assets path.
        |
        */

        'assets' => public_path('modules'),
        /*
        |--------------------------------------------------------------------------
        | The migrations path
        |--------------------------------------------------------------------------
        |
        | Where you run 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */

        'migration' => base_path('database/migrations'),
        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator' => [
            'config' => ['path' => 'Config', 'generate' => true],
            'command' => ['path' => 'Console', 'generate' => true],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'factory' => ['path' => 'Database/factories', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'observer' => ['path' => 'Observers', 'generate' => true],
            'routes' => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'filter' => ['path' => 'Http/Middleware', 'generate' => false],
            'request' => ['path' => 'Http/Requests', 'generate' => false],
            'provider' => ['path' => 'Providers', 'generate' => true],
            'lang' => ['path' => 'Resources/lang', 'generate' => true],
            'views' => ['path' => 'Resources/views', 'generate' => true],
            'event' => ['path' => 'Events', 'generate' => false],
            'listener' => ['path' => 'Listeners', 'generate' => true],
            'resource' => ['path' => 'Transformers', 'generate' => false],
            ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Package commands
    |--------------------------------------------------------------------------
    |
    | Here you can define which commands will be visible and used in your
    | application. If for example you don't use some of the commands provided
    | you can simply comment them out.
    |
    */
  'commands' => [
    Commands\Make\CommandMakeCommand::class,
    Commands\Make\ComponentClassMakeCommand::class,
    Commands\Make\ComponentViewMakeCommand::class,
    Commands\Make\ControllerMakeCommand::class,
    Commands\Make\ChannelMakeCommand::class,
    Commands\Actions\DisableCommand::class,
    Commands\Actions\DumpCommand::class,
    Commands\Actions\EnableCommand::class,
    Commands\Make\EventMakeCommand::class,
    Commands\Make\FactoryMakeCommand::class,
    Commands\Make\JobMakeCommand::class,
    Commands\Make\ListenerMakeCommand::class,
    Commands\Make\MailMakeCommand::class,
    Commands\Make\MiddlewareMakeCommand::class,
    Commands\Make\NotificationMakeCommand::class,
    Commands\Make\ObserverMakeCommand::class,
    Commands\Make\PolicyMakeCommand::class,
    Commands\Make\ProviderMakeCommand::class,
    Commands\Actions\InstallCommand::class,
    Commands\LaravelModulesV6Migrator::class,
    Commands\Actions\ListCommand::class,
    Commands\Actions\ModuleDeleteCommand::class,
    Commands\Make\ModuleMakeCommand::class,
    Commands\Database\MigrateCommand::class,
    Commands\Database\MigrateFreshCommand::class,
    Commands\Database\MigrateRefreshCommand::class,
    Commands\Database\MigrateResetCommand::class,
    Commands\Database\MigrateRollbackCommand::class,
    Commands\Database\MigrateStatusCommand::class,
    Commands\Make\MigrationMakeCommand::class,
    Commands\Make\ModelMakeCommand::class,
    Commands\Make\ResourceMakeCommand::class,
    Commands\Make\RequestMakeCommand::class,
    Commands\Make\RuleMakeCommand::class,
    Commands\Make\RouteProviderMakeCommand::class,
    Commands\Publish\PublishCommand::class,
    Commands\Publish\PublishConfigurationCommand::class,
    Commands\Publish\PublishMigrationCommand::class,
    Commands\Publish\PublishTranslationCommand::class,
    Commands\Database\SeedCommand::class,
    Commands\Make\SeedMakeCommand::class,
    Commands\SetupCommand::class,
    Commands\Make\TestMakeCommand::class,
    Commands\Actions\UnUseCommand::class,
    Commands\UpdatePhpunitCoverage::class,
    Commands\Actions\UseCommand::class,
],

    /*
    |--------------------------------------------------------------------------
    | Scan Path
    |--------------------------------------------------------------------------
    |
    | Here you define which folder will be scanned. By default will scan vendor
    | directory. This is useful if you host the package in packagist website.
    |
    */

    'scan' => [
        'enabled' => false,
        'paths' => [
            base_path('vendor/*/*'),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Composer File Template
    |--------------------------------------------------------------------------
    |
    | Here is the config for composer.json file, generated by this package
    |
    */

    'composer' => [
        'vendor' => 'workdo',
        'author' => [
            'name' => 'WorkDo',
            'email' => 'support@workdo.io',
        ],
        'composer-output' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled' => false,
        'driver' => 'file',
        'key' => 'laravel-modules',
        'lifetime' => 60,
    ],
    /*
    |--------------------------------------------------------------------------
    | Choose what laravel-modules will register as custom namespaces.
    | Setting one to false will require you to register that part
    | in your own Service Provider class.
    |--------------------------------------------------------------------------
    */
    'register' => [
        'translations' => true,
        /**
         * load files on boot or register method
         *
         * Note: boot not compatible with asgardcms
         *
         * @example boot|register
         */
        'files' => 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, file, database etc. The only
    | required parameter is 'class'.
    | The file activator will store the activation status in storage/installed_modules
    */
    'activators' => [
        'file' => [
            'class' => FileActivator::class,
            'statuses-file' => base_path('storage/modules_statuses.json'),
            'cache-key' => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],

    'activator' => 'file',
];
