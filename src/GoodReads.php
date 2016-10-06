<?php

namespace Nicat\GoodReads;

use Exception;

/**
 * GoodReads
 *
 * PHP wrapper to communicate with GoodReads API.
 *
 * @package Nicat\GoodReads
 */
class GoodReads {

    /**
     * @var API KEY
     */
    protected $key;

    /**
     * Main Api Url
     */
    const DOMAIN = 'https://www.goodreads.com/';

    const authorShow = 'author/show/';
    const authorBooks = 'author/list/';
    const authorSearch = 'api/author_url/';
    const isbn = 'book/isbn/';
    const search = 'search/index.xml';
    const listGroups = 'group/list/';
    const groupMembers = 'group/members/';
    const findGroup = 'group/search.xml';
    const groupShow = 'group/show/';
    const reviews = 'review/show.xml';
    const reviewOfUser = 'review/show_by_user_and_book.xml';
    const seriesByAuthor = 'series/list/';
    const userShow = 'user/show/';


    /**
     * GoodReads constructor.
     *
     * @param string $key API KEY
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Generate URL for Request
     *
     * @param      $url
     * @param null $params
     * @param bool $append
     *
     * @return string
     */
    protected function generateURL($url, $params = null, $append = false)
    {
        $url = self::DOMAIN . $url;

        if (is_array($params))
            $query = (( ! empty($params)) ? http_build_query($params, '', '&') : '');
        else
            $query = urlencode($params);


        $general = "?format=xml&key=" . $this->key;

        if ($append)
            $url .= $general . "&" . $query;
        else
            $url .= $query . $general;

        return $url;
    }

