<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Tecnology;
use App\Functions\Helper;
use App\Http\Requests\ProjectRequest;
use Illuminate\Support\Facades\Storage;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();
        $projects = Project::paginate(8);
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tecnologies = Tecnology::all();
        return view('admin.projects.create', compact('tecnologies'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        $form_data = $request->all();

        if ($request->hasFile('image')) {
            $image_path = Storage::put('uploads', $form_data['image']);
            $original_name = $request->file('image')->getClientOriginalName();
            $form_data['image'] = $image_path;
            $form_data['image_original_name'] = $original_name;
        }

        $new_project = new Project();
        $new_project->title = $form_data['title'];
        $new_project->description = $form_data['description'];
        $new_project->language = $form_data['language'];
        $new_project->image = $form_data['image'] ?? null;
        $new_project->image_original_name = $form_data['image_original_name'] ?? null;

        // Genera lo slug
        $new_project->slug = Helper::generateSlug($new_project->title, new Project());

        // Salva il progetto
        $new_project->save();

        if(array_key_exists('tecnologies', $form_data)){
            $new_project->tecnologies()->attach($form_data['tecnologies']);
        }

        return redirect()->route('admin.projects.show', $new_project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
{
    $tecnologies = Tecnology::all();
    return view('admin.projects.edit', compact('project', 'tecnologies'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $form_data = $request->all();

        // Genera lo slug solo se il titolo è cambiato
        if ($form_data['title'] !== $project->title) {
            $form_data['slug'] = Helper::generateSlug($form_data['title'], new Project());
        } else {
            $form_data['slug'] = $project->slug;
        }

        // Gestione del caricamento dell'immagine
        if ($request->hasFile('image')) {
            // Elimina l'immagine esistente se presente
            if ($project->image) {
                Storage::delete($project->image);
            }
            // Carica la nuova immagine
            $image_path = Storage::put('uploads', $form_data['image']);
            $original_name = $request->file('image')->getClientOriginalName();
            $form_data['image'] = $image_path;
            $form_data['image_original_name'] = $original_name;
        }

        // Aggiorna il progetto
        $project->update($form_data);

        // Aggiorna le relazioni con le tecnologie
        if (isset($form_data['tecnologies'])) {
            $project->tecnologies()->sync($form_data['tecnologies']);
        } else {
            // Se non vengono selezionate nuove tecnologie, rimuovi tutte le relazioni esistenti
            $project->tecnologies()->detach();
        }

        return redirect()->route('admin.projects.show', $project);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('deleted', 'Il progetto ' . $project->title . ' è stato eliminato correttamente');
    }
}
