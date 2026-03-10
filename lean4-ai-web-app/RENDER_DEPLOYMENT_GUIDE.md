# AXIO - Render Deployment Guide

Complete step-by-step instructions for deploying the AXIO Real Analysis Theorem Proving System to Render.com

## Prerequisites

- ✅ GitHub account with the code pushed to https://github.com/vhen09/Axio1.2
- ✅ Render.com account (free tier available)
- ✅ DeepSeek API key (from https://platform.deepseek.com/)
- ✅ PHP 8.0+ support on Render (automatic with their PHP runtime)

## Deployment Steps

### Step 1: Create Render Account

1. Go to https://render.com
2. Click **"Sign Up"** and choose GitHub as signup method
3. Authorize Render to access your GitHub account
4. Allow `vhen09/Axio1.2` repository access

### Step 2: Create New Web Service on Render

1. Log in to Render Dashboard: https://dashboard.render.com/
2. Click **"New +"** → **"Web Service"**
3. Select **"Deploy existing code from a repository"**
4. Under "GitHub" section, click **"Connect account"** if needed
5. Search for and select **`vhen09/Axio1.2`**
6. Click **"Connect"**
7. Fill in service details:
   - **Name**: `axio-ai-tutor` (or your preferred name)
   - **Branch**: `main`
   - **Runtime**: `PHP`
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `cd backend && php -S 0.0.0.0:${PORT}`
   - **Plan**: `Free` (sufficient for testing)

### Step 3: Configure Environment Variables

1. Under **"Environment"** section, add these variables:

```
DEEPSEEK_API_KEY=your_actual_deepseek_api_key_here
app_mode=production
database_mode=demo
backend_url=/backend/api/
frontend_url=/frontend/
```

**Important Notes**:
- Get `DEEPSEEK_API_KEY` from: https://platform.deepseek.com/api_keys
- `database_mode=demo` enables the system to work without a database (free tier)
- Without DeepSeek API key, the system will use fallback responses

2. Click **"Create Web Service"**

### Step 4: Handle Deployment

Render will automatically:
1. Clone your repository
2. Install PHP dependencies via composer
3. Start the PHP server on the assigned port
4. Assign a unique URL like: `https://axio-ai-tutor.onrender.com`

### Step 5: Access Your Application

Once deployment completes (usually 2-3 minutes):

1. Visit: `https://[your-service-name].onrender.com`
2. You'll see the landing page with options:
   - **Browse Theorems** - View all 50+ Real Analysis theorems
   - **AI Proof Tutor** - Interactive proof workspace with AI guidance
3. Test the system by:
   - Selecting a theorem from the library
   - Writing a proof in English or any language
   - Getting AI-powered feedback and Lean 4 conversion

### Step 6: Keep System Running

By default, Render services on the free tier **spin down after 15 minutes of inactivity**.

**To prevent inactivity spindown:**
- Upgrade to the paid tier ($7/month minimum), OR
- Use a monitoring service (like Better Uptime) to ping your URL every 10 minutes

## Project File Structure

The deployed system uses:

```
├── index.php                 # Root entry point - routes all requests
├── .htaccess                 # Apache URL rewriting (optional)
├── composer.json             # PHP dependencies
├── render.yaml              # Render deployment configuration
├── backend/
│   ├── api/                 # REST API endpoints
│   ├── services/            # Core business logic
│   ├── models/              # Database access
│   └── config/              # Configuration files
├── frontend/
│   ├── pages/               # HTML pages (theorems.html, tutor.html)
│   ├── img/                 # Images and assets
│   └── css/                 # Stylesheets
└── database/
    ├── schema.sql           # Database schema
    └── seed_theorems.sql   # Theorem data
```

## Troubleshooting

### 1. **Deployment fails with "Build command error"**

**Cause**: Missing `composer.json` file
**Solution**: Ensure `composer.json` is committed and pushed to GitHub

```bash
git add composer.json
git commit -m "Add composer.json for PHP dependencies"
git push origin main
```

### 2. **Pages return 404 errors**

**Cause**: Incorrect start command or routing
**Solution**: Verify `render.yaml` has correct start command:
```bash
cd backend && php -S 0.0.0.0:${PORT}
```

Alternatively, use root `index.php` routing:
```bash
php -S 0.0.0.0:${PORT}
```

### 3. **No database connection ("Demo mode" appears)**

**Status**: This is normal! The system supports demo mode without a database.
**To add database support**:
1. Upgrade to paid Render tier
2. Create PostgreSQL add-on (costs ~$15/month)
3. Set `DATABASE_URL` environment variable
4. Run migration: `mysql -u root < database/schema.sql`

### 4. **AI features not working (no DeepSeek responses)**

**Cause**: Missing DeepSeek API key
**Solution**:
1. Get free API key from https://platform.deepseek.com/api_keys
2. Add to Render environment variables as `DEEPSEEK_API_KEY`
3. Restart the service (Render dashboard → "Manual Deploy")

### 5. **Service keeps spinning down**

**Cause**: Free tier inactive timeout (15 minutes)
**Solution**: 
- Option 1: Upgrade to paid plan
- Option 2: Use uptime monitoring service (Better Uptime, Cron-job.org)
- These ping your URL every 10 minutes to keep it active

### 6. **Large file deploy fails**

**Cause**: Repository size limit
**Solution**: Remove unnecessary files and push:
```bash
git rm -r --cached axio2.0/     # Remove test folder if needed
git commit -m "Clean up repository"
git push origin main
```

## Performance Tips

### For Faster Response Times
1. **Cache static files** - Frontend assets load from CDN automatically on Render
2. **Optimize database queries** - If using database, add indexes to `theorems` table
3. **Use theorem library efficiently** - Pre-load popular theorems in browser cache

### For Monitoring
- Set up uptime monitoring at https://www.betteruptime.com/ (free alerts)
- Check Render logs: Dashboard → Your Service → "Logs"

## Security Notes

- ✅ All API endpoints allow CORS (configured for web access)
- ✅ No authentication required (educational system)
- ✅ DeepSeek API key stored in Render environment (never in code)
- ⚠️ **Production**: Add authentication/rate limiting before public release

## Helpful Links

- **Render Documentation**: https://render.com/docs
- **Render PHP Support**: https://render.com/docs/php-runtimes
- **DeepSeek API Docs**: https://platform.deepseek.com/docs
- **Project Repository**: https://github.com/vhen09/Axio1.2
- **Lean 4 Docs**: https://lean-lang.org/lean4/doc/

## Support & Next Steps

### If you encounter issues:
1. Check the **Troubleshooting** section above
2. Review Render logs: Dashboard → Logs tab
3. Verify all environment variables are set correctly
4. Check DeepSeek API status page

### To enhance the system:
- Add user authentication (optional upgrade)
- Create database backup strategy (when using database)
- Add proof submission persistence (requires database)
- Set up GitHub Actions for CI/CD

## Quick Commands for Future Deployments

```bash
# After making changes locally
git add .
git commit -m "Update AXIO system"
git push origin main

# Render will automatically redeploy from GitHub push
# Check status at: https://dashboard.render.com/
```

## Estimated Costs

| Feature | Cost | Notes |
|---------|------|-------|
| Web Service (Free) | $0 | 0.1 CPU, 512MB RAM, auto-spins down |
| Web Service (Paid) | $7+/month | 0.5 CPU, 1GB RAM, always running |
| PostgreSQL Database | $15+/month | Only needed for data persistence |
| **Total (Free Tier)** | **$0** | Perfect for testing/demonstration |
| **Total (Production)** | **$22+/month** | Web + Database + SSL certificate |

---

**Deployment Status**: Ready for production! All files are configured for Render. Just connect your GitHub repo and deploy. 🚀
