# AZ-305 Certification Helper - Deployment Guide

## Overview
This application is designed to be deployed on Azure App Service with PHP 8.2 support. All data is stored in JSON files for cost optimization.

## Prerequisites
- Azure Subscription
- Azure CLI or Azure PowerShell installed
- Git (for version control)

## Deployment Steps

### 1. Clone and Prepare the Repository
```bash
git clone <your-repository-url>
cd az-305-helper
```

### 2. Deploy Infrastructure with Bicep

#### Using Azure CLI:
```bash
# Login to Azure
az login

# Set your preferred region and resource group
RESOURCE_GROUP="az305-rg"
LOCATION="eastus"

# Create resource group
az group create \
  --name $RESOURCE_GROUP \
  --location $LOCATION

# Deploy the infrastructure
az deployment group create \
  --resource-group $RESOURCE_GROUP \
  --template-file infra/main.bicep \
  --parameters infra/main.bicepparam
```

#### Using Azure Portal:
1. Go to Azure Portal
2. Click "Create a resource"
3. Search for "Template deployment"
4. Click "Build your own template in the editor"
5. Upload the `infra/main.bicep` file
6. Fill in the parameters and deploy

### 3. Deploy Application Code

#### Option A: Using Azure App Service Deployment Slot (Recommended)
```bash
# Set variables
APP_NAME="az305helper-xxxxx" # Use the name from Bicep output
RESOURCE_GROUP="az305-rg"

# Publish the application
az webapp deployment source config-zip \
  --resource-group $RESOURCE_GROUP \
  --name $APP_NAME \
  --src public.zip
```

First, create the zip file:
```bash
# On Windows PowerShell:
Compress-Archive -Path "public\*", "api\*", "src\*", "data\*", "config.php" -DestinationPath public.zip

# On Linux/Mac:
zip -r public.zip public/ api/ src/ data/ config.php
```

#### Option B: Using Git Deployment
```bash
# Configure local git deployment
az webapp deployment user set \
  --user-name <username> \
  --password <password>

# Get the deployment URL
az webapp deployment source config-local-git \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP

# Add the Azure remote and push
git remote add azure <deployment-url>
git push azure main
```

#### Option C: Using FTP
```bash
# Get FTP credentials
az webapp deployment list-publishing-profiles \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --query "[?publishMethod=='FTP'].{url:publishUrl, userName:userName, userPassword:userPassword}"

# Use an FTP client to upload files
```

### 4. Configure App Service Settings

```bash
# Set environment variables
az webapp config appsettings set \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --settings APP_ENV="production" PHP_VERSION="8.2"

# Enable HTTPS only
az webapp update \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --https-only true

# Set startup command if needed
az webapp config set \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --startup-file "startup.sh"
```

### 5. Verify Deployment
```bash
# Get the application URL
az webapp show \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --query "defaultHostName"

# Test the API
curl https://<your-app-name>.azurewebsites.net/api/domains
```

## File Structure
```
az-305-helper/
├── public/                  # Web root
│   ├── index.html          # Main HTML file
│   ├── css/
│   │   └── styles.css      # Application styles
│   ├── js/
│   │   └── app.js          # Frontend JavaScript
│   ├── .htaccess           # Apache rewrite rules
│   └── web.config          # IIS configuration
├── api/                     # API endpoints
│   └── index.php           # Main API handler
├── src/                     # PHP classes
│   ├── QuestionManager.php # Question handling
│   └── SessionManager.php  # Session management
├── data/                    # Data files
│   ├── questions.json      # Question database
│   └── sessions/           # User session data
├── infra/                   # Infrastructure as Code
│   ├── main.bicep          # Bicep template
│   └── main.bicepparam     # Parameters file
└── config.php              # Application configuration
```

## Database Management (JSON Files)

### Adding More Questions
Edit `data/questions.json` and add questions to the `questions` array:
```json
{
  "id": 11,
  "domain": "identity-governance",
  "question": "Your question here?",
  "alternatives": [
    {
      "text": "Option A",
      "isCorrect": true
    },
    {
      "text": "Option B",
      "isCorrect": false
    },
    ...
  ],
  "explanation": "Detailed explanation",
  "references": ["https://learn.microsoft.com/..."]
}
```

### Modifying Domain Distribution
Update the `domains` array in `data/questions.json` to change the percentage distribution:
```json
{
  "id": "domain-id",
  "name": "Domain Name",
  "percentage": 25.0,
  "description": "Domain description"
}
```

## Maintenance

### Backup Sessions
Sessions are stored in `data/sessions/` directory. Set up automated backups:
```bash
# Create a backup schedule in App Service
az webapp config backup create \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP \
  --backup-name "weekly-backup" \
  --frequency "Weekly" \
  --frequency-unit "Week" \
  --frequency-interval 1 \
  --storage-account-url "https://<storage>.blob.core.windows.net/<container>/<sas-token>"
```

### Monitor Application
```bash
# View application logs
az webapp log tail \
  --name $APP_NAME \
  --resource-group $RESOURCE_GROUP

# View Application Insights metrics
az monitor app-insights show \
  --resource-group $RESOURCE_GROUP \
  --query "id"
```

## Security Considerations

1. **HTTPS**: The Bicep template configures HTTPS-only access
2. **Headers**: Security headers are set in `config.php`
3. **Input Validation**: API endpoints validate all inputs
4. **CORS**: Configured to allow requests from any origin (modify if needed)
5. **File Permissions**: Ensure `data/` directory is writable but not directly accessible

## Scaling

For increased load:
1. Upgrade App Service Plan from B1 to S1 or higher
2. Enable autoscaling:
```bash
az appservice plan update \
  --name <plan-name> \
  --resource-group $RESOURCE_GROUP \
  --sku S2

az monitor autoscale create \
  --resource-group $RESOURCE_GROUP \
  --resource <app-name> \
  --resource-type "Microsoft.Web/sites" \
  --min-count 1 --max-count 3 \
  --count 1
```

## Troubleshooting

### Application not loading
- Check that the `public/` directory is the web root
- Verify PHP version is set to 8.2
- Check web.config rules for rewrite errors

### API returning 404
- Ensure URL rewriting is enabled in web.config
- Check that `api/index.php` exists
- Verify permissions on the api/ directory

### Session data not saving
- Check that `data/sessions/` directory exists and is writable
- Verify file permissions: `chmod 755 data/sessions/`
- Check Azure App Service file system quotas

### Performance issues
- Monitor Application Insights metrics
- Check App Service CPU and memory usage
- Consider increasing App Service Plan tier

## Cost Optimization

This application uses:
- **B1 Basic App Service Plan**: ~$10/month (adjust based on needs)
- **JSON file storage**: Included in App Service storage
- **Application Insights**: First 5GB/month free

Total estimated cost: **~$10-15/month** for a small to medium user base.

## Support and Updates

For questions or to report issues, please check the documentation or contact support.

For latest Azure best practices: https://learn.microsoft.com/azure/
For PHP on Azure: https://learn.microsoft.com/azure/app-service/app-service-web-get-started-php
