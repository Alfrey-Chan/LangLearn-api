# Language Learning App - Laravel API Backend

## Project Overview
Building a Laravel API backend for a multi-language learning application supporting Japanese, Chinese, and English speakers/learners.

### Current Status: Quiz System Foundation Complete - AI Integration Next 🚧

## Project Requirements

### Core Features
1. **Vocabulary System**
   - Vocabulary sets with detailed word/phrase explanations
   - Usage examples and contextual dialogues
   - Premade sets (Pet terms, SNS phrases, Dating vocabularies, etc.)
   - Custom user-created sets

2. **User Notes System**
   - Save vocabulary sets (Premade vs Custom categories)
   - Search for entries, generate new ones via AI if not found
   - Generated entries can be saved to Notes

3. **Quiz System**
   - Generate quizzes from selected vocabulary sets or mixtures
   - AI integration (Claude, OpenAI) for quiz generation
   - Track quiz completion and scores

4. **User Features**
   - Firebase Authentication (Google, LINE, Facebook login)
   - Favorites system for quick access
   - Rating system for entries and vocabulary sets
   - User statistics: day streaks, total quizzes, average scores

5. **Multi-language Support**
   - Japanese ↔ English
   - Chinese ↔ English  
   - Chinese ↔ Japanese
   - Scalable for future language additions

6. **Future Features**
   - Sidebar chat system for native speaker conversations

### Sample Data Structure
Located at: `/Users/alfreychan/Desktop/LangLearn/data/enhanced_sns_vocab.json`

Key data fields per vocabulary entry:
- `id`, `word`, `hiragana`, `romaji`
- `meanings[]` with detailed explanations
- `usage_examples[]` with translations
- `contextual_usage[]` with dialogues
- `additional_notes`, voting system (`upvotes`, `downvotes`, `views`)

## Architecture Decisions

### Backend Type: Laravel API
- **What it is**: REST API providing data endpoints to frontend
- **Why Laravel**: Robust framework with excellent API features, authentication, database handling
- **Authentication**: Firebase Auth integration (no custom login system needed)

### Database Schema Design

#### Core Tables:
```
users - Firebase UID + profile data
languages - Japanese, English, Chinese with metadata  
vocabulary_sets - Premade & custom collections
vocabulary_entries - Individual words/phrases with all language data
user_notes - Saved vocabulary sets per user
user_favorites - Favorited entries/sets  
user_statistics - Streaks, quiz stats, scores
quizzes - Quiz instances and results
ratings - User ratings for entries/sets
```

#### Key Relationships:
- User has many Notes (saved vocab sets)
- User has many Favorites  
- VocabularySet belongs to Language, has many Entries
- Entry belongs to VocabularySet and Language
- Polymorphic rating system for entries and sets

### Laravel Best Practices to Implement:
1. **API Resources** - Clean JSON responses for frontend
2. **Form Requests** - Validation for all user inputs  
3. **Service Classes** - Business logic (quiz generation, AI integration)
4. **Repository Pattern** - For complex queries
5. **Database Seeders** - Transform JSON data into database
6. **Firebase Auth Middleware** - Secure API endpoints
7. **Localization** - Proper multi-language support

## Implementation Plan

### Phase 1: Foundation ✅ COMPLETED
- [x] Set up Laravel project structure
- [x] Design and create database migrations
- [x] Build Eloquent models with relationships
- [x] Implement Firebase authentication integration

### Phase 2: Core API ✅ COMPLETED
- [x] Create API controllers and routes
- [x] Transform JSON data with seeders
- [x] Build user notes and favorites system
- [x] Implement rating system
- [x] Example voting system with SentenceExample/DialogueExample models
- [x] Category and Tag taxonomy system
- [x] Dynamic multi-file JSON seeding

### Phase 3: Firebase Authentication ✅ COMPLETED
- [x] Install Firebase Admin SDK for Laravel
- [x] Configure Firebase credentials and service account
- [x] Create Firebase Auth middleware for API protection
- [x] Update User model to work with Firebase UIDs
- [x] Create user registration/login endpoints
- [x] Protect API routes with Firebase auth middleware
- [x] Test authentication flow

### Phase 4: AI Quiz System (CURRENT) 🚧

#### ✅ **COMPLETED - Quiz Foundation**:
- [x] **Quiz Database Schema**: Complete with proper relationships
  - `quizzes` - Quiz instances with vocabulary_set_id, title, version (decimal 3,1)
  - `questions` - Quiz questions with JSON item storage (with casts)
  - `quiz_results` - User quiz results with percentage (decimal 3,1 for 99.5% precision)
  - Foreign key relationships properly set up

