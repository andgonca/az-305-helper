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

// Parse the request
$request_method = $_SERVER['REQUEST_METHOD'];
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($request_path, '/'));

// Remove base path if present
if (count($path_parts) > 0 && $path_parts[0] === 'api') {
    array_shift($path_parts);
}

$endpoint = $path_parts[0] ?? null;
$action = $path_parts[1] ?? null;
$param = $path_parts[2] ?? null;

// Debug endpoint
if ($endpoint === 'debug') {
    echo json_encode([
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
                
                $data = json_decode(file_get_contents('php://input'), true);
                $session_id = $sm->createSession(
                    $data['question_count'] ?? 10,
                    $data['domains'] ?? null
                );
                
                // Get the full session object
                $session = $sm->getSession($session_id);
                if (!$session) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to retrieve session after creation']);
                    return;
                }
                echo json_encode($session);
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
