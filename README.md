# San Francisco 49ers Leaderboard Backend

## Overview

This backend application is designed to support the web-based leaderboard for the San Francisco 49ers. It handles data retrieval, user authentication, and secure API interactions to display game scores and player statistics.

## Prerequisites

Make sure you have the following installed:

- PHP (>= 7.4)
- Composer
- A web server (e.g., Apache or Nginx)
- A database (e.g., MySQL or PostgreSQL)

## Installation

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/umjutt786/GameScoreLeaderboard.git
   cd GameScoreLeaderboard/backend
# Install Dependencies

Use Composer to install the necessary PHP packages:
    composer install

# Set Up Your Database

1. **Create a New Database**:
   - Create a new database in your database management system (e.g., MySQL, PostgreSQL).

2. **Run Migration Scripts**:
   - Run the migration scripts to set up the necessary tables. You can find the SQL scripts in the `/database` directory.

# Configuration

1. **Create a `.env` File**:
   - Create a `.env` file based on the provided `.env.example` file.

2. **Fill in Your Database Credentials**:

   ```plaintext
   DB_HOST=your_database_host
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_database_password

php -S localhost:8000 -t public