- [x] **Basic Quiz Models**: Quiz, Question, QuizResult with Eloquent relationships
- [x] **Dynamic Quiz Seeding**: From JSON files with filename matching (vocab_set.json → vocab_set_quiz.json)  
- [x] **Quiz Retrieval API**: `GET /take-quiz/{id}` endpoint with questions loading
- [x] **Proper JSON Handling**: Using Laravel casts for complex question data structures

#### 🚧 **IN PROGRESS - AI Integration**:
- [ ] **AI Service Setup**: Configure Claude/OpenAI API integration for dynamic question generation
- [ ] **Question Generation Logic**: 
  - Multiple choice (word → definition, translation)
  - Fill-in-blank (contextual usage from dialogue examples)
  - Difficulty scaling based on vocabulary set difficulty level

- [ ] **Quiz Submission System**:
  - `POST /quizzes/{id}/submit` - Submit quiz responses and calculate score
  - Automatic scoring logic with percentage calculation (stored as decimal 3,1)
  - User progress tracking and statistics updates

- [ ] **Advanced Quiz Features**:
  - `POST /quizzes/generate` - AI-generated quiz from vocabulary set
  - `GET /user/quiz-history` - User's quiz statistics and progress
  - Smart answer validation with explanation feedback

### Phase 5: Advanced Features & Polish
- [ ] AI integration for generating new vocabulary entries
- [ ] Advanced search functionality with filters
- [ ] User preferences and learning paths
- [ ] Analytics dashboard for learning progress

### Phase 6: Deployment
- [ ] Choose hosting platform (research needed)
- [ ] Set up production environment
- [ ] Configure CI/CD pipeline
- [ ] Performance optimization

## Technical Stack
- **Backend**: Laravel (PHP framework)
- **Database**: MySQL/PostgreSQL
- **Authentication**: Firebase Auth
- **AI Integration**: Claude/OpenAI APIs
- **Deployment**: TBD (user is new to hosting)

## Commands to Remember
```bash
# Create Laravel project
composer create-project laravel/laravel language-learning-api

# Common Laravel commands (to learn later)
php artisan make:model ModelName -m  # Create model with migration
php artisan make:controller ControllerName --api  # Create API controller  
php artisan migrate  # Run migrations
php artisan db:seed  # Run seeders
```

## Current Questions/Decisions Needed:
1. Hosting platform choice (AWS, DigitalOcean, Vercel, etc.)
2. Database preference (MySQL vs PostgreSQL)
3. Frontend integration details
4. AI API choice and implementation approach

## Learning Goals for User:
- Laravel fundamentals (migrations, models, controllers)
- API development best practices
- Database design and relationships
- Authentication with external providers
- Deployment and hosting concepts

---
## Firebase Authentication Setup

### Key Components:
- **Firebase Admin SDK**: Installed via `composer require kreait/firebase-php`
- **Service Account**: JSON credentials file for server-side auth
- **Middleware**: Custom `FirebaseAuth` middleware for route protection
- **User Model**: Updated with `firebase_uid` column for user identification
- **AuthController**: Handles Firebase token verification and user creation

### Current API Endpoints:
- `POST /api/auth/firebase-login` - Verify Firebase token and login/register user
- `GET /api/test` - Test Firebase connection
- All existing vocabulary/language endpoints remain public
- Voting/rating endpoints ready for protection with `->middleware('firebase.auth')`

### Database Changes:
- Added `firebase_uid` column to `users` table
- Created `SentenceExample` and `DialogueExample` models with voting
- Created `ExampleVote` model for user-specific voting
- Added Category/Tag taxonomy system

## Next Steps: AI Quiz System Implementation

### Immediate Tasks:
1. **Design Quiz Database Schema** - Create migrations for quiz tables
2. **Set up AI Service** - Configure Claude/OpenAI API integration
3. **Create Quiz Models** - Build Eloquent models with relationships
4. **Implement Quiz Controller** - Handle quiz generation and submission
5. **Test Quiz Flow** - Ensure questions are generated correctly and scores calculated

### Quiz Generation Strategy:
- **Context-Aware**: Use vocabulary entries from selected sets as source material
- **Difficulty Scaling**: Match questions to user's proficiency level
- **Question Variety**: Mix multiple choice, translation, and contextual questions
- **Smart Distractors**: Generate plausible wrong answers for multiple choice
- **Immediate Feedback**: Provide explanations for correct/incorrect answers

---
*Last updated: 2025-08-19*
*Ready to implement AI-powered quiz system with Claude/OpenAI integration*