<?php

namespace Modules\QuizManagement\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
     public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');
        $module = 'QuizManagement';

        $permissions  = [
            'quiz dashboard manage',
            'quiz manage',
            'quizzes manage',
            'quizzes create',
            'quizzes edit',
            'quizzes show',
            'quizzes delete',
            'quizquestions manage',
            'quizquestions create',
            'quizquestions edit',
            'quizquestions delete',
            'quizcategories manage',
            'quizcategories create',
            'quizcategories edit',
            'quizcategories delete',
            'quizparticipants manage',
            'quizparticipants delete',
            'quizresults manage',
            'quizresults delete',
        ];

        $company_role = Role::where('name','company')->first();
        foreach ($permissions as $key => $value)
        {
            $check = Permission::where('name',$value)->where('module',$module)->exists();
            if($check == false)
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => $module,
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($permission);
                }
            }
        }
    }
}
