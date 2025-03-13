<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Requests\PermissionIndexRequest;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PermissionIndexRequest $request)
    {
        $permissions = Permission::query();
        if ($request->has('search')) {
            $permissions->where('name', 'LIKE', "%" . $request->search . "%");
            $permissions->orWhere('guard_name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $permissions->orderBy($request->field, $request->order);
        }
        return Inertia::render('admin/Permission/Index', [
            'title'         => 'Permission',
            'filters'       => $request->all(['search', 'field', 'order']),
            'permissions'   => $permissions->paginate(10),
        ]);
    }

    public function store(PermissionStoreRequest $request)
    {
        info($request->all());
        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'name'          => $request->name
            ]);
            $admin = Role::whereName('admin')->first();
            $admin->givePermissionTo([$request->name]);
            DB::commit();
            return back()->with('success', $permission->name . ' created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error creating ' .  $th->getMessage());
        }
    }

    public function update(PermissionUpdateRequest $request, Permission $permission)
    {
        DB::beginTransaction();
        try {
            $admin = Role::whereName('admin')->first();
            $admin->revokePermissionTo([$permission->name]);
            $permission->update([
                'name'          => $request->name
            ]);
            $admin->givePermissionTo([$permission->name]);
            DB::commit();
            return back()->with('success',  $permission->name . ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating ' .  $th->getMessage());
        }
    }

    public function destroy(Permission $permission)
    {
        DB::beginTransaction();
        try {
            $admin = Role::whereName('admin')->first();
            $admin->revokePermissionTo([$permission->name]);
            $permission->delete();
            DB::commit();
            return back()->with('success', $permission->name . ' deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error deleting ' . $permission->name . $th->getMessage());
        }
    }
}
