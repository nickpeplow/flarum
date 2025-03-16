# Keywords Automation Dashboard

A dashboard for managing keywords and tags for the Flarum forum. This application follows the MVC (Model-View-Controller) design pattern.

## Directory Structure

The application is organized into the following directories:

```
automate/
│
├── Config/                 # Configuration files
│   └── App.php             # Application configuration
│
├── Controllers/            # Controller classes
│   ├── HomeController.php  # Handles dashboard/index page
│   └── KeywordController.php # Handles keyword management
│
├── Core/                   # Core framework files
│   ├── Autoloader.php      # Class autoloader
│   ├── Bootstrap.php       # Application bootstrap
│   ├── Controller.php      # Base controller class
│   ├── Helper.php          # Helper functions
│   └── Router.php          # URL router
│
├── Models/                 # Model classes
│   ├── Keyword.php         # Keyword model
│   └── Tag.php             # Tag model
│
├── Views/                  # View templates
│   ├── home/               # Home/dashboard views
│   ├── keywords/           # Keyword management views
│   ├── layouts/            # Layout templates
│   │   └── main.php        # Main layout
│   ├── partials/           # Partial templates
│   │   └── helpers.php     # View helper functions
│   ├── schema/             # Schema viewer views
│   └── tags/               # Tag management views
│
├── public/                 # Public assets (CSS, JS, images)
│   ├── css/                # CSS files
│   ├── js/                 # JavaScript files
│   └── img/                # Image files
│
├── index.php               # Application entry point
├── README.md               # This file
└── ...                     # Other files
```

## MVC Pattern

The application follows the Model-View-Controller (MVC) pattern:

- **Models**: Handle database operations and business logic
- **Views**: Display data to the user
- **Controllers**: Process user input and interact with Models and Views

## Key Components

### Router

The Router (`Core/Router.php`) manages URL routes and dispatches requests to the appropriate controller. Routes are defined in `index.php`.

### Controllers

Controllers handle user requests and interact with models to fetch or update data. They then render the appropriate view with the data.

### Views

Views contain the HTML templates that display data to the user. They use PHP for dynamic content.

### Models

Models handle database operations and business logic. They encapsulate database queries and data manipulation.

## How to Use

1. Access the dashboard at `/index.php`
2. Navigate through the application using the sidebar menu
3. Manage keywords and tags using the provided interfaces

## Development

To extend the application:

1. Add new Models in the `Models/` directory
2. Add new Controllers in the `Controllers/` directory
3. Add new Views in the `Views/` directory
4. Define routes in `index.php`

## Dependencies

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Flarum installation
- Bootstrap 5
- Font Awesome 6

## License

This project is licensed under the MIT License - see the LICENSE file for details. 