# AZ-305 Certification Helper

A web-based application designed to help students prepare for the **Microsoft Azure Administrator Expert (AZ-305)** certification exam. The application provides challenging multiple-choice questions distributed across exam domains with detailed feedback and references to Microsoft Learn documentation.

## Features

### 📚 Comprehensive Question Database
- **10+ hard questions** per domain, designed to challenge deep knowledge
- Questions distributed by official exam domain percentages:
  - Identity & Governance: 27.5%
  - Network Solutions: 27.5%
  - Compute Solutions: 22.5%
  - Data Storage Solutions: 17.5%
  - Business Continuity: 5%

### 🎯 Customizable Study Sessions
- Choose number of questions (5-50)
- Select specific domains or all domains
- Variable session length for flexible studying

### 📊 Detailed Performance Analytics
- **Overall Score**: Percentage and letter grade
- **Domain-by-Domain Analysis**: See your strengths and weaknesses
- **Detailed Review**: Each question with:
  - Your answer
  - Correct answer
  - Detailed explanation
  - Links to Microsoft Learn documentation

### 🔐 Secure & Cost-Optimized
- Runs on Azure App Service (PHP 8.2)
- JSON-based data storage (no expensive databases)
- Secure HTTPS configuration
- Session data stored locally

### 📱 Responsive Design
- Works on desktop, tablet, and mobile devices
- Clean, modern interface
- Intuitive navigation

## Getting Started

### Local Development

#### Requirements
- PHP 8.2 or higher
- A web server (Apache with mod_rewrite or IIS)
- Git

#### Setup
```bash
# Clone the repository
git clone <repository-url>
cd az-305-helper

# Create necessary directories
mkdir -p data/sessions

# Set permissions (Linux/Mac)
chmod 755 data
chmod 755 data/sessions

# Start a local PHP server
cd public
php -S localhost:8000

# Open in browser
# http://localhost:8000
```

#### Using Docker
```bash
docker build -t az305-helper .
docker run -p 8000:80 az305-helper
```

### Azure Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed deployment instructions.

Quick start:
```bash
# Deploy with Azure CLI
az deployment group create \
  --resource-group az305-rg \
  --template-file infra/main.bicep

# Upload application files
az webapp deployment source config-zip \
  --resource-group az305-rg \
  --name az305helper-xxxxx \
  --src public.zip
```

## Application Structure

```
az-305-helper/
├── public/                          # Web root (served to clients)
│   ├── index.html                  # Main application page
│   ├── css/styles.css              # Application styling
│   ├── js/app.js                   # Frontend logic
│   ├── .htaccess                   # Apache URL rewriting
│   └── web.config                  # IIS configuration
│
├── api/                             # API endpoints
│   └── index.php                   # Main API router
│
├── src/                             # PHP classes
│   ├── QuestionManager.php         # Question loading & selection
│   └── SessionManager.php          # Session & scoring logic
│
├── data/                            # Data storage
│   ├── questions.json              # Question database
│   └── sessions/                   # User session files
│
├── infra/                           # Infrastructure as Code
│   ├── main.bicep                  # Azure infrastructure
│   └── main.bicepparam             # Deployment parameters
│
├── config.php                       # Application configuration
├── README.md                        # This file
└── DEPLOYMENT.md                    # Deployment guide
```

## API Endpoints

### Get Domains
```
GET /api/domains
```
Returns all exam domains with percentages and descriptions.

### Get Random Questions
```
GET /api/questions/random?count=10&domains=identity-governance,network
```
Returns random questions, optionally filtered by specific domains.

### Create Session
```
POST /api/session/create
Body: { "question_count": 10, "domains": null }
```
Creates a new study session and returns session ID.

### Get Session
```
GET /api/session/get/{session_id}
```
Returns session details including questions.

### Submit Answers
```
POST /api/session/submit/{session_id}
Body: { "answers": [0, 1, 2, ...] }
```
Grades the answers and returns detailed results.

## Adding Questions

Edit `data/questions.json` to add new questions:

```json
{
  "id": 11,
  "domain": "domain-id",
  "question": "Your question text?",
  "alternatives": [
    {
      "text": "Correct answer",
      "isCorrect": true
    },
    {
      "text": "Incorrect answer",
      "isCorrect": false
    },
    {
      "text": "Incorrect answer",
      "isCorrect": false
    },
    {
      "text": "Incorrect answer",
      "isCorrect": false
    }
  ],
  "explanation": "Detailed explanation of why the correct answer is right and why others are wrong.",
  "references": [
    "https://learn.microsoft.com/en-us/azure/...",
    "https://learn.microsoft.com/en-us/azure/..."
  ]
}
```

## Configuration

Edit `config.php` to customize:
- Application environment (development/production)
- Session timeout duration
- Security headers
- CORS settings
- PHP version

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance

- Average page load time: < 500ms
- Question loading: < 100ms
- Session creation: < 200ms
- Answer submission: < 300ms

## Security Features

- ✅ HTTPS-only access
- ✅ Security headers (CSP, HSTS, X-Frame-Options)
- ✅ Input validation
- ✅ CORS protection
- ✅ Session isolation
- ✅ No sensitive data in logs

## Cost Estimate (Azure)

- **App Service (B1 Basic)**: ~$10/month
- **Storage (JSON files)**: Negligible (KB)
- **Application Insights**: Free tier (5GB/month)
- **Total**: ~$10-15/month for 100-1000 monthly users

## Development Roadmap

- [ ] User authentication & progress tracking
- [ ] Timed practice exams
- [ ] Question difficulty levels
- [ ] Practice test mode (full exam simulation)
- [ ] Mobile app (native iOS/Android)
- [ ] Integration with Azure DevOps for analytics
- [ ] Multilingual support
- [ ] Video explanations for complex topics

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

Areas needing help:
- Additional questions (especially business continuity scenarios)
- UX/UI improvements
- Performance optimizations
- Documentation
- Language translations

## License

MIT License - See LICENSE file for details

## Support

For issues, questions, or feature requests:
1. Check existing issues
2. Create a detailed issue report
3. Include browser/device information
4. Provide steps to reproduce

## Disclaimer

This application is designed to help AZ-305 exam candidates prepare. It is **not** affiliated with Microsoft and does not provide official exam content. For official exam information, visit:
- [Microsoft Learn: AZ-305](https://learn.microsoft.com/en-us/certifications/exams/az-305/)
- [Exam Study Guide](https://learn.microsoft.com/en-us/credentials/certifications/azure-administrator/)

## Acknowledgments

Questions and explanations are based on official Microsoft documentation and Azure best practices. References link to authoritative Microsoft Learn resources.

## Contact

- Email: support@az305helper.com
- Twitter: @az305helper
- GitHub: [az-305-helper](https://github.com/yourusername/az-305-helper)

---

**Happy studying! Good luck on your AZ-305 exam! 🚀**
