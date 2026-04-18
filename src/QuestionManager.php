<?php
/**
 * QuestionManager - Handles question loading and selection
 */

class QuestionManager {
    private $questions_file;
    private $questions_data;
    
    public function __construct() {
        $this->questions_file = __DIR__ . '/../data/questions.json';
        $this->loadQuestions();
    }
    
    private function loadQuestions() {
        if (!file_exists($this->questions_file)) {
            throw new Exception('Questions file not found');
        }
        
        $json = file_get_contents($this->questions_file);
        $this->questions_data = json_decode($json, true);
        
        if ($this->questions_data === null) {
            throw new Exception('Invalid questions JSON');
        }
    }
    
    public function getDomains() {
        return $this->questions_data['domains'] ?? [];
    }
    
    public function getAllQuestions() {
        return $this->questions_data['questions'] ?? [];
    }
    
    public function getQuestion($id) {
        $questions = $this->questions_data['questions'] ?? [];
        foreach ($questions as $question) {
            if ($question['id'] === $id) {
                return $this->formatQuestionForDisplay($question);
            }
        }
        return null;
    }
    
    /**
     * Get random questions respecting domain distribution
     * 
     * @param int $count Total number of questions
     * @param array $domains Filter by specific domains (optional)
     * @return array Array of questions
     */
    public function getRandomQuestions($count, $domains = null) {
        $all_questions = $this->questions_data['questions'] ?? [];
        $available_domains = $this->questions_data['domains'] ?? [];
        
        // Filter questions by domain if specified
        if ($domains && is_array($domains) && count($domains) > 0) {
            $all_questions = array_filter($all_questions, function($q) use ($domains) {
                return in_array($q['domain'], $domains);
            });
        }
        
        if (empty($all_questions)) {
            throw new Exception('No questions available for selected domains');
        }
        
        // Build domain distribution map - only include domains that have questions
        $domain_dist = [];
        $selected_domain_ids = $domains && is_array($domains) ? $domains : null;
        
        foreach ($available_domains as $domain) {
            // Skip domains not selected by user
            if ($selected_domain_ids && !in_array($domain['id'], $selected_domain_ids)) {
                continue;
            }
            // Only include domains that have questions
            $domain_has_questions = count(array_filter($all_questions, function($q) use ($domain) {
                return $q['domain'] === $domain['id'];
            })) > 0;
            
            if ($domain_has_questions) {
                $domain_dist[$domain['id']] = $domain['percentage'];
            }
        }
        
        // If no valid domains with questions, use all available
        if (empty($domain_dist)) {
            foreach ($available_domains as $domain) {
                $domain_dist[$domain['id']] = $domain['percentage'];
            }
        }
        
        // Allocate questions by domain percentage
        $allocated = [];
        $remaining = $count;
        
        foreach ($domain_dist as $domain_id => $percentage) {
            $domain_count = max(1, round($count * ($percentage / 100)));
            $allocated[$domain_id] = min($domain_count, $remaining);
            $remaining -= $allocated[$domain_id];
        }
        
        // If we have remaining questions due to rounding, add to largest domains
        if ($remaining > 0) {
            arsort($domain_dist);
            foreach ($domain_dist as $domain_id => $percentage) {
                if ($remaining <= 0) break;
                if (isset($allocated[$domain_id])) {
                    $allocated[$domain_id]++;
                    $remaining--;
                }
            }
        }
        
        // Select random questions from each domain
        $selected = [];
        foreach ($allocated as $domain_id => $needed) {
            $domain_questions = array_filter($all_questions, function($q) use ($domain_id) {
                return $q['domain'] === $domain_id;
            });
            
            $domain_questions = array_values($domain_questions);
            
            // Skip if no questions in this domain
            if (empty($domain_questions)) {
                continue;
            }
            
            // Randomly select required number of questions (handle case where needed > available)
            $select_count = min($needed, count($domain_questions));
            
            if ($select_count == 1) {
                // array_rand with 1 element returns a single key, not array
                $key = array_rand($domain_questions);
                $selected[] = $this->formatQuestionForDisplay($domain_questions[$key]);
            } else {
                $keys = array_rand($domain_questions, $select_count);
                foreach ($keys as $key) {
                    $selected[] = $this->formatQuestionForDisplay($domain_questions[$key]);
                }
            }
        }
        
        // Shuffle to randomize order
        shuffle($selected);
        
        return $selected;
    }
    
    /**
     * Format question for display (remove correct answer from alternatives)
     */
    private function formatQuestionForDisplay($question) {
        $formatted = [
            'id' => $question['id'],
            'domain' => $question['domain'],
            'question' => $question['question'],
            'alternatives' => []
        ];
        
        // Shuffle and format alternatives without revealing correct answer
        $alternatives = $question['alternatives'];
        shuffle($alternatives);
        
        foreach ($alternatives as $alt) {
            $formatted['alternatives'][] = [
                'text' => $alt['text']
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get the full question with answer for grading
     */
    public function getQuestionForGrading($id) {
        $questions = $this->questions_data['questions'] ?? [];
        foreach ($questions as $question) {
            if ($question['id'] === $id) {
                return $question;
            }
        }
        return null;
    }
}
?>
