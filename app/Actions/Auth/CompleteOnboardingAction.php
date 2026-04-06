<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\QuizResult;
use Illuminate\Support\Facades\DB;

class CompleteOnboardingAction
{
    /**
     * Execute the onboarding logic involving multiple models.
     * 
     * Why Action: Touches User, UserLanguage, UserInterest in a single transaction.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            
            // 1. Determine CEFR level based on quiz score or user selection
            $cefrLevel = 'A1.1'; // System default
            
            // Usually the frontend provides the level after determining it via placement test
            if (isset($data['learning_languages'][0]['level'])) {
                $cefrLevel = $data['learning_languages'][0]['level'];
            }

            // 2. Update user profile
            $user->update([
                'native_language' => $data['native_language'],
                'cefr_level'      => $cefrLevel,
            ]);

            // 3. Clear existing data to allow re-onboarding safely
            $user->learningLanguages()->delete();
            $user->interests()->delete();

            // 4. Insert learning languages
            $languageData = [];
            foreach ($data['learning_languages'] as $lang) {
                // Hardcoding some flags for common languages, in reality could use a package/helper
                $flag = $this->getFlagForLanguage($lang['name']);
                
                $languageData[] = [
                    'language'  => $lang['name'],
                    'flag'      => $flag,
                    'level'     => $lang['level'],
                    'is_native' => false,
                ];
            }
            $user->learningLanguages()->createMany($languageData);

            // 5. Insert interests
            $interestData = array_map(function ($interest) {
                return ['interest' => $interest];
            }, $data['interests']);
            
            $user->interests()->createMany($interestData);

            // 6. Record Initial Placement Quiz Result if they took it
            if (isset($data['quiz_score'])) {
                QuizResult::create([
                    'user_id'         => $user->id,
                    'quiz_title'      => 'Initial Placement Test',
                    'score'           => $data['quiz_score'],
                    'total_questions' => 3, // based on MOCK_QUIZ_QUESTIONS
                    'passed'          => $data['quiz_score'] >= 50,
                    'type'            => 'onboarding',
                ]);
            }

            // Load relationships to return a fresh integrated record
            return $user->fresh(['learningLanguages', 'interests']);
        });
    }

    /**
     * Small helper to assign emojis
     */
    private function getFlagForLanguage(string $language): ?string
    {
        $map = [
            'Spanish' => '🇪🇸',
            'English' => '🇬🇧',
            'French'  => '🇫🇷',
            'German'  => '🇩🇪',
            'Italian' => '🇮🇹',
            'Arabic'  => '🇸🇦',
            'Chinese' => '🇨🇳',
            'Japanese'=> '🇯🇵',
        ];

        return $map[$language] ?? '🌍';
    }
}