    /**
     * Request via CURL to API URL
     *
     * @param string $url
     *
     * @return bool|null|string
     * @throws Exception
     */
    protected function curlRequest($url)
    {
        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/xml',
            ]);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);

            $body = substr($response, $info['header_size']);

            if (curl_errno($ch))
            {
                throw new Exception('Method failed: ' . $url);
            }
            if ($info['http_code'] === 401)
            {
                throw new Exception('Invalid API key : ' . $this->key);
            } elseif ($info['http_code'] === 404)
            {
                return false;
            }

            curl_close($ch);

            return $body;
        } else
        {
            throw new Exception('CURL library not loaded!');
        }

        return null;
    }

    /**
     * Convert XML to Object
     *
     * Used SimpleXMLElement
     *
     * @param $url
     *
     * @return \SimpleXMLElement
     */
    public function parseXML($url)
    {
        return @simplexml_load_string($this->curlRequest($url));;
    }

    /**
     * Get object by url and extra params
     *
     * @param string $url
     * @param array  $params
     * @param bool   $append
     *
     * @return \SimpleXMLElement
     */
    protected function getData($url, $params = [], $append = false)
    {
        $url = $this->generateURL($url, $params, $append);

        //echo $url . "\n";

        $object = $this->parseXML($url);

        return $object;
    }

    /**
     * Get Author ID By Author Name
     *
     * @param int $name Author Name
     *
     * @return int|null
     */
    public function getAuthorIDByName($name)
    {
        $get = $this->getData(static::authorSearch, $name);

        return $get->author ? (int)$get->author->attributes()[0] : null;
    }

    /**
     * Show Author By ID
     *
     * @param int $id Author ID
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function getAuthorByID($id)
    {
        $get = $this->getData(static::authorShow, $id);

        return $get ? $get->author : $get;
    }

    /**
     * Author Books
     *
     * @param     $id
     * @param int $page Page number. Default is 1
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function getAuthorBooks($id, $page = 1)
    {
        $params = [
            'id'   => $id,
            'page' => $page,
        ];
        $get = $this->getData(self::authorBooks, $params, true);

        return $get ? $get->author : $get;
    }

    /**
     * Get Author By Name
     *
     * @param string $name Author Name
     *
     * @return null|\SimpleXMLElement|\SimpleXMLElement[]
     */
    public function getAuthorByName($name)
    {
        /* Find author id */
        $id = $this->getAuthorIDByName($name);

        if ( ! $id)
            return null;

        /* Get Author by ID */
        $author = $this->getAuthorByID($id);

        return $author;
    }

    /**
     * Get Book By ISBN
     *
     * @param int|string $id ISBN number
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function getBookByISBN($id)
    {
        $get = $this->getData(self::isbn, $id);

        return $get ? $get->book : $get;
    }

    /**
     * Search Book for All filter
     *
     * @param string $name
     * @param array  $searchParams
     * @param int    $page
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function searchBook($name, $searchParams = [], $page = 1)
    {
        $params = [
            'q'    => $name,
            'page' => $page,
        ];

        if ( ! empty($searchParams))
        {
            $params = array_merge($params, $searchParams);
        }

        $get = $this->getData(static::search, $params, true);

        if ($get)
        {
            $search = $get->search;
            foreach ($search->children() as $child)
            {
                if (strpos($child->getName(), '-') !== false)
                {
                    $key = str_replace('-', '_', $child->getName());
                    $search->{$key} = $child;
                    echo($search->{$child->getName()});
                }
            }
        }

        return $get ? $search : $get;
    }

    /**
     * Search Books by Title
     *
     * @param string $name Book Name
     * @param int    $page
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function searchBookByName($name, $page = 1)
    {
        $params = [
            'search' => [
                'field' => 'title',
            ],
        ];

        return $this->searchBook($name, $params, $page);
    }

    /**
     * Search Books By Author Name
     *
     * @param string $title Author Name
     * @param int    $page  Page number. Default is 1
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function searchBookByAuthorName($title, $page = 1)
    {
        $params = [
            'search' => [
                'field' => 'author',
            ],
        ];

        return $this->searchBook($title, $params, $page);
    }

    /**
     * Get User groups
     *
     * @param  int   $id
     * @param string $sort One of 'my_activity', 'members', 'last_activity', 'title' ('members' will sort by number of members in the group)
     * @param int    $page
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function groupsOfUser($id, $sort = 'members', $page = 1)
    {
        $params = [
            'sort' => $sort,
            'page' => $page,
        ];

        $get = $this->getData(self::listGroups . $id . '.xml', $params, true);

        return $get ? $get->groups : $get;
    }

    /**
     * Get Group Members
     *
     * @param int         $id   Group ID
     * @param string|bool $search
     * @param string|bool $sort One of 'last_online', 'num_comments', 'date_joined', 'num_books', 'first_name'
     * @param int         $page Page number. Default is 1
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function groupMembers($id, $search = false, $sort = false, $page = 1)
    {
        $params = [
            'page' => $page,
        ];

        if ($search)
            $params['search'] = $search;

        if ($sort)
            $params['sort'] = $sort;

        $get = $this->getData(self::groupMembers . $id . '.xml', $params, false);

        return $get ? $get->group_users : $get;
    }

    /**
     * Find Group
     *
     * @param string $name Group name
     * @param int    $page Page number. Default is 1
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function findGroup($name, $page = 1)
    {
        $params = [
            'q'    => $name,
            'page' => $page,
        ];

        $get = $this->getData(self::findGroup, $params, true);

        return $get ? $get->groups : $get;
    }

    /**
     * Get Information about group by ID
     *
     * @param int    $id   Group ID
     * @param string $sort Field to sort topics by. One of 'comments_count', 'title', 'updated_at', 'views'
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function groupInfo($id, $sort = 'title')
    {
        $params = [
            'sort' => $sort,
        ];

        $get = $this->getData(self::groupShow . $id . 'xml', $params, true);

        return $get ? $get->group : $get;
    }

    /**
     * Get details of Review by ID
     *
     * @param int $id Review ID
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function review($id)
    {
        $params = [
            'id' => $id,
        ];
        $get = $this->getData(self::reviews, $params, true);

        return $get ? $get->review : $get;
    }

    /**
     * User review on given Book
     *
     * @param int $userId User ID
     * @param int $bookId Book ID
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function userReviewByBook($userId, $bookId)
    {
        $params = [
            'user_id' => $userId,
            'book_id' => $bookId,
        ];
        $get = $this->getData(self::reviewOfUser, $params, true);

        return $get ? $get->review : $get;
    }

    /**
     * Get Series of Author by ID
     *
     * @param int $id Author ID
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function seriesByAuthor($id)
    {
        $get = $this->getData(self::seriesByAuthor . $id . '.xml');

        return $get ? $get->series_works : $get;
    }

    /**
     * User information by User ID
     *
     * @param integer $id User id
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function userInfoByID($id)
    {
        $params = [
            'id' => $id,
        ];
        $get = $this->getData(self::userShow, $params, true);

        return $get ? $get->user : $get;
    }

    /**
     * User information by Username
     *
     * @param string $username Username
     *
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function userInfoByUsername($username)
    {
        $params = [
            'username' => $username,
        ];
        $get = $this->getData(self::userShow, $params, true);

        return $get ? $get->user : $get;
    }
}