<?php

namespace App\Services;

use App\Models\KnowledgeArticle;

/**
 * কৃষি জ্ঞানভান্ডার search — answers farmer questions from the local Knowledge
 * Base before any Gemini call.
 *
 * Priority: (1) exact question match, (2) keyword-overlap scoring across
 * keywords/title/question, (3) loose "similar" fallback. Portable LIKE-based
 * scoring (no MySQL FULLTEXT) so it runs on SQLite + MySQL; fast for hundreds
 * of rows and easily under 500ms.
 */
class KnowledgeBaseService
{
    /** Bengali/English question words that carry no topical meaning. */
    private const STOPWORDS = [
        'কি', 'কী', 'কেন', 'কোথায়', 'কিভাবে', 'কীভাবে', 'করবো', 'করব', 'করতে', 'হলে', 'হয়',
        'এর', 'ও', 'এবং', 'আমার', 'আমি', 'আপনি', 'জন্য', 'কখন', 'কোন', 'একটি', 'নতুন', 'ভালো',
        'how', 'what', 'why', 'when', 'where', 'the', 'and', 'for', 'is', 'are', 'my', 'a',
    ];

    /**
     * Find the best matching article for a question.
     *
     * @return array{article:KnowledgeArticle, source:string, score:int}|null
     */
    public function search(string $question): ?array
    {
        $q = trim($question);
        if ($q === '') {
            return null;
        }

        // 1) Exact / greeting match — punctuation-insensitive so "কেমন আছেন",
        //    "কেমন আছেন?" and "হ্যালো!" all hit their conversational article.
        $norm = $this->stripPunctuation(mb_strtolower($q));
        $exact = KnowledgeArticle::active()
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(question)), '?', ''), '।', ''), '!', ''), ',', '') = ?", [$norm])
            ->first();
        if ($exact) {
            return ['article' => $exact, 'source' => 'exact', 'score' => 100];
        }

        // 2) Keyword-overlap scoring.
        $tokens = $this->tokenize($q);
        if (empty($tokens)) {
            return null;
        }

        $candidates = KnowledgeArticle::active()
            ->where(function ($w) use ($tokens) {
                foreach ($tokens as $t) {
                    $w->orWhere('keywords', 'like', "%{$t}%")
                        ->orWhere('title', 'like', "%{$t}%")
                        ->orWhere('question', 'like', "%{$t}%");
                }
            })
            ->limit(60)
            ->get();

        $best = null;
        $bestScore = 0;
        $bestMatched = 0;
        foreach ($candidates as $article) {
            $keywordsLower = mb_strtolower((string) $article->keywords);
            $haystack = mb_strtolower($article->keywords . ' ' . $article->title . ' ' . $article->question);
            $score = 0;
            $matched = 0;
            foreach ($tokens as $t) {
                if (mb_strpos($haystack, $t) !== false) {
                    $matched++;
                    // Keyword-field hits weigh more than incidental title/question hits.
                    $score += (mb_strpos($keywordsLower, $t) !== false) ? 2 : 1;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatched = $matched;
                $best = $article;
            }
        }

        // Precision: require a solid score AND that the match covers at least half
        // the query's meaningful words — so common words (ধান, চাষ) alone don't
        // return a wrong answer to a vague/off-topic question.
        $needed = count($tokens) <= 2 ? 1 : 2;
        $coverage = $bestMatched / count($tokens);
        if ($best && $bestScore >= $needed && $coverage >= 0.5) {
            return ['article' => $best, 'source' => 'keyword', 'score' => $bestScore];
        }

        return null;
    }

    public function recordView(KnowledgeArticle $article): void
    {
        $article->increment('views_count');
    }

    /** Strip trailing/embedded sentence punctuation for exact-match normalization. */
    private function stripPunctuation(string $text): string
    {
        return trim(str_replace(['?', '।', '!', ','], '', $text));
    }

    /** Split into meaningful search tokens (length >= 2, no stopwords). */
    private function tokenize(string $text): array
    {
        // Keep \p{M} (combining marks) so Bengali matras/hasanta are preserved.
        $clean = preg_replace('/[^\p{L}\p{N}\p{M}\s]+/u', ' ', $text);
        $words = preg_split('/\s+/u', mb_strtolower(trim($clean)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        foreach ($words as $w) {
            if (mb_strlen($w) >= 2 && ! in_array($w, self::STOPWORDS, true)) {
                $tokens[] = $w;
            }
        }

        return array_values(array_unique($tokens));
    }
}
