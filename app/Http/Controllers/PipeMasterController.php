<?php

namespace App\Http\Controllers;

use App\Models\PipeCategory;
use App\Models\PipeSize;
use App\Models\PipeType;
use App\Models\PipeClass;
use Illuminate\Http\Request;

class PipeMasterController extends Controller
{
    public function index()
    {
        $categories = PipeCategory::orderBy('name')->get();
        $sizes      = PipeSize::orderBy('id')->get();
        $types      = PipeType::orderBy('code')->get();   // Grade
        $classes    = PipeClass::orderBy('code')->get();

        return view('master.index', compact('categories', 'sizes', 'types', 'classes'));
    }

    // ── UKURAN PIPA ──────────────────────────────────────────────
    public function storeSize(Request $request)
    {
        $request->validate([
            'size_label'     => 'required|string|max:20|unique:pipe_sizes,size_label',
            'pcs_per_bundle' => 'required|integer|min:1',
        ]);

        PipeSize::create($request->only('size_label', 'pcs_per_bundle'));

        return back()->with('success_tab', 'size')
                     ->with('success', "Ukuran {$request->size_label} berhasil ditambahkan.");
    }

    public function destroySize($id)
    {
        $item = PipeSize::findOrFail($id);
        $label = $item->size_label;
        $item->delete();

        return back()->with('success_tab', 'size')
                     ->with('success', "Ukuran {$label} berhasil dihapus.");
    }

    // ── GRADE ─────────────────────────────────────────────────────
    public function storeGrade(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:pipe_types,code',
            'name' => 'required|string|max:50',
        ]);

        PipeType::create($request->only('code', 'name'));

        return back()->with('success_tab', 'grade')
                     ->with('success', "Grade {$request->code} berhasil ditambahkan.");
    }

    public function destroyGrade($id)
    {
        $item = PipeType::findOrFail($id);
        $code = $item->code;
        $item->delete();

        return back()->with('success_tab', 'grade')
                     ->with('success', "Grade {$code} berhasil dihapus.");
    }

    // ── CLASS ─────────────────────────────────────────────────────
    public function storeClass(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:pipe_classes,code',
            'name' => 'required|string|max:50',
        ]);

        PipeClass::create($request->only('code', 'name'));

        return back()->with('success_tab', 'class')
                     ->with('success', "Class {$request->name} berhasil ditambahkan.");
    }

    public function destroyClass($id)
    {
        $item = PipeClass::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return back()->with('success_tab', 'class')
                     ->with('success', "Class {$name} berhasil dihapus.");
    }

    // ── KATEGORI ──────────────────────────────────────────────────
    public function storeCategory(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:pipe_categories,code',
            'name' => 'required|string|max:100',
        ]);

        PipeCategory::create($request->only('code', 'name'));

        return back()->with('success_tab', 'category')
                     ->with('success', "Kategori {$request->name} berhasil ditambahkan.");
    }

    public function destroyCategory($id)
    {
        $item = PipeCategory::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return back()->with('success_tab', 'category')
                     ->with('success', "Kategori {$name} berhasil dihapus.");
    }


}
