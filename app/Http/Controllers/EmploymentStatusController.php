<?php

namespace App\Http\Controllers;
use App\Models\EmploymentStatus;
use Illuminate\Http\Request;

class EmploymentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $statuses = EmploymentStatus::where('created_by', \Auth::user()->id);
        return view('employmentstatus.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('employmentstatus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if the user has permission to create employment status
        // if (\Auth::user()->can('create employment status')) {
    
            // Validate the form input
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'color_value' => 'required',
                ]
            );
    
            // If validation fails, return an error message
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
    
            // Create new Employment Status
            $employmentStatus = new EmploymentStatus();
            $employmentStatus->name = $request->name;
            $employmentStatus->color_value = $request->color_value;
            $employmentStatus->description = $request->description;
            $employmentStatus->created_by = \Auth::user()->creatorId(); // Save the user who created it
            $employmentStatus->save();
    
            // Redirect back to the index page with success message
            return redirect()->route('employment_status.index')->with('success', __('Employment Status successfully created.'));
        // } else {
        //     // If the user doesn't have permission, return an error
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
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
    public function edit($id)
    {
        //
         // Check if the user has permission to edit employment status
    // if (\Auth::user()->can('edit employment status')) {

        // Find the employment status by ID
        $employmentStatus = EmploymentStatus::find($id);

        // If no employment status is found, return an error
        if (!$employmentStatus) {
            return redirect()->route('employment_status.index')->with('error', __('Employment Status not found.'));
        }

        // Return the edit view with the found employment status
        return view('employmentstatus.edit', compact('employmentStatus'));
    // } else {
    //     // Return a permission error if the user doesn't have the required permissions
    //     return redirect()->back()->with('error', __('Permission denied.'));
    // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Check if the user has permission to update employment status
        // if (\Auth::user()->can('edit employment status')) {
    
            // Validate input fields
            $validator = \Validator::make(
                $request->all(), 
                [
                    'name' => 'required|string|max:255',
                    'color_value' => 'required|string',
                    'description' => 'nullable|string'
                ]
            );
    
            // Return validation errors if validation fails
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
    
            // Find the employment status by ID
            $employmentStatus = EmploymentStatus::find($id);
    
            // If employment status not found, return error
            if (!$employmentStatus) {
                return redirect()->back()->with('error', __('Employment Status not found.'));
            }
    
            // Update the employment status fields
            $employmentStatus->name = $request->input('name');
            $employmentStatus->color_value = $request->input('color_value');
            $employmentStatus->description = $request->input('description');
            $employmentStatus->save();
    
            // Redirect back to the employment status index with a success message
            return redirect()->route('employment_status.index')->with('success', __('Employment Status successfully updated.'));
        // } else {
        //     // Return permission error if the user is not allowed to update
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
         // if(\Auth::user()->can('delete assets management'))
    // {
        $employmentStatus = EmploymentStatus::find($id);
        if($employmentStatus->created_by == \Auth::user()->creatorId())
        {  
           
            $employmentStatus->delete();

            return redirect()->route('employment_status.index')->with('success', __('employmentStatus successfully deleted.'));
        }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }

    }
}
