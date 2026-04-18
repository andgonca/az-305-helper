<?php
/**
 * AZ-305 Helper API
 * Main API endpoint handler
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/QuestionManager.php';
require_once __DIR__ . '/../src/SessionManager.php';

// Set error reporting to show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log all errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../data/php-errors.log');

// Parse the request
$request_method = $_SERVER['REQUEST_METHOD'];
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($request_path, '/'));

// Remove base path if present (api)
if (count($path_parts) > 0 && $path_parts[0] === 'api') {
    array_shift($path_parts);
}

// Remove index.php if present (direct access)
if (count($path_parts) > 0 && $path_parts[0] === 'index.php') {
    array_shift($path_parts);
}

$endpoint = $path_parts[0] ?? null;
$action = $path_parts[1] ?? null;
$param = $path_parts[2] ?? null;

// Debug endpoint - show what we're parsing
if ($endpoint === 'debug') {
    echo json_encode([
        'request_uri' => $_SERVER['REQUEST_URI'],
        'request_path' => $request_path,
        'path_parts' => $path_parts,
        'endpoint' => $endpoint,
        'action' => $action,
        'param' => $param,
        'cwd' => getcwd(),
        'script_dir' => __DIR__,
        'questions_file' => __DIR__ . '/../data/questions.json',
        'questions_file_exists' => file_exists(__DIR__ . '/../data/questions.json'),
        'src_dir' => __DIR__ . '/../src',
        'src_exists' => is_dir(__DIR__ . '/../src')
    ]);
    exit;
}

try {
    // Route the request
    switch ($endpoint) {
        case 'domains':
            handleDomainsRequest($request_method);
            break;
        
        case 'questions':
            handleQuestionsRequest($request_method, $action, $param);
            break;
        
        case 'session':
            handleSessionRequest($request_method, $action, $param);
            break;
        
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('API Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleDomainsRequest($method) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    try {
        $qm = new QuestionManager();
        $domains = $qm->getDomains();
        
        if ($domains === null) {
            http_response_code(500);
            echo json_encode(['error' => 'getDomains() returned null']);
            return;
        }
        
        if (empty($domains)) {
            http_response_code(500);
            echo json_encode(['error' => 'No domains found in questions data']);
            return;
        }
        
        // Ensure we're returning an array
        if (!is_array($domains)) {
            http_response_code(500);
            echo json_encode(['error' => 'Domains is not an array: ' . gettype($domains)]);
            return;
        }
        
        echo json_encode($domains);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to load domains: ' . $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    }
}

function handleQuestionsRequest($method, $action, $param) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $qm = new QuestionManager();
    
    if ($action === 'random') {
        // Get random questions
        $count = $_GET['count'] ?? 10;
        $domains = $_GET['domains'] ? explode(',', $_GET['domains']) : null;
        $questions = $qm->getRandomQuestions((int)$count, $domains);
        echo json_encode($questions);
    } elseif ($param) {
        // Get specific question
        $question = $qm->getQuestion((int)$param);
        if ($question) {
            echo json_encode($question);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Question not found']);
        }
    } else {
        // Get all questions
        $questions = $qm->getAllQuestions();
        echo json_encode($questions);
    }
}

function handleSessionRequest($method, $action, $param) {
    try {
        $sm = new SessionManager();
        
        switch ($action) {
            case 'create':
                if ($method !== 'POST') {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
                    return;
                }
                
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    if (!isset($data['question_count'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Missing question_count parameter']);
                        return;
                    }
                    
                    error_log('Creating session with: ' . json_encode($data));
                    
                    $question_count = intval($data['question_count']);
                    $domains = $data['domains'] ?? null;
                    
                    error_log('Calling createSession with count=' . $question_count . ', domains=' . json_encode($domains));
                    
                    // createSession now returns the full session object
                    $session = $sm->createSession($question_count, $domains);
                    
                    error_log('Session created: ' . json_encode($session));
                    
                    if (!$session || !isset($session['id'])) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to create valid session']);
                        return;
                    }
                    
                    echo json_encode($session);
                } catch (Exception $e) {
                    http_response_code(500);
                    error_log('Session creation error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                    echo json_encode([
                        'error' => 'Session creation error: ' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
        
        case 'get':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $session = $sm->getSession($param);
            if ($session) {
                echo json_encode($session);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Session not found']);
            }
            break;
        
        case 'submit':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $sm->submitAnswers(
                $param,
                $data['answers'] ?? []
            );
            echo json_encode($result);
            break;
        
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Session error: ' . $e->getMessage()]);
    }
}
?>
