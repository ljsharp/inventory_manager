<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RoleIndexRequest;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoleIndexRequest $request)
    {
        $roles = Role::query();
        if ($request->has('search')) {
            $roles->where('name', 'LIKE', "%" . $request->search . "%");
            $roles->orWhere('guard_name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $roles->orderBy($request->field, $request->order);
        }
        $roles->with('permissions');
        $role = auth()->user()->roles->pluck('name')[0];
        $permissions = Permission::latest();
        if ($role != 'admin') {
            $permissions = Permission::whereNotIn('name', ['create permission', 'read permission', 'update permission', 'delete permission'])->latest();
            $roles->where('name', '<>', 'admin');
        }
        return Inertia::render('admin/Role/Index', [
            'title'         => "Role",
            'filters'       => $request->all(['search', 'field', 'order']),
            'roles'         => $roles->paginate(10),
            'permissions'   => $permissions->get(),
        ]);
    }

    public function store(RoleStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name'          => $request->name,
            ]);
            if (!empty($request->permissions)) {
                $role->givePermissionTo(collect($request->permissions)
                    ->map(fn($permission) => intval($permission))
                    ->values()->toArray());
            }
            DB::commit();
            return back()->with('success', $role->name . ' created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error creating ' .  $th->getMessage());
        }
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->update([
                'name'          => $request->name,
            ]);
            if (!empty($request->permissions)) {
                $role->syncPermissions(collect($request->permissions)
                    ->map(fn($permission) => intval($permission))
                    ->values()->toArray());
            }
            DB::commit();
            return back()->with('success', $role->name . ' updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', 'Error updating ' .  $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return back()->with('success', $role->name . ' deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Error deleting ' . $role->name . $th->getMessage());
        }
    }
}
