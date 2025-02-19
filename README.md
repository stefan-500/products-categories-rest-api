# Laravel REST API for Product Management

This project is a REST API built with Laravel, designed to handle product management, including parsing a CSV file, managing product data and exporting HTTP response data to a new CSV file.

## Features

 - **REST API implementation**
 - **CSV file processing and import**
 - **CRUD implementation**
 - **Category-based product filtering**
 - **JSON data export to CSV file**

## CSV File Processing

The CSV file for processing needs to be located in the project's root folder.  
To parse the CSV file and store data in the database, run the following Artisan command:
```
php artisan app:parse-csv-file
```
## API Usage and Testing

For testing API functionalities, use **Postman** or any API testing tool.

- Base URL:
```
http://127.0.0.1:8000/api/
```
- Available Endpoints:

| Method | Endpoint                                    | Description                                  |
|--------|---------------------------------------------|----------------------------------------------|
| **GET**    | `/api/products`                         | Fetch products (general data)                |
| **GET**    | `/api/products-specific`                | Fetch products (product specific data)       |
| **GET**    | `/api/category-products`                | Fetch products by  category                  |
| **GET**    | `/api/categories`                       | Fetch categories                             |
| **PUT**    | `/api/categories/{category}`            | Update category name                         |
| **DELETE** | `/api/categories/{category}`            | Delete a category                            |
| **PUT**    | `/api/products/{product}`               | Update a product                             |
| **DELETE** | `/api/products/{product}`               | Delete a product                             |
| **POST**   | `/api/category-products`                | Generate a CSV of products for a category    |

- Few examples of **HTTP request parameters**  are located in **routes/api.php** as comments above each route definition.

## ER Diagram

![ER Diagram](public/img/er_diagram.png)

## Possible Improvements

- **Implement soft deletion and update migration tables with proper ``onDelete`` constraints**
- **Modify ``ProductController@update`` to allow updating a product's category and manufacturer**
- **Implement rate limiting for API endpoints**

## License

This project is open-source and available under the [MIT License](LICENCE).