<?php

namespace App\Actions\Learning;

use App\Models\Lesson;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use App\Models\StudyDay;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EvaluateQuizAction
{
    public function execute(User $user, array $data): QuizResult
    {
        return DB::transaction(function () use ($user, $data) {
            $lesson = Lesson::with('quizzes')->findOrFail($data['lesson_id']);
            $questions = $lesson->quizzes->keyBy('id');

            $correctCount = 0;
            $totalQuestions = count($data['answers']);

            foreach ($data['answers'] as $answer) {
                $question = $questions->get($answer['question_id']);
                if ($question && (int)$question->correct_answer === (int)$answer['selected_option']) {
                    $correctCount++;
                }
            }

            $score = $totalQuestions > 0 ? (int)(($correctCount / $totalQuestions) * 100) : 0;
            $passed = $score >= 70; // 70% passing threshold

            $result = QuizResult::create([
                'user_id'         => $user->id,
                'quiz_title'      => "Quiz: {$lesson->title}",
                'score'           => $score,
                'total_questions' => $totalQuestions,
                'passed'          => $passed,
                'type'            => 'lesson_quiz',
            ]);

            // Create or update study day for tracking active days heatmap
            StudyDay::firstOrCreate([
                'user_id' => $user->id,
                'date'    => today(),
            ]);

            return $result;
        });
    }
}
