<?php
/**
 * QuestionManager - Handles question data management
 */

class QuestionManager {
    private $questionsFile;
    private $domains = [];
    private $questions = [];

    public function __construct() {
        $this->questionsFile = __DIR__ . '/../../data/questions.json';
        $this->loadQuestions();
    }

    private function loadQuestions() {
        if (!file_exists($this->questionsFile)) {
            throw new Exception('Questions file not found');
        }

        $json = file_get_contents($this->questionsFile);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in questions file: ' . json_last_error_msg());
        }

        $this->domains = $data['domains'] ?? [];
        $this->questions = $data['questions'] ?? [];
    }

    public function getDomains() {
        return $this->domains;
    }

    public function getAllQuestions() {
        return $this->questions;
    }

    public function getQuestion($id) {
        foreach ($this->questions as $question) {
            if ($question['id'] === $id) {
                return $question;
            }
        }
        return null;
    }

    public function getRandomQuestions($count = 10, $domains = null) {
        $available = $this->questions;

        // Filter by domains if specified
        if (!empty($domains)) {
            $available = array_filter($available, function($q) use ($domains) {
                return in_array($q['domain'], $domains);
            });
            $available = array_values($available); // Re-index array
        } else {
            // If no domains specified, maintain domain distribution percentages
            $domainDistribution = [];
            foreach ($this->domains as $domain) {
                $percentage = $domain['percentage'] / 100;
                $domainDistribution[$domain['id']] = (int)ceil($count * $percentage);
            }

            // Adjust for rounding errors
            $totalAllocated = array_sum($domainDistribution);
            if ($totalAllocated !== $count) {
                $diff = $count - $totalAllocated;
                // Add/remove from largest allocation
                $largestDomain = array_key_first($domainDistribution);
                $domainDistribution[$largestDomain] += $diff;
            }

            $selected = [];
            foreach ($domainDistribution as $domainId => $neededCount) {
                $domainQuestions = array_filter($available, function($q) use ($domainId) {
                    return $q['domain'] === $domainId;
                });
                $domainQuestions = array_values($domainQuestions);
                shuffle($domainQuestions);
                $selected = array_merge($selected, array_slice($domainQuestions, 0, min($neededCount, count($domainQuestions))));
            }

            shuffle($selected);
            return $selected;
        }

        // Shuffle and get random questions
        shuffle($available);
        return array_slice($available, 0, min($count, count($available)));
    }

    public function getQuestionForGrading($id) {
        return $this->getQuestion($id);
    }
}
