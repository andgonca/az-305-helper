<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    echo "Testing QuestionManager...\n";
    require_once 'src/QuestionManager.php';
    echo "QuestionManager loaded OK\n";
    
    echo "Testing SessionManager...\n";
    require_once 'src/SessionManager.php';
    echo "SessionManager loaded OK\n";
    
    echo "Creating instances...\n";
    $qm = new QuestionManager();
    echo "QuestionManager instance created\n";
    
    $sm = new SessionManager();
    echo "SessionManager instance created\n";
    
    echo "Creating test session...\n";
    $session = $sm->createSession(5, null);
    echo "Session created: " . json_encode($session) . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
