<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoadmapController extends Controller
{
    public function getCompleteRoadmap($roadmap_id)
    {
        // Vérifier l'existence de la roadmap
        $roadmap = DB::table('roadmaps')->where('id', $roadmap_id)->first();
        if (!$roadmap) {
            return response()->json(['error' => 'Roadmap not found'], 404);
        }

        // Prérequis liés à la roadmap via skill_id
        $prerequisites = DB::table('prerequistes')
            ->join('skills', 'prerequistes.skill_id', '=', 'skills.id')
            ->join('roadmaps', 'skills.id', '=', 'roadmaps.skill_id')
            ->where('roadmaps.id', $roadmap_id)
            ->select('prerequistes.*')
            ->get();

        // Outils liés à la roadmap via skill name
        $tools = DB::table('tools')
            ->join('skills', 'tools.name', '=', 'skills.name')
            ->join('roadmaps', 'skills.id', '=', 'roadmaps.skill_id')
            ->where('roadmaps.id', $roadmap_id)
            ->select('tools.*')
            ->get();

        // Cours liés à la roadmap via nom partiel
        $courses = DB::table('candidate_courses')
            ->join('skills', function ($join) {
                $join->on(DB::raw('candidate_courses.name'), 'like', DB::raw("CONCAT('%', skills.name, '%')"))
                    ->orOn(DB::raw('skills.name'), 'like', DB::raw("CONCAT('%', candidate_courses.name, '%')"));
            })
            ->join('roadmaps', 'skills.id', '=', 'roadmaps.skill_id')
            ->where('roadmaps.id', $roadmap_id)
            ->select('candidate_courses.*')
            ->get();

        // Compétences liées à la roadmap via contenu textuel
        $roadmapSkills = DB::table('roadmap_skills')
            ->join('skills', function ($join) {
                $join->on(DB::raw('roadmap_skills.text'), 'like', DB::raw("CONCAT('%', skills.name, '%')"))
                    ->orOn(DB::raw('skills.name'), 'like', DB::raw("CONCAT('%', roadmap_skills.text, '%')"));
            })
            ->join('roadmaps', 'skills.id', '=', 'roadmaps.skill_id')
            ->where('roadmaps.id', $roadmap_id)
            ->select('roadmap_skills.*')
            ->get();

        // Retour du tout en une seule réponse JSON
        return response()->json([
            'roadmap' => $roadmap,
            'prerequisites' => $prerequisites,
            'tools' => $tools,
            'candidate_courses' => $courses,
            'roadmap_skills' => $roadmapSkills,
        ]);
    
    }
 
    public function generateRoadmap(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'skill_id' => 'required|integer|exists:skills,id',
            'completed' => 'required|string',
            "company_id" => 'required |integer|exists:companies,id',
            'candidate_id' => 'required|integer|exists:candidates,id'
        ]);

        // Check for existing roadmap with the same candidate_id and skill_id
        $existingRoadmap = Roadmap::where('candidate_id', $validated['candidate_id'])
            ->where('skill_id', $validated['skill_id'])
            ->first();

        if ($existingRoadmap) {
            return response()->json([
                'message' => 'Existing roadmap found for this candidate and skill',
                'data' => $existingRoadmap
            ], 200); // 200 OK for existing resource
        }

        // Create new roadmap
        $roadmap = Roadmap::create([
            'name' => $validated['name'],
            'skill_id' => $validated['skill_id'],
            'completed' => $validated['completed'],
            'company_id'=>$validated['company_id'],
            'candidate_id' => $validated['candidate_id']
        ]);

        return response()->json([
            'message' => 'Roadmap created successfully',
            'data' => $roadmap
        ], 201);
    }
    public function details($id)
    {
        try {
            // Fetch the roadmap by ID
            $roadmap = Roadmap::find($id);
    
            // Check if roadmap exists
            if (!$roadmap) {
                return response()->json([
                    'message' => 'Roadmap not found'
                    
                ], 404);
            }
            
            // Get the skill associated with this roadmap
            $skill = DB::table('skills')
                ->where('id', $roadmap->skill_id)
                ->first();
                
            // Get progress information
            $progress = DB::table('roadmapsprogress')
                ->where('roadmap_id', $id)
                ->first();
                
            // Get company information
            $company = null;
            if ($roadmap->company_id) {
                $company = DB::table('companies')
                    ->where('id', $roadmap->company_id)
                    ->select('id', 'name', 'sector')
                    ->first();
            }
            
            // Get candidate information
            $candidate = DB::table('candidates')
                ->where('id', $roadmap->candidate_id)
                ->select('id', 'name', 'email')
                ->first();
                
            // Get related badges
            $badges = DB::table('badges')
                ->join('qcm_for_roadmaps', 'badges.qcm_for_roadmap_id', '=', 'qcm_for_roadmaps.id')
                ->where('qcm_for_roadmaps.roadmap_id', $id)
                ->select('badges.*')
                ->get();
    
            // Return the enhanced roadmap details
            return response()->json([
                'id' => $roadmap->id,
                'name' => $roadmap->name,
                'completed' => $roadmap->completed,
                'created_at' => $roadmap->created_at,
                'updated_at' => $roadmap->updated_at,
                'skill' => $skill,
                'progress' => $progress ? $progress->progress : 0,
                'company' => $company,
                'candidate' => $candidate,
                'badges' => $badges
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching roadmap details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getSelectedCompanyForCandidate($roadmap_id)
    {
       // Validate query parameters
    $candidate_id = request()->query('candidate_id');
    
    if (!$candidate_id || !is_numeric($candidate_id)) {
        return response()->json(['message' => 'Invalid or missing candidate_id'], 400);
    }

    if (!is_numeric($roadmap_id)) {
        return response()->json(['message' => 'Invalid roadmap_id'], 400);
    }

    // Fetch data with explicit columns
    $result = DB::table('companies_selecteds')
        ->join('roadmaps', 'companies_selecteds.company_id', '=', 'roadmaps.company_id')
        ->join('companies', 'companies_selecteds.company_id', '=', 'companies.id')
        ->where('companies_selecteds.candidate_id', $candidate_id)
        ->where('roadmaps.id', $roadmap_id)
        ->select([
            'companies.id as company_id',
            'companies.name as company_name',
            'roadmaps.id as roadmap_id',
            'roadmaps.completed as roadmap_completed',
            'companies_selecteds.candidate_id as candidate_id'
        ])
        ->first();

    if (!$result) {
        return response()->json(['message' => 'No data found for the given candidate and roadmap'], 404);
    }

    // Structure the response to match frontend expectations
    $response = [
        'company' => [
            'id' => $result->company_id,
            'name' => $result->company_name ?? 'Unknown Company',
     
        ],
        'candidate' => [
            'id' => $result->candidate_id
        ],
        'roadmap' => [
            'id' => $result->roadmap_id,
            'completed' => $result->roadmap_completed
        ]
    ];

    return response()->json($response);
    }
    
    public function saveRoadmapProgress(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'roadmap_id' => 'required|integer|exists:roadmaps,id',
            'progress' => 'required|integer|min:0|max:100',
            'steps' => 'nullable|string', // Added validation for steps JSON string
            'candidate_id' => 'required|integer|exists:candidates,id' // Added validation for candidate_id
        ]);

        // Find or create roadmap progress record
        $roadmapProgress = DB::table('roadmapsprogress')
            ->where('roadmap_id', $validated['roadmap_id'])
            ->first();

        if ($roadmapProgress) {
            // Update existing record
            DB::table('roadmapsprogress')
                ->where('roadmap_id', $validated['roadmap_id'])
                ->update([
                    'progress' => $validated['progress'],
                    'steps' => $validated['steps'] ?? null, // Save steps data
                    'updated_at' => now()
                ]);
        } else {
            // Create new record
            DB::table('roadmapsprogress')->insert([
                'roadmap_id' => $validated['roadmap_id'],
                'progress' => $validated['progress'],
                'steps' => $validated['steps'] ?? null, // Save steps data
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // If progress is 100%, update the roadmap as completed
        if ($validated['progress'] >= 100) {
            DB::table('roadmaps')
                ->where('id', $validated['roadmap_id'])
                ->update(['completed' => 'completed']);
        }

        return response()->json([
            'message' => 'Roadmap progress saved successfully',
            'data' => [
                'roadmap_id' => $validated['roadmap_id'],
                'progress' => $validated['progress'],
                'steps' => $validated['steps'] ?? null
            ]
        ], 200);
    }
    
    public function getRoadmapProgress($roadmap_id, $candidate_id = null)
    {
        if (!is_numeric($roadmap_id)) {
            return response()->json(['message' => 'Invalid roadmap_id'], 400);
        }

        if ($candidate_id && !is_numeric($candidate_id)) {
            return response()->json(['message' => 'Invalid candidate_id'], 400);
        }

        // Fetch progress data
        $progress = DB::table('roadmapsprogress')
            ->where('roadmap_id', $roadmap_id)
            ->first();

        if (!$progress) {
            return response()->json([
                'message' => 'No progress data found for this roadmap',
                'data' => [
                    'roadmap_id' => (int)$roadmap_id,
                    'progress' => 0,
                    'steps' => null
                ]
            ], 200);
        }

        return response()->json([
            'message' => 'Roadmap progress retrieved successfully',
            'data' => [
                'roadmap_id' => (int)$roadmap_id,
                'progress' => (int)$progress->progress,
                'steps' => $progress->steps
            ]
        ], 200);
    }

    public function getCompleted($roadmapId){
        $completed= Roadmap::where('id' ,$roadmapId )->first();
        return response()->json($completed);
    }
}