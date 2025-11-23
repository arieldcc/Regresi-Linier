<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Role;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::with('roles')->get();
        return view('pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('pages.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'roles' => 'required|array',
        ]);

        $page = Page::create($request->only('title', 'content'));
        $page->roles()->sync($request->roles);

        return redirect()->route('halaman.index')->with('success', 'Halaman berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $halaman)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $halaman)
    {
        $roles = Role::all();
        $selectedRoles = $halaman->roles->pluck('id')->toArray();
        return view('pages.edit', compact('halaman', 'roles', 'selectedRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $halaman)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'roles' => 'required|array',
        ]);

        $halaman->update($request->only('title', 'content'));
        $halaman->roles()->sync($request->roles);

        return redirect()->route('halaman.index')->with('success', 'Halaman berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $halaman)
    {
        $halaman->delete();
        return redirect()->route('halaman.index')->with('success', 'Halaman berhasil dihapus');
    }
}
