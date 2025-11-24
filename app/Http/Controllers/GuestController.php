<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    /**
     * Halaman dashboard publik (guest).
     * Jika user login, role mengikuti user.
     * Jika tidak login, dianggap role guest.
     */
    public function dashboard()
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
        } else {
            $role = Role::where('name', 'guest')->first();
        }

        // fallback kalau role guest belum ada
        $roleId = $role?->id;
        $roleName = $role?->name ?? 'guest';

        $pages = [];
        if ($roleId) {
            $pages = Page::whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            })->get();
        }

        return view('welcome', compact('pages', 'roleName'));
    }
}
