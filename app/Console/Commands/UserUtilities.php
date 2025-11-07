<?php

/**
 * UserUtilities.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\User\PermissionModel as Permission;
use App\User\RoleModel as Role;
use App\User\UserModel as User;
use Exception;
use Illuminate\Console\Command;

/**
 * Tools to perform various user utilities via the command line
 */
class UserUtilities extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:user
                            {--list : List Users}
                            {--create : Create user}
                            {--name= : User name}
                            {--email= : User email}
                            {--pw= : User password}
                            {--list-r : List roles}
                            {--create-r : Create role}
                            {--list-p : List permissions}
                            {--create-p : Create permission}
                            {--name= : Name}
                            {--slug= : Slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform user utilities, create, add roles etc';

    /**
     * Command options.
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $this->options = $this->options();

        if ($this->options['list']):
            $users = User::all()->toArray();
            foreach($users as $key => $user):
                $roles = User::find($user['id'])->roles->toArray();
                $users[$key]['roles'] = array_column($roles, 'name');
                $permissions = User::find($user['id'])->permissions->toArray();
                $users[$key]['permissions'] = array_column($permissions, 'name');
            endforeach;
            $this->info(json_encode($users));
        endif;

        if ($this->options['create']):
            $this->createUser();
        endif;

        if ($this->options['list-r']):
            $this->info(json_encode(Role::all()->toArray()));
        endif;

        if ($this->options['create-r']):
            $this->createRole();
        endif;

        if ($this->options['list-p']):
            $this->info(json_encode(Permission::all()->toArray()));
        endif;

        if ($this->options['create-p']):
            $this->createPermission();
        endif;


    }

    /**
     * Create a user.
     *
     * @return void
     */
    public function createUser()
    {
        try {

            if(isset($this->options['name'])):
                $name = $this->options['name'];
            else:
                $name = $this->ask("Please supply user's name");
            endif;

            if(isset($this->options['email'])):
                $email = $this->options['email'];
            else:
                $email = $this->ask("Please supply user's email");
            endif;

            if(isset($this->options['pw'])):
                $pw = $this->options['pw'];
            else:
                $pw = $this->ask("Please supply user's password");
            endif;

            // Validate user.
            if(empty($name)):
                $this->error('User name is required');
                exit;
            else:
                $exists = User::where('name', $name)->first();
                if($exists):
                    $this->error('User already exists');
                    exit;
                endif;
            endif;

            // Validate email.
            if(empty($email)):
                $this->error('Email is required');
                exit;
            else:
                $exists = User::where('email', $email)->first();
                if($exists):
                    $this->error('Email is already being used');
                    exit;
                endif;
            endif;

            // Validate password.
            if(empty($pw)):
                $this->error('The password is required');
                exit;
            endif;

            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($pw);
            $user->save();

            $user_role = Role::where('slug', 'user')->first();
            $user->roles()->attach($user_role);

            $this->info('The user has been created successfully.');

        } catch (Exception $e) {
            $this->error("{$e}");
        }
    }

    /**
     * Create a role.
     *
     * @return void
     */
    public function createRole()
    {
        try {

            $name_slug = $this->validateRetrieveNameSlug();

            $role = new Role;
            $role->slug = $name_slug['slug'];
            $role->name = $name_slug['name'];
            $role->save();

            $this->info('The role has been created successfully.');

        } catch (Exception $e) {
            $this->error("{$e}");
        }

    }

    /**
     * Validate name
     *
     * @return array
     */
    public function validateRetrieveNameSlug()
    {
        if(isset($this->options['name'])):
            $name = $this->options['name'];
        else:
            $name = $this->ask("Please supply name");
        endif;

        if(isset($this->options['slug'])):
            $slug = $this->options['slug'];
        else:
            $slug = $this->ask("Please supply slug");
        endif;

        // Validate name.
        if(empty($name)):
            $this->error('Name is required');
            exit;
        endif;

        // Validate slug.
        if(empty($slug)):
            $this->error('Slug is required');
            exit;
        endif;

        return ['name' => $name, 'slug' => $slug];
    }

    /**
     * Create a permission.
     *
     * @return void
     */
    public function createPermission()
    {
        try {

            $name_slug = $this->validateRetrieveNameSlug();

            $permission = new Permission;
            $permission->slug = $name_slug['slug'];
            $permission->name = $name_slug['name'];
            $permission->save();

            $this->info('The permission has been created successfully.');

        } catch (Exception $e) {
            $this->error("{$e}");
        }

    }

    /**
     * Add role to user.
     *
     * @return void
     */
    public function addUserRole()
    {
        try {

            if(isset($this->options['name'])):
                $name = $this->options['name'];
            else:
                $name = $this->ask("Please supply user's name");
            endif;

            if(isset($this->options['email'])):
                $email = $this->options['email'];
            else:
                $email = $this->ask("Please supply user's email");
            endif;

            if(isset($this->options['pw'])):
                $pw = $this->options['pw'];
            else:
                $pw = $this->ask("Please supply user's password");
            endif;

            // Validate user.
            if(empty($name)):
                $this->error('User name is required');
                exit;
            else:
                $exists = User::where('name', $name)->first();
                if($exists):
                    $this->error('User already exists');
                    exit;
                endif;
            endif;

            // Validate email.
            if(empty($email)):
                $this->error('Email is required');
                exit;
            else:
                $exists = User::where('email', $email)->first();
                if($exists):
                    $this->error('Email is already being used');
                    exit;
                endif;
            endif;

            // Validate password.
            if(empty($pw)):
                $this->error('The password is required');
                exit;
            endif;

            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($pw);
            $user->save();

            $user_role = Role::where('slug', 'user')->first();
            $user->roles()->attach($user_role);

            $this->info('The user has been created successfully.');

        } catch (Exception $e) {
            $this->error("{$e}");
        }
    }

}
