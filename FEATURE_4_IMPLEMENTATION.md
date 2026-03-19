# Feature 4 Implementation Guide - Login/Signup with First/Last Names

## ✅ Changes Made

### 1. **Database Schema Updates**
- **File**: `database/schema.sql`
- **Changes**: Added three new columns to users table:
  - `first_name VARCHAR(100) DEFAULT ''`
  - `last_name VARCHAR(100) DEFAULT ''`
  - `email VARCHAR(100) DEFAULT ''`

### 2. **Frontend - Authentication Form** (`frontend/pages/auth.html`)
- **Signup Form Changes**:
  - Replaced single "Full Name" field with separate "First Name" and "Last Name" fields
  - Added "Email" field for email collection
  - Updated form validation to require all three fields
  
- **Form Visibility**:
  - `switchToSignup()` now shows: firstname-group, lastname-group, email-group
  - `switchToLogin()` now hides: firstname-group, lastname-group, email-group
  
- **Form Submission**:
  - `handleSignup()` now collects: first_name, last_name, email, username, password
  - Sends all fields to backend in JSON payload
  - Stores all user data in sessionStorage after successful login

- **Session Storage**:
  - Signup auto-login saves: first_name, last_name, email to sessionStorage
  - Manual login also saves: first_name, last_name, email to sessionStorage

### 3. **Backend - User Model** (`backend/models/User.php`)
- **create() method signature updated**:
  - From: `create($username, $password)`
  - To: `create($username, $password, $firstName = '', $lastName = '', $email = '')`
  
- **Database query updated**:
  - INSERT now includes: first_name, last_name, email columns
  - Gracefully handles missing values with empty string defaults

- **findByUsername() query updated**:
  - SELECT now retrieves: first_name, last_name, email
  - Available in returned user array

### 4. **Backend - Auth API** (`backend/api/auth.php`)
- **register() method signature**:
  - From: `register($username, $password)`
  - To: `register($username, $password, $firstName = '', $lastName = '', $email = '')`
  
- **POST handler updated**:
  - Extracts: first_name, last_name, email from request
  - Passes to register() method
  
- **register() response**:
  - Now returns: first_name, last_name, email in JSON response
  
- **login() response updated**:
  - Now returns: first_name, last_name, email in JSON response
  - Uses null coalescing to provide empty strings if not set

### 5. **Backend - User Profile API** (`backend/api/user-profile.php`)
- **GET action**:
  - Queries: first_name, last_name, email
  - Returns complete user profile with name fields
  
- **UPDATE action**:
  - Accepts: first_name, last_name, email parameters
  - Updates user record with new values
  - Saves updated values to sessionStorage
  
- **Demo mode**:
  - Stores sessionStorage values for demo environment

### 6. **Frontend - Settings Page** (`frontend/pages/settings.html`)
- **Profile form restructured**:
  - Removed: "Full Name" field
  - Added: "First Name" input field
  - Added: "Last Name" input field
  - Added: "Email" input field
  - Kept: "Username" field
  
- **Form loading**:
  - Loads first_name, last_name, email from sessionStorage
  - Falls back to backend API if not in session
  - Displays all values in form
  
- **Form submission**:
  - Sends: first_name, last_name, email, username to backend
  - Saves response data to sessionStorage
  - Shows success message on completion

## 📋 Database Migration

### For New Installations
The new schema is already in `database/schema.sql`. Just run START_SYSTEM.bat to create the database.

### For Existing Installations
Run the migration script to add columns to existing database:
```sql
mysql -u root -p lean4_ai_db < database/migrate_add_user_names.sql
```

Or manually in MySQL:
```sql
USE lean4_ai_db;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT '';

-- Verify columns were added
DESCRIBE users;
```

## 🧪 Testing Checklist

### 1. Signup Flow
- [ ] Navigate to auth.html
- [ ] Click "Sign Up"
- [ ] Enter: First Name="John", Last Name="Doe", Email="john@example.com", Username="johndoe", Password="pass123"
- [ ] Confirm password matches
- [ ] Click "Sign Up"
- [ ] Auto-login should occur
- [ ] User should be redirected to latex-skill-check.html
- [ ] Browser should show no console errors

### 2. Profile Verification
- [ ] After successful signup, navigate to settings.html
- [ ] Verify "First Name" field shows "John"
- [ ] Verify "Last Name" field shows "Doe"
- [ ] Verify "Email" field shows "john@example.com"
- [ ] Verify "Username" field shows "johndoe"

