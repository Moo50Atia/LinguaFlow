<?php

namespace App\Actions\Community;

use App\Models\User;

class DiscoverLanguagePartnersAction
{
    /**
     * Compute language partner match percentage and filter eligible users
     */
    public function execute(User $user, array $filters = [])
    {
        // Get user's learning languages (e.g. English, French)
        $learningLanguages = $user->learningLanguages()->pluck('language')->toArray();
        $nativeLanguage = $user->native_language;

        // Base Query: find someone whose native language is one we are learning, 
        // OR who is learning our native language.
        $query = User::where('id', '!=', $user->id)
            ->where('role', 'student') // Only students for language partners
            ->with(['learningLanguages', 'interests'])
            ->where(function ($q) use ($learningLanguages, $nativeLanguage) {
                // They natively speak what I am learning
                $q->whereIn('native_language', $learningLanguages)
                  // Or they are learning what I natively speak
                  ->orWhereHas('learningLanguages', function ($sub) use ($nativeLanguage) {
                      $sub->where('language', $nativeLanguage);
                  });
            });

        // Apply visual filters from the frontend UI
        if (!empty($filters['level'])) {
            $query->where('cefr_level', $filters['level']);
        }
        
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        
        if (!empty($filters['online'])) {
            $query->where('is_online', filter_var($filters['online'], FILTER_VALIDATE_BOOLEAN));
        }

        $potentialPartners = $query->paginate(15);

        // Compute dynamic match score
        $potentialPartners->getCollection()->transform(function ($partner) use ($user) {
            $score = 50; // Base score for crossing the basic language bridge filter

            // Add points for shared interests
            $myInterests = $user->interests->pluck('interest')->toArray();
            $theirInterests = $partner->interests->pluck('interest')->toArray();
            $intersection = array_intersect($myInterests, $theirInterests);
            
            $score += (count($intersection) * 10); // +10% per shared interest

            // Max out at 99% 
            $partner->match_percentage = min(99, $score);
            return $partner;
        });

        // Sort collection loosely by score (in a real app, this might be a raw SQL query or Elasticsearch)
        $sortedCollection = $potentialPartners->getCollection()->sortByDesc('match_percentage')->values();
        $potentialPartners->setCollection($sortedCollection);

        return $potentialPartners;
    }
}
