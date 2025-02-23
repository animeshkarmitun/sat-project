<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SectionService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Models\Section;

class SectionController extends Controller
{
    protected $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * Create a new section for an exam.
     */
    public function createSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|uuid|exists:exams,exam_id',
            'section_name' => 'required|string|max:255',
            'section_order' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'created_by' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $section = $this->sectionService->createSection($request->all());
            Log::info('Section created', ['section_id' => $section->section_id, 'exam_id' => $request->exam_id]);
            return response()->json(['message' => 'Section created successfully', 'section' => $section], 201);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get all sections for a specific exam.
     */
    public function getExamSections($examId)
    {
        $sections = $this->sectionService->getSectionsByExam($examId);
        return response()->json(['sections' => $sections], 200);
    }

    /**
     * Get details of a specific section.
     */
    public function getSectionDetails($sectionId)
    {
        $section = $this->sectionService->getSectionById($sectionId);
        return response()->json(['section' => $section], 200);
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, $sectionId)
    {
        $validator = Validator::make($request->all(), [
            'section_name' => 'sometimes|string|max:255',
            'section_order' => 'sometimes|integer|min:1',
            'time_limit' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $section = $this->sectionService->updateSection($sectionId, $request->all());
            Log::info('Section updated', ['section_id' => $section->section_id]);
            return response()->json(['message' => 'Section updated successfully', 'section' => $section], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Delete a section.
     */
    public function deleteSection($sectionId)
    {
        try {
            $this->sectionService->deleteSection($sectionId);
            Log::info('Section deleted', ['section_id' => $sectionId]);
            return response()->json(['message' => 'Section deleted successfully'], 200);
        } catch (CustomException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
