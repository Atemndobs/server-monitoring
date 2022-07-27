<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class UpdatePerms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissions = Permission::all();
        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            $permission->name = $permission->key;
            $permission->guard_name = $permission->key;
            $permission->save();
        }

        dd([
            'permissions' => $permissions->toArray(),
        ]);
        return 0;
    }
}
