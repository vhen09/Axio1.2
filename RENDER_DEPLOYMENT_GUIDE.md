# AXIO Deployment Guide - Render.com

## Quick Overview
This guide will show you how to deploy the AXIO (Real Analysis Theorem Proving System) to Render.com for FREE, making it accessible online.

---

## Prerequisites
- ✅ GitHub account (already done!)
- ✅ Render.com account (free)
- ✅ DeepSeek API key (optional, for AI features)
- ✅ Your code already pushed to: https://github.com/vhen09/Axio1.2
- ✅ All changes committed and ready (March 18, 2026)

---

## Step-by-Step Deployment

### Step 1: Create Render Account (FREE)
1. Go to https://render.com
2. Click **"Sign Up"** (free tier available)
3. Choose GitHub to sign up
4. Connect your GitHub account
5. Authorize Render to access your repositories

### Step 2: Deploy the Application

#### Option A: Deploy from GitHub (Recommended)
1. After logging in to Render, click **"+ New"** → **"Web Service"**
2. Select your repository: **vhen09/Axio1.2**
3. Fill in the following settings:

   **Name:** `axio-app` (or any name you prefer)
   
   **Region:** Choose closest to you (e.g., `us-east`)
   
   **Branch:** `main`
   
   **Runtime:** `PHP`
   
   **Build Command:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   
   **Start Command:**
   ```bash
   cd . && php -S 0.0.0.0:8080
   ```
   
   **Plan:** Free (optional, but available)

4. Click **"Create Web Service"** and wait for deployment (2-5 minutes)

---

### Step 3: Configure Environment Variables

After deployment starts:

1. Go to your service dashboard
2. Click **"Environment"** on the left sidebar
3. Add these variables:

   ```
   DEEPSEEK_API_KEY = your_api_key_here
   DATABASE_HOST = localhost
   DATABASE_NAME = axio_app
   APP_ENV = production
   ```

4. Get your DEEPSEEK_API_KEY from https://platform.deepseek.com/

---

### Step 4: Access Your Online Website

Once deployment completes:
- Your website will be live at: `https://axio-app.onrender.com` (or similar)
- You'll see a URL like: `https://[your-service-name].onrender.com`

Access the main pages:
- **Dashboard:** `https://axio-app.onrender.com/frontend/pages/dashboard.html`
- **Proof Workspace:** `https://axio-app.onrender.com/frontend/pages/proofworkspace.html`
- **Theorems:** `https://axio-app.onrender.com/frontend/pages/theorems.html`
- **My Proofs:** `https://axio-app.onrender.com/frontend/pages/myproofs.html`
- **Scores:** `https://axio-app.onrender.com/frontend/pages/scores.html`

---

## Latest Changes (March 17-18, 2026)

All these improvements have been added to the deployment:

✅ **UI/UX Redesign**
- Modern interface matching design mockups
- Improved button spacing and styling
- Fixed z-index overlay issues
- Enhanced card layouts with better shadows

✅ **Feedback System Enhancements**
- Removed all emojis from AI feedback
- Made feedback more concise (1-2 sentences max)
- Cleaner verification panel display
- Improved preview area spacing

✅ **Documentation**
- Complete dependencies and libraries list
- Comprehensive thesis appendices
- Updated deployment guides

---

## Troubleshooting

### Issue: "PHP not found"
**Solution:** 
- Make sure `runtime` is set to `PHP`
- Ensure you're using the correct start command: `cd . && php -S 0.0.0.0:8080`

### Issue: "Frontend files not loading"
**Solution:**
- Files are in `/frontend/` directory
- Access with full path: `https://your-domain.onrender.com/frontend/pages/dashboard.html`

### Issue: "Database connection failed"
**Solution:**
- App runs in DEMO MODE without database
- To use real database, upgrade to paid plan or add PostgreSQL

### Issue: "DeepSeek API not working"
**Solution:**
- Add `DEEPSEEK_API_KEY` to environment variables
- Get key from https://platform.deepseek.com/
- Verify key is correct and account has credits

### Issue: "Deployment fails during build"
**Solution:**
- Check **Logs** tab in Render dashboard
- Verify all environment variables are set correctly
- Try **Manual Deploy** button on dashboard

---

## Database Setup (Optional - Paid Plans)

If you want a live database:

1. Render supports PostgreSQL (paid, $7/month minimum)
2. Or use free tier with limitations
3. Alternative: Use MongoDB Atlas (free tier available)

For now, the app works in DEMO MODE without a database.

---

## Performance Tips

✅ **Free tier limitations:**
- Application spins down after 15 minutes of inactivity
- Limited to 1 free web service
- 0.5 GB RAM

✅ **To improve performance:**
- Upgrade to paid plan ($7/month)
- Enable auto-scaling for consistent uptime
- Add database for persistent storage

---

## Automatic Updates

To push new code and auto-redeploy:

1. Make changes locally in VS Code
2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Update: description of changes"
   git push origin main
   ```

3. Render automatically redeploys (auto-update enabled by default)
4. Check deployment status on Render dashboard

---

## Monitoring Your Deployment

In Render Dashboard:
- **Logs** - View application output and errors
- **Metrics** - Check CPU and memory usage
- **Events** - See deployment history
- **Settings** - Configure auto-deploy and environment

---

## System Status Before Deployment

✅ **All Systems Ready:**
- Frontend: Fully functional with modern UI
- Backend: All APIs operational
- Database: Ready (uses SQLite/MySQL)
- Authentication: bcrypt password hashing enabled
- AI Features: DeepSeek integration ready
- Verification: AI proof feedback system working
- Documentation: Complete and thorough

✅ **GitHub Status:**
- Repository: https://github.com/vhen09/Axio1.2
- Branch: main (latest)
- Commits: All changes pushed (bd691afb latest)
- Ready for: Immediate deployment

---

## Helpful Links

- Render Docs: https://render.com/docs
- PHP Deployment: https://render.com/docs/deploy-php
- Your GitHub Repo: https://github.com/vhen09/Axio1.2
- DeepSeek Platform: https://platform.deepseek.com/
- System Documentation: See README.md and other .md files in repo

---

## QUICK START CHECKLIST

- [ ] Create Render account (go to render.com)
- [ ] Connect GitHub account to Render
- [ ] Create new Web Service from vhen09/Axio1.2 repo
- [ ] Set Name: `axio-app`
- [ ] Set Runtime: `PHP`
- [ ] Set Build Command: `composer install --no-dev --optimize-autoloader`
- [ ] Set Start Command: `cd . && php -S 0.0.0.0:8080`
- [ ] Add Environment Variable: `APP_ENV = production`
- [ ] Add DeepSeek API key (optional but recommended)
- [ ] Click "Create Web Service"
- [ ] Wait 2-5 minutes for deployment
- [ ] Access your live site at the provided URL
- [ ] Test the dashboard and features

---

## Support & Issues

If deployment fails:
1. ✅ Check the **Logs** tab in Render dashboard
2. ✅ Verify all environment variables match exactly
3. ✅ Ensure PHP runtime is selected
4. ✅ Try **Manual Deploy** button
5. ✅ Check GitHub for latest commits

**Your AXIO system is production-ready and deployable! 🚀**

Last Updated: March 18, 2026
