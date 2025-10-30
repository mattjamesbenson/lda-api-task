# Lda API Task

## About the Project

This project is a small Laravel-based API integration that connects to the UK government Apprenticeship API and retrieves vacancy data based on proximity to a postcode. The data retrieved is then stored in the linked database. Handles unsuccessful API responses with error logging, and utilised TDD to ensure the Apprenticeship API Service is behaving as expected.

I've specifically tailored app/Http/Controllers/SearchController.php to work with large amounts of data in the local database. A search will be a lot more efficient than calling ->get() and loading all records into memory.

This has been specifically built for Lda.

---

## Requirements

-   PHP >= 8.4
-   Composer
-   MySQL

---

## Setup Instructions

### 1. Extract the zipped file into an appropiate folder location (alternatively, clone the repository with the command below)

git clone https://github.com/mattjamesbenson/lda-api-task

CD into the project

### 2. Install dependencies

Run:

composer install 

Ensure you are on PHP 8.4 before running this.

### 3. Configure .env

Update the local database and API credentials. Please utilise your own API key.

APPRENTICESHIP_UKPRN=UKPRN_HERE
APPRENTICESHIP_API_KEY=YOUR_KEY_HERE
APPRENTICESHIP_API_URL=https://api.apprenticeships.education.gov.uk

### 4. Generate application key (not required for this project but good practise to include it)

php artisan key:generate

### 5. Run database migrations

php artisan migrate

### 6. Serve the application (Valet/Herd setups are optional and not required for this project)

php artisan serve

-   OR for Valet

    valet link

    valet secure

-   OR for Herd

    herd link
    
    herd secure

### 7. Run unit tests to confirm API is responding as expected

php artisan test

### 8. Run the below command (fetches apprenticeships from the API and stores them in the database)

php artisan apprenticeship:fetch

To call with a postcode, use:

    php artisan apprenticeship:fetch M281NE

To add a proximity, use (value passed in is in km):

    php artisan apprenticeship:fetch M281NE --radius=2500

Make sure there are no spaces in the postcode.

---

Many thanks for taking the time to set up my project and take the chance to view my skillset through a small API based project. This was a great little task to complete. I look forward to receiving feedback.

Matt Benson