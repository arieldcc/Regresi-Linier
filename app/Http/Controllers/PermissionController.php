<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $maxUrut = Permission::max('urut') ?? 0;
        $nextUrut = $maxUrut + 1;

        return view('permissions.create', compact('nextUrut'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'route' => [
                'required',
                'regex:/^[a-z0-9\-\.\/]+$/', // hanya lowercase, angka, dash dan slash
                'not_regex:/[A-Z]/',       // tidak boleh huruf besar
                'not_regex:/[^a-z0-9\-\.\/]/'// tidak boleh simbol lain
            ],
            'name' => 'required|string|max:100|unique:permissions,name',
            'urut' => 'required|numeric|max:100|unique:permissions,urut'
        ]);
        // $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(
            [
                'name' => $request->name,
                'route' => $request->route,
                'urut' => $request->urut
            ]);
        return redirect()->route('permissions.index')->with('status', 'Permission ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'route' => [
                'required',
                'regex:/^[a-z0-9\-\.\/]+$/', // hanya lowercase, angka, dash dan slash
                'not_regex:/[A-Z]/',       // tidak boleh huruf besar
                'not_regex:/[^a-z0-9\-\.\/]/',// tidak boleh simbol lain
                'unique:permissions,route,' . $permission->id
            ],
            'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
            'urut' => 'required|numeric|max:100|unique:permissions,urut,' . $permission->id
        ]);

        // $request->validate(['name' => 'required|unique:permissions,name,' . $permission->id]);
        $permission->update(
            [
                'name' => $request->name,
                'route' => $request->route,
                'urut' => $request->urut
            ]);
        return redirect()->route('permissions.index')->with('status', 'Permission diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')->with('status', 'Permission sedang digunakan oleh role dan tidak bisa dihapus!');
        }
        $permission->delete();
        return redirect()->route('permissions.index')->with('status', 'Permission dihapus.');
    }

    public function editRolePermissions(Role $role)
    {
        $permissions = Permission::all();
        return view('permissions.assign', compact('role', 'permissions'));
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $role->permissions()->sync($request->permissions ?? []);
        return redirect()->route('roles.index')->with('status', 'Akses role diperbarui.');
    }
}
