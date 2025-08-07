# Language Learning App - Laravel API Backend

## Project Overview
Building a Laravel API backend for a multi-language learning application supporting Japanese, Chinese, and English speakers/learners.

### Current Status: Planning & Architecture Phase

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

### Phase 1: Foundation
- [ ] Set up Laravel project structure
- [ ] Design and create database migrations
- [ ] Build Eloquent models with relationships
- [ ] Implement Firebase authentication integration

### Phase 2: Core API
- [ ] Create API controllers and routes
- [ ] Transform JSON data with seeders
- [ ] Build user notes and favorites system
- [ ] Implement rating system

### Phase 3: Advanced Features  
- [ ] AI integration for generating new entries
- [ ] Quiz generation and tracking system
- [ ] User statistics tracking
- [ ] Search functionality

### Phase 4: Deployment
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
*Last updated: 2025-08-07*
*Context preservation for Claude autocompact*