<?php
/**
 * SessionManager - Handles user session management and grading
 */

class SessionManager {
    private $sessionsDir;
    private $qm;

    public function __construct() {
        $this->sessionsDir = __DIR__ . '/../../data/sessions';
        $this->qm = new QuestionManager();
        
        // Create sessions directory if it doesn't exist
        if (!is_dir($this->sessionsDir)) {
            mkdir($this->sessionsDir, 0755, true);
        }
    }

    public function createSession($questionCount, $domains = null) {
        $sessionId = uniqid('session_', true);
        
        $questions = $this->qm->getRandomQuestions($questionCount, $domains);
        
        $session = [
            'id' => $sessionId,
            'created_at' => date('Y-m-d H:i:s'),
            'question_count' => $questionCount,
            'domains' => $domains,
            'questions' => $questions,
            'answers' => [],
            'completed' => false
        ];

        $this->saveSession($sessionId, $session);
        
        return $session;
    }

    public function getSession($sessionId) {
        $filePath = $this->getSessionFilePath($sessionId);
        
        if (!file_exists($filePath)) {
            throw new Exception('Session not found');
        }

        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }

    public function submitAnswers($sessionId, $answers) {
        $session = $this->getSession($sessionId);
        $session['answers'] = $answers;
        $session['completed'] = true;
        $session['completed_at'] = date('Y-m-d H:i:s');

        $this->saveSession($sessionId, $session);

        return $this->gradeAnswers($session);
    }

    private function gradeAnswers($session) {
        $results = [
            'session_id' => $session['id'],
            'total_questions' => count($session['questions']),
            'correct_answers' => 0,
            'domain_scores' => [],
            'questions_review' => []
        ];

        // Initialize domain scores
        $domains = $this->qm->getDomains();
        foreach ($domains as $domain) {
            $results['domain_scores'][$domain['id']] = [
                'name' => $domain['name'],
                'correct' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        // Grade each question
        foreach ($session['questions'] as $index => $question) {
            $userAnswer = $session['answers'][$index] ?? null;
            $correctAnswer = $this->getCorrectAnswer($question);
            $isCorrect = $userAnswer === $correctAnswer;

            if ($isCorrect) {
                $results['correct_answers']++;
                $results['domain_scores'][$question['domain']]['correct']++;
            }

            $results['domain_scores'][$question['domain']]['total']++;

            $results['questions_review'][] = [
                'question_id' => $question['id'],
                'question_text' => $question['question'],
                'domain' => $question['domain'],
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect,
                'explanation' => $question['explanation'],
                'references' => $question['references'] ?? []
            ];
        }

        // Calculate percentages
        foreach ($results['domain_scores'] as $domainId => &$score) {
            if ($score['total'] > 0) {
                $score['percentage'] = round(($score['correct'] / $score['total']) * 100);
            }
        }

        // Overall percentage
        $results['percentage'] = round(($results['correct_answers'] / $results['total_questions']) * 100);

        return $results;
    }

    private function getCorrectAnswer($question) {
        foreach ($question['alternatives'] as $index => $alternative) {
            if ($alternative['isCorrect']) {
                return $index;
            }
        }
        return null;
    }

    private function saveSession($sessionId, $session) {
        $filePath = $this->getSessionFilePath($sessionId);
        $json = json_encode($session, JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
    }

    private function getSessionFilePath($sessionId) {
        return $this->sessionsDir . '/' . str_replace('/', '_', $sessionId) . '.json';
    }
}
