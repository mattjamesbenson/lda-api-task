# Lda API Task

## About the Project

This project is a small Laravel-based API integration that connects to the UK government Apprenticeship API and retrieves vacancy data. All data retrieved is then stored in the linked database.

The command will display how many vacancies are within proximity to the given postcode. Duplicate vacancy references are displayed and not stored in the database.

Handles unsuccessful API responses with error logging, and utilises TDD to ensure the Apprenticeship API Service is behaving as expected after future changes.

I've specifically tailored app/Http/Controllers/SearchController.php to work with large amounts of data in the local database. A search will be a lot more efficient than calling ->get() and loading all records into memory.

This has been specifically built for Lda.

---

## Requirements

- PHP >= 8.4
- Composer
- MySQL

---

## Setup Instructions

### 1. Clone the repository

git clone https://github.com/mattjamesbenson/lda-api-task

cd lda-api-task

### 2. Install dependencies

Make sure your CLI PHP version is 8.4 before running:

composer install

### 3. Configure .env

Update the local database and API credentials in a newly created .env (use .env.example for reference). 
Please utilise your own API key.

APPRENTICESHIP_UKPRN=UKPRN_HERE

APPRENTICESHIP_API_KEY=YOUR_KEY_HERE

APPRENTICESHIP_API_URL=https://api.apprenticeships.education.gov.uk

### 4. Generate application key

php artisan key:generate

### 5. Run database migrations

php artisan migrate

### 6. Serve the application

php artisan serve

- OR for Valet:

  valet link

  valet secure

- OR for Herd:

  herd link

  herd secure

### 7. Run unit tests

php artisan test

### 8. Fetch apprenticeships and store in database

php artisan apprenticeship:fetch

To call with a postcode:

php artisan apprenticeship:fetch M281NE

To add a proximity (in km):

# Returns no vacancies
php artisan apprenticeship:fetch M281NE --radius=10

# Returns 5 vacancies
php artisan apprenticeship:fetch M281NE --radius=250

# Returns 14 vacancies
php artisan apprenticeship:fetch M281NE --radius=400

Note: The postcode and --radius parameters **only filter results for display purposes**. Data is still imported. There must be no spaces in your provided postcode.

---

Many thanks for taking the time to set up my project and review my API-based solution. This was a great task to complete. I look forward to receiving feedback.

Matt Benson
