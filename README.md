# Voucher Generator

This is a Laravel project set up with Docker for easy development and deployment.

## Prerequisites

Make sure you have the following installed on your machine:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## Getting Started

Follow these steps to get your Laravel application up and running:

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd <project-directory>

2. php artisan key:generate

3. cp .env.example .env

4. docker-compose build

5. composer install

6. docker-compose up -d

7. docker-compose exec app php artisan migrate

8. http://localhost:8080/generate
