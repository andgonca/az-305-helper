# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-18

### Added
- Initial release of AZ-305 Certification Helper
- Comprehensive question database with 10+ questions per domain
- 5 exam domains with proper percentage distribution:
  - Identity & Governance (27.5%)
  - Network Solutions (27.5%)
  - Compute Solutions (22.5%)
  - Data Storage Solutions (17.5%)
  - Business Continuity (5%)
- Customizable study sessions (5-50 questions)
- Domain filtering for targeted practice
- Detailed performance analytics by domain
- Comprehensive explanations for each question
- Links to Microsoft Learn documentation
- Responsive web interface (desktop, tablet, mobile)
- Session management with local JSON storage
- Scoring engine with letter grades
- Security headers and HTTPS support
- API endpoints for all functionality

### Architecture
- PHP 8.2 backend
- JSON-based data storage (no database required)
- RESTful API
- Modern vanilla JavaScript frontend
- CSS3 with responsive design
- Apache and IIS compatibility

### Deployment
- Azure App Service Bicep infrastructure template
- Docker and Docker Compose support
- Development and production configurations
- Automated deployment documentation

### Documentation
- README with feature overview and quick start
- Deployment guide with multiple deployment options
- Development guide for contributors
- Comprehensive API documentation

### Testing
- Manual testing checklist
- cURL examples for API testing
- Browser compatibility information

## Future Roadmap

### Planned for 1.1.0
- [ ] User authentication (Microsoft Entra ID integration)
- [ ] User progress tracking across sessions
- [ ] Exam mode (full 2-3 hour practice exam)
- [ ] Timed questions with countdown timer
- [ ] Question difficulty levels (Easy, Medium, Hard)
- [ ] Spaced repetition algorithm
- [ ] Statistics dashboard

### Planned for 1.2.0
- [ ] Video explanations for complex topics
- [ ] Interactive diagrams for architecture questions
- [ ] Practice test generator with customizable parameters
- [ ] Performance comparison with other users (anonymized)
- [ ] Recommended learning paths by domain

### Planned for 2.0.0
- [ ] Mobile native apps (iOS/Android)
- [ ] Multiple language support
- [ ] Machine learning-based question recommendations
- [ ] Integration with Azure DevOps for team analytics
- [ ] Support for other Azure certifications (AZ-104, AZ-204, etc.)
- [ ] Migration from JSON to cloud database (Azure Cosmos DB)

## Known Issues

None at release.

## Version History

### [1.0.0] - Initial Release
- 2026-04-18: First stable release

---

## How to Contribute

When contributing changes, please:

1. Create a feature branch from `develop`
2. Make your changes
3. Write or update tests
4. Update documentation
5. Update CHANGELOG.md with your changes
6. Create a pull request

### Change Categories

Use one of these categories when describing changes:

- **Added** for new features
- **Changed** for changes in existing functionality
- **Deprecated** for soon-to-be removed features
- **Removed** for now removed features
- **Fixed** for any bug fixes
- **Security** for any security-related changes

### Example Entry

```markdown
## [1.1.0] - 2026-05-15

### Added
- User authentication with Microsoft Entra ID
- Session history for registered users
- Dark mode theme option

### Changed
- Improved performance of question loading
- Updated styling for better accessibility

### Fixed
- Session timeout handling
- Mobile navigation menu

### Security
- Updated security headers
- Added rate limiting to API endpoints
```

## Release Process

1. Update version number in relevant files
2. Update CHANGELOG.md
3. Create a release commit
4. Tag the commit with version number
5. Deploy to Azure
6. Announce release

---

For more information, see [DEVELOPMENT.md](./DEVELOPMENT.md) and [DEPLOYMENT.md](./DEPLOYMENT.md).
