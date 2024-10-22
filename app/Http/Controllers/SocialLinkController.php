<?php

namespace App\Http\Controllers;

use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $social_links = SocialLink::all();
        return view('sociallink.index',compact('social_links'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('sociallink.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
              
            ]
        );

        // If validation fails, return an error message
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Create new Employment Status
        $social_link = new SocialLink();
        $social_link->name = $request->name;
        $social_link->created_by = \Auth::user()->creatorId(); // Save the user who created it
        $social_link->save();

        // Redirect back to the index page with success message
        return redirect()->route('social_link.index')->with('success', __('Soical link successfully created.'));
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

        
           $social_link = SocialLink    ::find($id);
           if (!$social_link) {
               return redirect()->route('soical_link.index')->with('error', __('Social link  not found.'));
           }

           return view('sociallink.edit', compact('social_link'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        //
        $validator = \Validator::make(
            $request->all(), 
            [
                'name' => 'required|string|max:255',
            ]
        );

        // Return validation errors if validation fails
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Find the employment status by ID
        $social_link = socialLink::find($id);

        // If employment status not found, return error
        if (!$social_link) {
            return redirect()->back()->with('error', __('Social Links Status not found.'));
        }

        // Update the employment status fields
        $social_link->name = $request->input('name');
        $social_link->save();

        // Redirect back to the employment status index with a success message
        return redirect()->route('social_link.index')->with('success', __('Social link  successfully updated.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $social_link = SocialLink::find($id);
        if($social_link->created_by == \Auth::user()->creatorId())
        {  
           
            $social_link->delete();

            return redirect()->route('social_link.index')->with('success', __('Social Links successfully deleted.'));
        }
    }
}
