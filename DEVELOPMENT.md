# AZ-305 Helper - Development Guide

## Table of Contents
1. [Local Setup](#local-setup)
2. [Project Structure](#project-structure)
3. [Adding Questions](#adding-questions)
4. [API Development](#api-development)
5. [Frontend Development](#frontend-development)
6. [Database Management](#database-management)
7. [Testing](#testing)
8. [Deployment](#deployment)

## Local Setup

### Prerequisites
- PHP 8.2 or higher
- Apache or Nginx with rewrite module enabled
- Git
- (Optional) Docker and Docker Compose

### Option 1: Using PHP Built-in Server

```bash
# Navigate to the project
cd az-305-helper

# Start PHP server from public directory
cd public
php -S localhost:8000

# Access the application
# Browser: http://localhost:8000
```

### Option 2: Using Apache

```bash
# Create virtual host (Ubuntu/Debian)
sudo nano /etc/apache2/sites-available/az305.local.conf

# Add the following configuration:
<VirtualHost *:80>
    ServerName az305.local
    ServerAdmin admin@az305.local
    DocumentRoot /path/to/az-305-helper/public
    
    <Directory /path/to/az-305-helper/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/az305-error.log
    CustomLog ${APACHE_LOG_DIR}/az305-access.log combined
</VirtualHost>

# Enable the site
sudo a2ensite az305.local
sudo a2enmod rewrite
sudo systemctl restart apache2

# Add to hosts file
echo "127.0.0.1 az305.local" | sudo tee -a /etc/hosts

# Access: http://az305.local
```

### Option 3: Using Docker

```bash
# Build and run with docker-compose
docker-compose up -d

# Access the application
# Browser: http://localhost:8000

# View logs
docker-compose logs -f

# Stop the application
docker-compose down
```

## Project Structure

### Frontend (`public/`)
- **index.html**: Main application page with all views
- **css/styles.css**: Application styling using CSS variables for theming
- **js/app.js**: Frontend logic and API communication

### Backend (`api/` and `src/`)
- **api/index.php**: Main API router handling all endpoints
- **src/QuestionManager.php**: Loads and filters questions
- **src/SessionManager.php**: Creates sessions and grades answers

### Data (`data/`)
- **questions.json**: Question database with domains and answers
- **sessions/**: Directory storing user session files

### Configuration
- **config.php**: Application settings and security headers
- **public/web.config**: IIS configuration
- **public/.htaccess**: Apache URL rewriting rules

## Adding Questions

### Question Format

```json
{
  "id": 11,
  "domain": "identity-governance",
  "question": "Your question text here?",
  "alternatives": [
    {
      "text": "Correct answer",
      "isCorrect": true
    },
    {
      "text": "Wrong answer 1",
      "isCorrect": false
    },
    {
      "text": "Wrong answer 2",
      "isCorrect": false
    },
    {
      "text": "Wrong answer 3",
      "isCorrect": false
    }
  ],
  "explanation": "Detailed explanation covering:\n1. Why this answer is correct\n2. Why other answers are incorrect\n3. Key concepts to remember",
  "references": [
    "https://learn.microsoft.com/en-us/azure/...",
    "https://learn.microsoft.com/en-us/azure/..."
  ]
}
```

### Steps to Add a Question

1. Open `data/questions.json`
2. Find the highest question ID and add 1
3. Add your new question object to the `questions` array
4. Ensure the `domain` matches an ID in the `domains` array
5. Create exactly 4 alternatives with one marked as correct
6. Provide a detailed explanation with key concepts
7. Add 2-3 Microsoft Learn reference links
8. Save the file

### Question Writing Tips

- **Write comprehensive questions**: Include detailed scenarios
- **Make wrong answers plausible**: Wrong answers should be common mistakes
- **Link to resources**: Always provide references to official documentation
- **Test your questions**: Review for clarity and accuracy
- **Cover multiple angles**: Ask about design tradeoffs, not just facts

### Valid Domains

```json
{
  "id": "identity-governance",
  "name": "Design identity and governance solutions",
  "percentage": 27.5
},
{
  "id": "network",
  "name": "Design network solutions",
  "percentage": 27.5
},
{
  "id": "compute",
  "name": "Design compute solutions",
  "percentage": 22.5
},
{
  "id": "data-storage",
  "name": "Design data storage solutions",
  "percentage": 17.5
},
{
  "id": "business-continuity",
  "name": "Design business continuity solutions",
  "percentage": 5.0
}
```

## API Development

### Adding a New Endpoint

1. Create method in `src/` class (e.g., `QuestionManager` or `SessionManager`)
2. Add case to switch statement in `api/index.php`
3. Return JSON response

### Example: Adding a Statistics Endpoint

```php
// In src/SessionManager.php
public function getStatistics($session_id) {
    $session = $this->getSession($session_id);
    if (!$session) {
        return null;
    }
    
    return [
        'total_sessions' => count(glob($this->sessions_dir . '/*.json')),
        'completed_sessions' => count(array_filter(...))
    ];
}

// In api/index.php
case 'stats':
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    $sm = new SessionManager();
    $stats = $sm->getStatistics();
    echo json_encode($stats);
    break;
```

### API Response Format

All endpoints should return JSON with this structure:
```json
{
  "success": true,
  "data": { /* actual data */ },
  "error": null
}
```

For errors:
```json
{
  "success": false,
  "data": null,
  "error": "Error description"
}
```

## Frontend Development

### App Structure

The `AZ305App` class handles:
- View management (switching between home, setup, quiz, results)
- API communication
- Session lifecycle
- Question display and answer collection
- Results rendering

### Adding a New View

1. Create HTML structure in `public/index.html`
2. Add CSS styles to `public/css/styles.css`
3. Add show/hide logic to `AZ305App` class in `public/js/app.js`
4. Update navigation links

### Modifying Styles

Use CSS variables defined at the top of `styles.css`:
```css
:root {
    --primary-color: #0078d4;
    --secondary-color: #50e6ff;
    --success-color: #107c10;
    --danger-color: #da3b01;
    /* ... */
}
```

Change theme by updating these values.

### JavaScript Best Practices

- Use async/await for API calls
- Always catch promises
- Validate user input before sending
- Update UI immediately after user action (optimistic updates)
- Provide loading states and error messages

## Database Management

### Exporting Sessions

```php
// Create backup of all sessions
$sessions = glob(__DIR__ . '/data/sessions/*.json');
$backup = [];
foreach ($sessions as $session_file) {
    $backup[] = json_decode(file_get_contents($session_file), true);
}
file_put_contents('sessions_backup_' . date('Y-m-d') . '.json', 
    json_encode($backup, JSON_PRETTY_PRINT));
```

### Cleaning Up Old Sessions

```php
// Delete sessions older than 30 days
$threshold = time() - (30 * 24 * 60 * 60);
$sessions = glob(__DIR__ . '/data/sessions/*.json');
foreach ($sessions as $session_file) {
    if (filemtime($session_file) < $threshold) {
        unlink($session_file);
    }
}
```

### Analyzing Session Data

```php
// Get statistics from all sessions
$sessions = glob(__DIR__ . '/data/sessions/*.json');
$stats = [
    'total_sessions' => count($sessions),
    'average_score' => 0,
    'domain_stats' => []
];

// Calculate aggregates
```

## Testing

### Manual Testing Checklist

- [ ] Create a session with default settings
- [ ] Select specific domains and create session
- [ ] Answer all questions
- [ ] Submit answers
- [ ] Verify scoring is correct
- [ ] Check results display
- [ ] Test navigation between questions
- [ ] Test on mobile device
- [ ] Test on different browsers
- [ ] Test API endpoints with curl

### API Testing with cURL

```bash
# Get domains
curl http://localhost:8000/api/domains

# Get random questions
curl "http://localhost:8000/api/questions/random?count=5"

# Create session
curl -X POST http://localhost:8000/api/session/create \
  -H "Content-Type: application/json" \
  -d '{"question_count": 5, "domains": null}'

# Submit answers (replace SESSION_ID)
curl -X POST http://localhost:8000/api/session/submit/SESSION_ID \
  -H "Content-Type: application/json" \
  -d '{"answers": [0, 1, 2, 1, 0]}'
```

### Browser DevTools

1. **Network Tab**: Monitor API calls
2. **Console**: Check for JavaScript errors
3. **Application**: View session storage
4. **Responsive Design Mode**: Test on different screen sizes

## Deployment

### Before Deployment Checklist

- [ ] Run all tests
- [ ] Update version number
- [ ] Test on different browsers
- [ ] Check console for warnings/errors
- [ ] Verify all links are correct
- [ ] Test on mobile
- [ ] Check security headers
- [ ] Backup data directory
- [ ] Update CHANGELOG
- [ ] Review code changes

### Deployment Steps

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed instructions.

Quick summary:
1. Push to Git
2. Run infrastructure deployment (Bicep)
3. Upload application files
4. Configure App Service settings
5. Test deployed application

## Performance Optimization

### Frontend
- Minimize CSS and JavaScript
- Lazy load images if added
- Use CSS variables for theming efficiency
- Optimize DOM manipulation

### Backend
- Cache domain data if needed
- Consider session cleanup job
- Monitor file system performance
- Profile slow endpoints

### Database (JSON)
- Keep questions.json reasonably sized
- Archive old sessions periodically
- Consider migrating to database if > 1000 sessions/month

## Troubleshooting Development Issues

### "API endpoint not found"
- Check URL in browser matches route in `api/index.php`
- Verify `.htaccess` or `web.config` is in correct location
- Check rewrite module is enabled

### "Sessions not saving"
- Verify `data/sessions/` directory exists
- Check permissions: `chmod 755 data/sessions/`
- Verify PHP can write to directory

### "Frontend doesn't load"
- Check `public/index.html` exists
- Verify CSS and JS files are referenced correctly
- Check browser console for errors

### "Questions not loading"
- Verify `data/questions.json` is valid JSON
- Check file path in `QuestionManager.php`
- Verify JSON syntax with online validator

## Code Style Guidelines

- PHP: PSR-12 standard
- JavaScript: Use const/let, arrow functions
- CSS: BEM naming convention
- Comments: Clear and concise
- Functions: Single responsibility principle

## Resources

- [Microsoft Learn - AZ-305](https://learn.microsoft.com/en-us/certifications/exams/az-305/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MDN Web Docs](https://developer.mozilla.org/)
- [Azure Documentation](https://learn.microsoft.com/en-us/azure/)
