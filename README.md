# Goodreads

PHP wrapper to communicate with Goodreads API.

[![Latest Stable Version](https://poser.pugx.org/njt/good-reads/v/stable)](https://packagist.org/packages/njt/good-reads)  [![License](https://poser.pugx.org/njt/good-reads/license)](https://packagist.org/packages/njt/good-reads)

## Requirements

- PHP  >= 5.5.3

## Installation

```
composer require njt/good-reads
```

## Getting Started

Before using Goodreads API you must create a new application. Visit [signup form](http://www.goodreads.com/api/keys) for details.

Setup client:

``` php
$gr = new GoodReads('api_key');
```


## Examples

### Lookup books

You can lookup a book by ISBN, ID or Title:

```php
$gr->bookByISBN("ISBN");
$gr->book("id");
```

Search for books:

```php
$gr->searchBook("Search any think");
$gr->searchBookByName("Book Name");
$gr->searchBookByAuthorName('Author Name');
```

### Authors

Look up an author by their Goodreads Author ID:

```php
$author = $gr->authorByID("id");
```

Look up an author books by their Goodreads Author ID:

```php
$books = $gr->authorBooks("id");
```

Get Author ID by Author Name:

```php
$id = $gr->authorIDByName("Author Name");
```

Look up an author by name:

```php
$author = $gr->authorByName("Author Name");
```


Author series:

```php
$series = $gr->seriesByAuthor("id");
```

### Reviews

Get review details:

```php
$review = $gr->review('id');
```

Get User review details by Book:

```php
$review = $gr->userReviewByBook('userID', 'bookID');
```

### User

Get the user by id:

```php
$user = $gr->userInfoByID('id');
```

Get the user by username:

```php
$user = $gr->userInfoByUsername('username');
```

### Groups

Get group details:

```php
$group = $gr->group('id', 'sort');
```
The `sort` parameter is optional, and defaults to `title`.
One of `comments_count`, `title`, `updated_at`, `views`


Find group by Name:

```php
$groups = $gr->findGroup('group Name');
```

List the groups a given user is a member of:

```php
$groups = $gr->groupsOfUser('userID', 'sort');
```

The `sort` parameter is optional, and defaults to `members`.
One of `my_activity`, `members`, `last_activity`, `title`

List the group Members a given group id:

```php
$members = $gr->groupMembers('id');
```

## Contributions

You're welcome to submit patches and new features.

- Create a new branch for your feature of bugfix
- Add tests so it does not break any existing code
- Open a new pull request
- Check official [API documentation](http://www.goodreads.com/api)

## License

The MIT License (MIT)

Nijat Asadov, <nijatasadov@gmail.com>