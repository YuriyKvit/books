
## Test task with books

###### **Installation:**

`composer install`

`configure db in config.php`

`vendor\bin\phpunit tests`

###### **Requirements:**

PHP 7.1+<br>
MySQL 5+<br>

###### **Usage:**

- get all books: **GET**   `/api/books`
- get book:      **GET**   `/api/books/{id}`      
- create book    **POST**  `/api/books/create`    
- update book    **PUT**   `/api/books/update`    
- search book    **GET**   `/api/books/search?author=1&genres=1,2&name=test`
- get authors    **GET**   `/api/books/authors`
- get genres     **GET**   `/api/books/genres`