# AXIO Deployment Guide - Render.com

## Quick Overview
This guide will show you how to deploy the AXIO (Real Analysis Theorem Proving System) to Render.com for FREE, making it accessible online.

---

## Prerequisites
- ✅ GitHub account (already done!)
- ✅ Render.com account (free)
- ✅ DeepSeek API key (optional, for AI features)
- ✅ Your code already pushed to: https://github.com/vhen09/Axio1.2

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
   cd backend && php -S 0.0.0.0:8080
   ```
   
   **Plan:** Free (optional, but available)

4. Click **"Create Web Service"** and wait for deployment (2-5 minutes)

---

### Step 3: Configure Environment Variables

After deployment starts:

1. Go to your service dashboard
2. Click **"Environment"** on the left
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
- **Theorem Browser:** `https://axio-app.onrender.com/frontend/pages/theorems.html`
- **AI Proof Tutor:** `https://axio-app.onrender.com/frontend/pages/tutor.html`
- **Dashboard:** `https://axio-app.onrender.com/frontend/pages/dashboard.html`

---

## Troubleshooting

### Issue: "PHP not found"
- Make sure `runtime` is set to `PHP`
- Ensure you're using the correct start command

### Issue: "Frontend files not loading"
- The files are in `/frontend/` directory
- Access via: `https://your-domain.onrender.com/frontend/pages/theorems.html`

### Issue: "Database connection failed"
- Without database: App runs in DEMO MODE
- To use real database, upgrade to paid plan or add PostgreSQL

### Issue: "DeepSeek API not working"
- Add `DEEPSEEK_API_KEY` to environment variables
- Get key from https://platform.deepseek.com/

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

✅ **To improve:**
- Upgrade to paid plan ($7/month)
- Enable auto-scaling
- Add database for persistent storage

---

## Update Your Site

To push new code:

1. Make changes locally in VS Code
2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Update: new feature"
   git push origin main
   ```

3. Render automatically redeploys (auto-update enabled by default)
4. Check deployment status on Render dashboard

---

## Monitoring

In Render Dashboard:
- View **Logs** to see application output
- Check **Metrics** for CPU/Memory usage
- Monitor **Events** for deployment history

---

## Helpful Links

- Render Docs: https://render.com/docs
- PHP Support: https://render.com/docs/deploy-php
- Your Repo: https://github.com/vhen09/Axio1.2
- DeepSeek API: https://platform.deepseek.com/

---

## Support

If deployment fails:
1. Check the **Logs** tab in Render
2. Verify all environment variables are set
3. Try redeploying using **Manual Deploy** button

**Final Notes:**
- ✅ Your code is production-ready
- ✅ Frontend is fully functional
- ✅ Backend API is available
- ✅ AI features work (with API key)

Your AXIO system is now deployable! 🚀
