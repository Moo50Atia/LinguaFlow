<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizQuestion;
use App\Actions\Learning\EvaluateQuizAction;
use App\Http\Requests\Learning\StoreQuizQuestionRequest;
use App\Http\Requests\Learning\SubmitQuizRequest;
use App\Http\Resources\QuizQuestionResource;
use App\Http\Resources\QuizResultResource;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;

class QuizController extends BaseController
{
    public function __construct(
        protected QuizService $quizService
    ) {}

    public function index(): JsonResponse
    {
        // For instructors fetching all their course quizzes
        $courseId = request('course_id');
        $quizzes = $this->quizService->listForCourse($courseId);

        return $this->sendSuccess([
            'quizzes' => QuizQuestionResource::collection($quizzes)
        ]);
    }

    public function store(StoreQuizQuestionRequest $request): JsonResponse
    {
        // TODO: Authorize using policy
        $quiz = $this->quizService->create($request->validated());

        return $this->sendCreated([
            'quiz' => new QuizQuestionResource($quiz)
        ]);
    }

    public function update(StoreQuizQuestionRequest $request, QuizQuestion $quiz): JsonResponse
    {
        // TODO: Authorize
        $quiz = $this->quizService->update($quiz, $request->validated());

        return $this->sendSuccess([
            'quiz' => new QuizQuestionResource($quiz)
        ]);
    }

    public function destroy(QuizQuestion $quiz): JsonResponse
    {
        // TODO: Authorize
        $this->quizService->delete($quiz);

        return $this->sendDeleted();
    }

    public function submit(SubmitQuizRequest $request, EvaluateQuizAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return $this->sendSuccess([
            'result' => new QuizResultResource($result)
        ], 'Quiz evaluated successfully.');
    }
}
