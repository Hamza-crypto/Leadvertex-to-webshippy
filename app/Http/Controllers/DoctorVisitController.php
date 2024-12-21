<?php

namespace App\Http\Controllers;

use App\Models\DoctorVisit;
use Illuminate\Http\Request;

class DoctorVisitController extends Controller
{

    public function index()
    {
        $doctorVisits = DoctorVisit::all();
        return view('pages/visits/index', compact('doctorVisits'));
    }

    public function create()
    {
        return view('pages/visits/add');
    }

    public function store(Request $request)
    {
        DoctorVisit::create($request->all());

        return redirect()->route('doctor-visits.create')->with('success', 'Doctor Visit added successfully');
    }

    public function edit(DoctorVisit $doctorVisit)
    {
        return view('pages/visits/edit', compact('doctorVisit'));
    }

    public function update(Request $request, DoctorVisit $doctorVisit)
    {
        $doctorVisit->update($request->all());

        return redirect()->route('doctor-visits.index')->with('success', 'Doctor visit updated successfully.');
    }


    public function destroy($id)
    {
        $visit = DoctorVisit::findOrFail($id);
        $visit->delete();
        return redirect()->route('doctor-visits.index')->with('success', 'Visit deleted successfully!');
    }
}
