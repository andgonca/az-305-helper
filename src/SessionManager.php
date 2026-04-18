<?php
/**
 * SessionManager - Handles session creation, management, and grading
 */

class SessionManager {
    private $sessions_dir;
    private $qm;
    
    public function __construct() {
        $this->sessions_dir = __DIR__ . '/../data/sessions';
        $this->qm = new QuestionManager();
        
        // Create sessions directory if it doesn't exist
        if (!is_dir($this->sessions_dir)) {
            mkdir($this->sessions_dir, 0755, true);
        }
    }
    
    /**
     * Create a new session
     */
    public function createSession($question_count = 10, $domains = null) {
        $session_id = $this->generateSessionId();
        
        // Get random questions
        $questions = $this->qm->getRandomQuestions($question_count, $domains);
        
        $session = [
            'id' => $session_id,
            'created_at' => date('Y-m-d H:i:s'),
            'question_count' => count($questions),
            'domains_selected' => $domains,
            'questions' => array_map(function($q) {
                return [
                    'id' => $q['id'],
                    'domain' => $q['domain'],
                    'question' => $q['question'],
                    'alternatives' => $q['alternatives']
                ];
            }, $questions),
            'answers' => [],
            'completed' => false,
            'completed_at' => null
        ];
        
        // Save session
        $this->saveSession($session);
        
        return $session;
    }
    
    /**
     * Get a session
     */
    public function getSession($session_id) {
        $file = $this->getSessionFile($session_id);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $json = file_get_contents($file);
        return json_decode($json, true);
    }
    
    /**
     * Submit answers for a session
     */
    public function submitAnswers($session_id, $answers) {
        $session = $this->getSession($session_id);
        
        if (!$session) {
            throw new Exception('Session not found');
        }
        
        if ($session['completed']) {
            throw new Exception('Session already completed');
        }
        
        // Grade the answers
        $results = $this->gradeAnswers($session, $answers);
        
        // Update session
        $session['answers'] = $answers;
        $session['completed'] = true;
        $session['completed_at'] = date('Y-m-d H:i:s');
        $session['results'] = $results;
        
        $this->saveSession($session);
        
        return $results;
    }
    
    /**
     * Grade the answers
     */
    private function gradeAnswers($session, $answers) {
        $total_questions = count($session['questions']);
        $correct_count = 0;
        $results_by_domain = [];
        $detailed_results = [];
        
        // Initialize domain stats
        $domains = $this->qm->getDomains();
        foreach ($domains as $domain) {
            $results_by_domain[$domain['id']] = [
                'name' => $domain['name'],
                'correct' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }
        
        // Grade each answer
        foreach ($session['questions'] as $index => $question) {
            $question_id = $question['id'];
            $domain = $question['domain'];
            $user_answer = $answers[$index] ?? null;
            
            // Get the full question with correct answer
            $full_question = $this->qm->getQuestionForGrading($question_id);
            
            if (!$full_question) {
                continue;
            }
            
            // Find the correct answer
            $correct_answer = null;
            $correct_index = null;
            foreach ($full_question['alternatives'] as $alt_index => $alt) {
                if ($alt['isCorrect']) {
                    $correct_answer = $alt['text'];
                    $correct_index = $alt_index;
                    break;
                }
            }
            
            // Check if answer is correct
            $is_correct = false;
            $user_answer_text = null;
            
            if ($user_answer !== null && isset($question['alternatives'][$user_answer])) {
                $user_answer_text = $question['alternatives'][$user_answer]['text'];
                // Compare with correct answer
                if ($correct_answer && $user_answer_text === $correct_answer) {
                    $is_correct = true;
                    $correct_count++;
                }
            }
            
            // Update domain stats
            if (isset($results_by_domain[$domain])) {
                $results_by_domain[$domain]['total']++;
                if ($is_correct) {
                    $results_by_domain[$domain]['correct']++;
                }
            }
            
            // Store detailed result
            $detailed_results[] = [
                'question_index' => $index,
                'question_id' => $question_id,
                'domain' => $domain,
                'question' => $question['question'],
                'user_answer' => $user_answer_text,
                'correct_answer' => $correct_answer,
                'is_correct' => $is_correct,
                'explanation' => $full_question['explanation'] ?? '',
                'references' => $full_question['references'] ?? []
            ];
        }
        
        // Calculate percentages
        foreach ($results_by_domain as $domain_id => &$stats) {
            if ($stats['total'] > 0) {
                $stats['percentage'] = round(($stats['correct'] / $stats['total']) * 100, 2);
            }
        }
        
        $overall_percentage = round(($correct_count / $total_questions) * 100, 2);
        
        return [
            'session_id' => $session['id'],
            'completed_at' => date('Y-m-d H:i:s'),
            'overall' => [
                'correct' => $correct_count,
                'total' => $total_questions,
                'percentage' => $overall_percentage,
                'grade' => $this->calculateGrade($overall_percentage)
            ],
            'by_domain' => $results_by_domain,
            'details' => $detailed_results
        ];
    }
    
    /**
     * Calculate letter grade based on percentage
     */
    private function calculateGrade($percentage) {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Save session to file
     */
    private function saveSession($session) {
        $file = $this->getSessionFile($session['id']);
        $json = json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($file, $json);
    }
    
    /**
     * Get session file path
     */
    private function getSessionFile($session_id) {
        return $this->sessions_dir . '/' . $session_id . '.json';
    }
    
    /**
     * Generate unique session ID
     */
    private function generateSessionId() {
        return 'session_' . bin2hex(random_bytes(8)) . '_' . time();
    }
}
?>
