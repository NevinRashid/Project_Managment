# Secure & Collaborative Project Management System API

This repository contains the backend API for a comprehensive project management system designed to facilitate secure collaboration among users.

## Project Overview

The core idea is to build a robust API that allows users to collaborate on projects, assign tasks, track progress, securely share files, and manage notifications effectively. The system places a strong emphasis on **security**, **performance**, **scalability**, and **code quality**, enforced through comprehensive testing.

## Key Features & Requirements

This project aims to implement the following core features and demonstrate proficiency in these areas of Laravel development:

1.  **Authentication & Authorization:**
    *   Implementation using Laravel Sanctum for API authentication.
    *   Utilizing Laravel Policies and Gates for fine-grained authorization control.
    *   Integration with Spatie Permissions package.
    *   Policies. 

2.  **Advanced Eloquent Relationships:**
    *   Proper use of `belongsTo`, `hasMany`, `belongsToMany` relationships.
    *   Implementation of `Polymorphic Relationships` (e.g., for comments, attachments).

3.  **Comprehensive Eloquent Features:**
    *   Implementing `Accessors` and `Mutators`.
    *   Defining and using `Scopes` (e.g., `scopeActiveProjects`, `scopeOverdueTasks`).
    *   Leveraging `withCount` for relationship counts.
    *   Using `whereHas` for querying relationships.
    *   Understanding and using `wasRecentlyCreated` and `isDirty`.
    *   Strategic implementation of `Eager Loading` and `Lazy Eager Loading` to solve the N+1 problem.

4.  **Performance & Efficiency:**
    *   Implementing `Caching` strategies.
    *   Utilizing `Queues` for handling background tasks (e.g., sending emails).
    *   Integrating `Sending Emails`.

5.  **Data Validation:**
    *   Strict input validation using `Form Requests` for all incoming data.

6.  **Robust Security Measures:**
    *   Preventing `SQL Injection`.
    *   Implementing `Secure File Uploads` (storing outside public directory, validation, unique naming).
    *   Applying `Rate Limiting` to sensitive endpoints.
    *   Implementing `Input Sanitization` to prevent XSS attacks.
    *   Using strong `Password Hashing`.
    *   Understanding the context of `XSS` and `CSRF` in an API-only project.

7.  **Event-Driven Architecture:**
    *   Using `Events`, `Listeners`, and `Observers` to decouple application logic.

8.  **Extensive Testing:**
    *   Writing comprehensive `Unit Tests` for models, services, observers, etc.

9.  **Console Commands:**
    *   Creating custom `Console Commands` for automation (e.g.updating task statuses).

10. **Custom Stubs:**
    *   Customizing default framework stubs (e.g., for Models, Controllers, Form Requests, service class).

## Database Schema (Migrations)

The database design includes the following tables and relationships:

*   **`users`**: `id`, `name`, `email`, `password`, `role` (`admin`, `project_manager`, `member`)
*   **`teams`**: `id`, `name`, `owner_id` (Foreign key to `users.id`). A user can belong to multiple teams (`team_user` pivot). Each team has one owner.
*   **`projects`**: `id`, `team_id` (FK to `teams.id`), `name`, `description`, `status`, `due_date`, `created_by_user_id` (FK to `users.id`). Belongs to a team, created by a user. Users can be assigned to projects (`project_user` pivot).
*   **`tasks`**: `id`, `project_id` (FK to `projects.id`), `assigned_to_user_id` (FK to `users.id`, nullable), `name`, `description`, `status`, `priority`, `due_date`. Belongs to a project, can be assigned to a user.
*   **`comments`**: `id`, `user_id` (FK to `users.id`), `commentable_id`, `commentable_type`, `content`. `Polymorphic Relationship` to `projects` and `tasks`. Belongs to a user.
*   **`attachments`**: `id`, `path`, `disk`, `attachable_id`, `attachable_type`, `file_name`, `file_size`, `mime_type`. `Polymorphic Relationship` to `projects`, `tasks`, and `comments`.
*   **`notifications`**: `id`, `user_id` (FK to `users.id`), `type`, `data`, `read_at`. Belongs to a user (for internal notifications).
*   **`team_user`**: (Pivot table) `user_id` (FK), `team_id` (FK). Many-to-Many relationship linking users to teams.
*   **`project_user`**: (Pivot table) `user_id` (FK), `project_id` (FK). Many-to-Many relationship linking users to projects.

## Getting Started

To get a local copy up and running, follow these steps:

1.  Clone the repository.
    ```bash
    git clone https://github.com/NevinRashid/Project_Managment.git
    ```
2.  Navigate into the project directory.
    ```bash
    cd projects-management
    ```
3.  Install PHP dependencies.
    ```bash
    composer install
    ```
4.  Copy the environment file and configure your database settings.
    ```bash
    cp .env.example .env
    php artisan key:generate
    # Edit .env with your database credentials
    ```
5.  Run database migrations.
    ```bash
    php artisan migrate
    ```
6.   Seed the database with test data.
    ```bash
    php artisan db:seed
    ```
7.  Start the Laravel development server.
    ```bash
    php artisan serve
    ```
8.  (If Sanctum is used) You might need to issue API tokens for users.

## Key Concepts Emphasized (Notes)

*   **Plan First:** Architectural planning (relationships, data flow, permissions) is crucial before writing code.
*   **SOLID Principles:** Strive to apply SOLID design principles wherever possible.
*   **Code Quality:** Focus on clean, readable, and maintainable code.
*   **Error Handling:** Implement robust error handling and provide clear API response messages.
*   **Security First:** Treat security as a top priority in every step of development.
*   **Manage Complexity:** Break down the naturally complex project into smaller, manageable tasks.
## API Usage (Postman Collection)

To help you easily explore and test the API endpoints, a Postman collection is provided.

1.  **Download/Import the Collection:**
    You can download the Postman collection from the following link:

    **[--- https://www.postman.com/nevinrashid/new-workspace/collection/euz6izu/projects-management ---]**

    Import this collection into your Postman application.

2.  **Configure Environment Variables:**
    Once imported, set up a Postman Environment for your local development. The most crucial variable is likely the `base_url`. Set this to the URL where your Laravel development server is running (e.g., `http://localhost:8000`).

3.  **Authentication:**
    Most API endpoints will require authentication using the Sanctum token.
    *   Find the login endpoint in the collection.
    *   Send a request with valid user credentials to receive an API token.
    *   This token should then be used in subsequent requests by adding an `Authorization` header with the value `Bearer <your_token>`. You can often automate this in Postman's collection settings or by using test scripts to store the token in an environment variable after login.

4.  **Explore Endpoints:**
    Navigate through the folders and requests in the collection to understand the available endpoints for managing users, teams, projects, tasks, comments, attachments, and notifications.

Make sure your Laravel development server (`php artisan serve`) is running before sending requests from Postman.

## Key Concepts Emphasized (Notes)

*   **Plan First:** Architectural planning (relationships, data flow, permissions) is crucial before writing code.
*   **SOLID Principles:** Strive to apply SOLID design principles wherever possible.
*   **Code Quality:** Focus on clean, readable, and maintainable code.
*   **Error Handling:** Implement robust error handling and provide clear API response messages.
*   **Security First:** Treat security as a top priority in every step of development.
*   **Manage Complexity:** Break down the naturally complex project into smaller, manageable tasks.

## Contributing

Contributions are welcome! If you find issues or have suggestions, please open an issue or submit a pull request.

## License

This project is licensed under the MIT License.
