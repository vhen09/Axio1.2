# Lean4 AI Web Application

## Overview
This project is a web-based application that utilizes Lean4 as the core logic engine for verifying mathematical proofs and integrates AI-assisted analysis through the DeepSeek AI API. The application allows users to submit both formal and informal mathematical inputs, which are processed and validated using Lean4, providing dynamic scoring based on verification results.

## Tech Stack
- **Backend**: PHP, MySQL
- **Logic Engine**: Lean4
- **AI Integration**: DeepSeek AI (API-based)
- **Frontend**: HTML, CSS, JavaScript

## Features
- **User Authentication**: Secure login and registration processes.
- **Submission Management**: Users can submit mathematical proofs and informal inputs.
- **Lean Verification**: Inputs are verified for correctness using Lean4.
- **AI Assistance**: AI provides guidance and explanations through API interactions.
- **Dynamic Scoring**: Scores are generated based on Lean verification results.
- **User-Friendly Interface**: A clean and intuitive web interface with a clickable sidebar navigation.

## Project Structure
```
lean4-ai-web-app
├── backend
│   ├── api
│   ├── config
│   ├── services
│   ├── models
│   └── index.php
├── lean
│   ├── Main.lean
│   ├── Verification.lean
│   ├── Scoring.lean
│   └── Parser.lean
├── frontend
│   ├── css
│   ├── js
│   ├── pages
│   └── index.html
├── database
│   └── schema.sql
├── .env.example
├── composer.json
├── lakefile.lean
└── README.md
```

## Setup Instructions
1. **Clone the Repository**: 
   ```
   git clone <repository-url>
   cd lean4-ai-web-app
   ```

2. **Install Dependencies**: 
   - For PHP dependencies, run:
     ```
     composer install
     ```

3. **Database Setup**: 
   - Import the SQL schema located in `database/schema.sql` into your MySQL database.

4. **Environment Configuration**: 
   - Copy `.env.example` to `.env` and configure your database and API settings.

5. **Run the Application**: 
   - Start your PHP server and access the application through your web browser.

## Usage Guidelines
- Users can navigate through the application using the sidebar.
- Submissions can be made on the submissions page, where both formal and informal inputs are accepted.
- Scores can be viewed on the scores page after verification.
- Settings can be adjusted on the settings page.

## Contribution
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.