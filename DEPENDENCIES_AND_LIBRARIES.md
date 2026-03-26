# Table 1. List of Libraries and Dependencies Installed

## Frontend Dependencies

| Name | Version | Description |
|------|---------|-------------|
| HTML5 | 5.0 | Standard markup language for creating web pages and applications |
| CSS3 | 3.0 | Style sheet language used for describing the presentation of HTML documents |
| JavaScript (Vanilla) | ES6+ | Core scripting language for interactive frontend functionality without framework dependencies |
| KaTeX | 0.16.10 | Fast and reliable math typesetting library for rendering LaTeX mathematical expressions |
| Auto-render (KaTeX plugin) | 0.16.10 | Automatic rendering of math in HTML elements using KaTeX |
| SVG | - | Scalable vector graphics format for creating interactive visual elements (gaze-tracking logo) |

## Backend Dependencies

| Name | Version | Description |
|------|---------|-------------|
| PHP | 8.0+ | Server-side scripting language for backend API development and business logic |
| PDO (PHP Data Objects) | - | Database abstraction layer for secure database connectivity using prepared statements |
| MySQL | 5.7+ | Relational database management system for persistent data storage (users, proofs, submissions) |
| Apache/PHP-FPM | - | Web server and FastCGI process manager for running PHP applications |

## External APIs and Services

| Name | Version | Description |
|------|---------|-------------|
| DeepSeek API | Latest | Large Language Model API for natural language processing, proof conversion, and tutoring feedback |
| Lean4 | 4.0+ | Formal verification language and proof checker for mathematical proof validation |
| Lake (Lean Build Tool) | - | Build system for compiling and managing Lean4 projects |

## Development Tools

| Name | Version | Description |
|------|---------|-------------|
| Git | 2.30+ | Version control system for source code management and collaboration |
| GitHub | - | Cloud-based repository hosting for code storage and deployment integration |
| Visual Studio Code | Latest | Lightweight code editor with syntax highlighting, debugging, and extension support |
| Composer | 2.0+ | PHP dependency manager for managing server-side packages and autoloading |
| npm | 8.0+ | JavaScript package manager for managing frontend dependencies (for build tools) |
| Node.js | 14.0+ | JavaScript runtime environment for running build and development tools |

## Database Schema Components

| Name | Version | Description |
|------|---------|-------------|
| MySQL Database | 5.7+ | Core database engine supporting ACID transactions and JSON data types |
| InnoDB | - | Storage engine providing transactional support and FOREIGN KEY constraints |
| JSON Support | - | Native JSON data type support for storing arrays (tags, prerequisites, related_theorems) |

## Authentication & Security

| Name | Version | Description |
|------|---------|-------------|
| bcrypt | - | Password hashing function for secure credential storage |
| PHP Sessions | - | Built-in session management for maintaining user authentication state |
| SSL/TLS | Latest | Cryptographic protocols for secure HTTPS communication |

## File Format Support

| Name | Version | Description |
|------|---------|-------------|
| PDF.js | Latest | Library for PDF generation and rendering of completed proof documents |
| JSON | Standard | Data interchange format for API communication and configuration storage |
| CSV | Standard | Comma-separated values format for data export functionality |
| LaTeX | Standard | Document markup format for mathematical notation in proofs |

## Styling & Animation Libraries

| Name | Version | Description |
|------|---------|-------------|
| CSS Animations | 3.0 | Native CSS for floating particle animations and interactive transitions |
| CSS Keyframes | 3.0 | Animation timing control for smooth visual effects (particles, gaze-tracking) |
| CSS Grid & Flexbox | 3.0 | Modern layout management for responsive interface design |
| Inter Typography | Latest | High-quality system font for consistent, professional text rendering |

## Deployment & Hosting

| Name | Version | Description |
|------|---------|-------------|
| Render | Latest | Cloud hosting platform for deploying PHP backend and serving frontend files |
| Docker | Latest | Containerization platform for standardized deployment environments |
| render.yaml | - | Configuration file for Render deployment automation |

## Development Environment

| Name | Version | Description |
|------|---------|-------------|
| localhost:8080 | - | Local PHP development server for testing during development |
| PHPMyAdmin | Latest | Web-based MySQL administration interface for database management |
| Browser DevTools | Built-in | Developer tools for JavaScript debugging and performance profiling (Chrome, Firefox, Edge) |

## Supporting Utilities

| Name | Version | Description |
|------|---------|-------------|
| Math.js | Latest | Comprehensive mathematics library for mathematical expression parsing and evaluation |
| Date/Time APIs | ES6+ | Native JavaScript Date and Time functionality for tracking proof submissions and modifications |
| localStorage | HTML5 | Client-side storage API for maintaining user workspace state between sessions |
| sessionStorage | HTML5 | Session-based storage for temporary proof data and user information |

## System Architecture Components

| Name | Version | Description |
|------|---------|-------------|
| RESTful API | Standard | Architectural style for designing stateless API endpoints for client-server communication |
| MVC Pattern | - | Model-View-Controller design pattern organizing backend services and database models |
| Service Layer | - | Abstract service classes (DeepSeekService, ProofTutorService, NaturalLanguageToLeanConverter) |

---

## Summary

The AXIO Real Analysis Theorem Proving System integrates **44+ core technologies and dependencies** across frontend, backend, and external services. The stack prioritizes:

- **Simplicity**: Vanilla JavaScript frontend (no framework overhead)
- **Reliability**: Prepared statements and bcrypt for security
- **Scalability**: Render cloud hosting with containerization support
- **Functionality**: DeepSeek AI for linguistic processing and Lean4 for formal verification
- **User Experience**: KaTeX for mathematical rendering and CSS animations for interactivity

All dependencies are production-ready and actively maintained as of March 2026.