### 3. Database Verification
```sql
SELECT id, username, first_name, last_name, email FROM users WHERE username='johndoe';
```
Should return:
```
id | username | first_name | last_name | email
1  | johndoe  | John       | Doe       | john@example.com
```

### 4. Login Flow
- [ ] Logout from dashboard
- [ ] Navigate to auth.html
- [ ] Enter: Username="johndoe", Password="pass123"
- [ ] Click "Log In"
- [ ] User should be authenticated
- [ ] sessionStorage should contain first_name, last_name, email

### 5. Profile Update
- [ ] In settings.html, change First Name to "Jonathan"
- [ ] Click "Save Profile"
- [ ] Refresh the page
- [ ] Verify "First Name" now shows "Jonathan"
- [ ] Check database to verify update persisted:
```sql
SELECT first_name FROM users WHERE username='johndoe';  -- Should be "Jonathan"
```

### 6. Dark Mode Compatibility
- [ ] Enable dark mode in settings
- [ ] Verify profile form is readable
- [ ] Verify all labels and inputs are properly styled

### 7. Backward Compatibility
- [ ] Users signed up before this update (if any) should still be able to login
- [ ] Their first_name and last_name fields will be empty strings
- [ ] They can update their profile to add first/last names

## 🚀 Deployment Steps

1. **Backup current database** (if production):
   ```bash
   mysqldump -u root -p lean4_ai_db > backup_$(date +%Y%m%d).sql
   ```

2. **Apply migration**:
   ```bash
   mysql -u root -p lean4_ai_db < database/migrate_add_user_names.sql
   ```

3. **Test locally**:
   ```bash
   # Run START_SYSTEM.bat or ./start_system.sh
   # Navigate to http://localhost:8080/frontend/pages/auth.html
   # Test signup and login flows
   ```

4. **Deploy to production**:
   ```bash
   git add -A
   git commit -m "Feature 4: Add first/last name fields to signup and profile"
   git push origin main
   # Render.com will auto-deploy
   ```

5. **Post-deployment verification**:
   - [ ] Test signup on production
   - [ ] Test login on production
   - [ ] Check user profile shows correct names
   - [ ] Verify database has new columns and data

## 📊 Data Flow Diagram

```
Signup Form (auth.html)
    ↓
collectData: {
  first_name: "John",
  last_name: "Doe",
  email: "john@example.com",
  username: "johndoe",
  password: "pass123"
}
    ↓
POST /backend/api/auth.php?action=register
    ↓
Auth::register($username, $password, $firstName, $lastName, $email)
    ↓
User::create($username, $password, $firstName, $lastName, $email)
    ↓
INSERT into users (username, password, first_name, last_name, email, created_at)
    ↓
Response: {
  success: true,
  user_id: 1,
  username: "johndoe",
  first_name: "John",
  last_name: "Doe",
  email: "john@example.com"
}
    ↓
sessionStorage.setItem('first_name', 'John')
sessionStorage.setItem('last_name', 'Doe')
sessionStorage.setItem('email', 'john@example.com')
    ↓
Auto-login → Dashboard
```

## 🐛 Troubleshooting

### "Undefined column" error on signup
- **Cause**: Migration script not run
- **Solution**: 
  ```sql
  ALTER TABLE users ADD COLUMN first_name VARCHAR(100) DEFAULT '';
  ALTER TABLE users ADD COLUMN last_name VARCHAR(100) DEFAULT '';
  ALTER TABLE users ADD COLUMN email VARCHAR(100) DEFAULT '';
  ```

### Profile fields show empty even after signup
- **Cause**: sessionStorage not populated
- **Solution**: 
  1. Check browser DevTools → Application → Session Storage
  2. Verify first_name, last_name, email keys exist
  3. Clear sessionStorage and login again

### Login doesn't return name fields
- **Cause**: Backend query not updated
- **Solution**: Verify auth.php login() method returns first_name, last_name, email in response

### Form won't submit
- **Cause**: Frontend validation failing
- **Solution**: 
  1. Check browser console for error messages
  2. Verify first name and last name fields are not empty
  3. Verify email has @ symbol

## 📝 Notes
- All existing users can still login (first/last name will be empty)
- Users must signup again or update profile to add name fields
- Email addresses are stored but not yet used for password reset (Feature 6)
- Avatar initials feature not yet implemented (coming in Phase 3)
