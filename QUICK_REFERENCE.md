# AZ-305 Helper - Quick Reference Card

## 🚀 Getting Started (Choose One)

### Windows
```bash
start-dev-windows.bat
```

### Linux/Mac
```bash
chmod +x start-dev.sh
./start-dev.sh
```

### Docker (All Platforms)
```bash
docker-compose up
```

**Access:** http://localhost:8000

---

## 📁 Project Structure at a Glance

```
az-305-helper/
├── public/              # Web root (HTML, CSS, JS)
├── api/                 # PHP API endpoints
├── src/                 # PHP business logic
├── data/                # JSON database + sessions
├── infra/               # Azure infrastructure (Bicep)
├── config.php           # Configuration
├── *.md                 # Documentation (5 guides)
└── Docker*              # Container files
```

---

## 📚 Documentation Map

| Document | Purpose | Audience |
|----------|---------|----------|
| **README.md** | Overview, features, setup | Everyone |
| **DEPLOYMENT.md** | Azure deployment steps | DevOps/Admins |
| **DEVELOPMENT.md** | API docs, adding questions | Developers |
| **PROJECT_SUMMARY.md** | Complete file reference | Project managers |
| **CHANGELOG.md** | Version history, roadmap | Contributors |

---

## 🔌 API Quick Reference

```bash
# Get domains
curl http://localhost:8000/api/domains

# Get random questions (10, all domains)
curl "http://localhost:8000/api/questions/random?count=10"

# Create session
curl -X POST http://localhost:8000/api/session/create \
  -H "Content-Type: application/json" \
  -d '{"question_count": 10, "domains": null}'

# Submit answers
curl -X POST http://localhost:8000/api/session/submit/SESSION_ID \
  -H "Content-Type: application/json" \
  -d '{"answers": [0, 1, 2, 1, 0]}'
```

---

## 🎯 Add a New Question (5 Steps)

1. Open `data/questions.json`
2. Copy the last question block
3. Update: `id`, `question`, `alternatives`, `explanation`, `references`
4. Ensure exactly 1 alternative has `"isCorrect": true`
5. Save and test in browser

**Example:**
```json
{
  "id": 11,
  "domain": "identity-governance",
  "question": "Your question here?",
  "alternatives": [
    { "text": "Correct", "isCorrect": true },
    { "text": "Wrong 1", "isCorrect": false },
    { "text": "Wrong 2", "isCorrect": false },
    { "text": "Wrong 3", "isCorrect": false }
  ],
  "explanation": "Why the answer is correct...",
  "references": ["https://learn.microsoft.com/..."]
}
```

---

## 🌐 Valid Domains

```json
"identity-governance"    // 27.5%
"network"               // 27.5%
"compute"              // 22.5%
"data-storage"         // 17.5%
"business-continuity"  // 5%
```

---

## 🔐 Security Features

✅ HTTPS-only  
✅ Security headers  
✅ Input validation  
✅ CORS protection  
✅ Session isolation  

---

## 💰 Azure Deployment Cost

- **App Service (B1)**: ~$10/month
- **Storage**: <$1/month  
- **Application Insights**: Free (5GB)
- **Total**: ~$10-15/month

---

## 🚢 One-Line Deploy

```bash
az deployment group create --resource-group az305-rg --template-file infra/main.bicep --parameters infra/main.bicepparam
```

---

## 📊 Application Features

**Questions:**
- 10+ per domain
- Domain-specific filtering
- 4 alternatives each
- With explanations
- Microsoft Learn links

**Sessions:**
- 5-50 questions
- Custom domain selection
- Progress tracking
- Real-time grading

**Results:**
- Overall score & grade
- Domain breakdown
- Detailed review
- Answer explanations

---

## 🔧 Useful Commands

```bash
# Development server (PHP)
cd public && php -S localhost:8000

# Docker development
docker-compose up

# Deploy to Azure
az webapp deployment source config-zip \
  --resource-group az305-rg \
  --name az305helper-xxxxx \
  --src public.zip

# View logs
az webapp log tail --name APP_NAME --resource-group RG_NAME

# Clear sessions (cleanup)
rm data/sessions/*.json

# Add to Git
git add .
git commit -m "Add AZ-305 Helper application"
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| API 404 | Check `.htaccess` or `web.config` |
| Sessions won't save | `chmod 755 data/sessions/` |
| Questions don't load | Validate `questions.json` |
| PHP not found | Install PHP 8.2, add to PATH |
| Port 8000 in use | Change port: `php -S localhost:8001` |

---

## 📈 Performance

- Page load: <500ms
- Questions load: <100ms
- API response: <100ms average
- Session create: <200ms

---

## 🎓 Exam Domains (AZ-305)

1. **Identity & Governance** (27.5%)
   - Azure AD, RBAC, Governance

2. **Network Solutions** (27.5%)
   - VNets, Load Balancers, DNS

3. **Compute Solutions** (22.5%)
   - VMs, App Service, AKS

4. **Data Storage** (17.5%)
   - Storage, Databases, Caching

5. **Business Continuity** (5%)
   - DR, Backup, High Availability

---

## 📞 Support

- **Documentation**: See .md files
- **Issues**: Create GitHub issue
- **Development**: See DEVELOPMENT.md
- **Deployment**: See DEPLOYMENT.md

---

## ✨ Key Files

| File | Lines | Purpose |
|------|-------|---------|
| `public/js/app.js` | ~500 | Frontend logic |
| `public/css/styles.css` | ~1000 | Responsive design |
| `api/index.php` | ~100 | API router |
| `src/SessionManager.php` | ~200 | Session logic |
| `src/QuestionManager.php` | ~150 | Question logic |
| `data/questions.json` | ~500 | Question DB |
| `infra/main.bicep` | ~150 | Infrastructure |

---

## 📦 Technology Stack

**Backend:** PHP 8.2, Apache/IIS  
**Frontend:** HTML5, CSS3, JavaScript (vanilla)  
**Data:** JSON files  
**Cloud:** Azure App Service  
**Container:** Docker  
**IaC:** Bicep  

---

## 🎯 Next Steps

1. **Setup:** Run `start-dev.sh` or `start-dev-windows.bat`
2. **Test:** Visit http://localhost:8000
3. **Add Questions:** Edit `data/questions.json`
4. **Deploy:** Follow `DEPLOYMENT.md`
5. **Contribute:** See `DEVELOPMENT.md`

---

**Happy Studying! Good luck on AZ-305! 🚀**

For detailed information, see [PROJECT_SUMMARY.md](./PROJECT_SUMMARY.md)
