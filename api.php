<?php

class API {

    protected $GOODREADS_KEY = "GOODREADS_API_KEY";

    /**
     * Get search results from goodreads
     * @param string $querytext
     */
    public function collect_bookID_querytext($querytext = "") {

        // make config before send.
        $option = array(
            'url' => "https://www.goodreads.com/search/index.xml",
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "q",
            'query' => $querytext
        );

        $arr = $this->fetch_data($option);

        $collect_bookID = array();

        if ($this->isAssoc($arr ['search'] ['results'] ['work'])) {
            // only one work array
            array_push($collect_book_id, $arr ['search'] ['results'] ['work'] ['best_book'] ['id']);
        } else {
            // multiple work array
            $start = intval($arr ['search'] ['results-start']) - 1;
            $end = intval($arr ['search'] ['results-end']);

            for ($idx = $start; $idx < $end; $idx ++) {

                $book_id = $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['id'];
                array_push($collect_book_id, $book_id);

                $item_detail = array(
                    'book_title' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['title'],
                    'book_id' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['id'],
                    'average_rating' => $arr ['search'] ['results'] ['work'] [$idx] ['average_rating'],
                    'image_url' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['image_url'],
                    'author_name' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['author'] ['name'],
                    'author_id' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['author'] ['id']
                );
                array_push($search_details, $item_detail);
            }
        }
    }

    /**
     *
     * @param type $book_id
     * @return boolean|array
     */
    public function get_bookDetails_bookID($book_id = "") {

        // Return false if book_id is empty
        if (empty($book_id)) {
            return FALSE;
        }
        $url = "https://www.goodreads.com/book/show/$book_id";

        $option = array(
            'url' => $url,
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "format",
            'query' => "xml",
        );

        $arr = $this->fetch_data($option);

        $book_id = $arr['book']['id'];
        $book_title = $arr['book']['title'];

        $isbn = $arr['book']['isbn'];
        $isbn13 = $arr['book']['isbn13'];

        $imagePriceArray = array('maxImage' => "", 'pricelist' => array());
        if (!empty($isbn)) {
            $imagePriceArray = $this->get_best_image($isbn);
        }

        $image_url = $imagePriceArray['maxImage'];
        $pricelist = $imagePriceArray['pricelist'];

        $publication_year = $arr['book']['publication_year'];
        $publication_month = $arr['book']['publication_month'];
        $publication_day = $arr['book']['publication_day'];
        $publisher = $arr['book']['publisher'];

        $inlanguage = $arr['book']['language_code'];

        $description = $arr['book']['description'];

        $average_rating = $arr['book']['average_rating'];

        $no_of_page = $arr['book']['num_pages'];
        $format = $arr['book']['format'];

        $author = array();
        if ($this->isAssoc($arr['book']['authors']['author'])) {
            $auth = array(
                'author_name' => $arr['book']['authors']['author']['name'],
                'author_average_rating' => $arr['book']['authors']['author']['average_rating'],
            );
            array_push($author, $auth);
        } else {
            $end = count($arr['book']['authors']['author']);
            for ($idx = 0; $idx < $end; ++$idx) {
                $auth = array(
                    'author_name' => $arr['book']['authors']['author'][$idx]['name'],
                    'author_average_rating' => $arr['book']['authors']['author'][$idx]['average_rating'],
                );
                array_push($author, $auth);
            }
        }

        $reviewWidget = $arr['book']['reviews_widget'];

        $search_data = array(
            'book_id' => $book_id,
            'book_title' => $book_title,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'image_url' => $image_url,
            'publication_year' => $publication_year,
            'publication_month' => $publication_month,
            'publication_day' => $publication_day,
            'publisher' => $publisher,
            'inlanguage' => $inlanguage,
            'description' => $description,
            'average_rating' => $average_rating,
            'no_of_page' => $no_of_page,
            'format' => $format,
            'author' => $author,
            'pricelist' => $pricelist,
            'reviewWidget' => $reviewWidget,
        );

        return $search_data;
    }

    /**
     * Get details for the author with its author_id
     * @param string $author_id
     * @return boolean|array
     */
    public function get_authorDetail_authorID($author_id = "") {

        // Return false if book_id is empty
        if (empty($author_id)) {
            return FALSE;
        }
        $url = "https://www.goodreads.com/author/show.xml";

        $option = array(
            'url' => $url,
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "id",
            'query' => $author_id,
        );

        $arr = $this->fetch_data($option);

        $author_name = $arr['author']['name'];
        $image_url = $arr['author']['large_image_url'];
        $gender = $arr['author']['gender'];
        $hometown = $arr['author']['hometown'];

        // collecting books
        $books = array();
        # todo : to get other authors.{To reproduce it, run this api again author.show}.
        if ($this->isAssoc($arr['author']['books']['book'])) {
            $abook = array(
                'book_id' => $arr['author']['books']['book']['id'],
                'book_title' => $arr['author']['books']['book']['title'],
                'image_url' => $arr['author']['books']['book']['image_url'],

                'publisher' => $arr['author']['books']['book']['publisher'],
                'publication_year' => $arr['author']['books']['book']['publication_year'],
                'publication_month' => $arr['author']['books']['book']['publication_month'],
                'publication_day' => $arr['author']['books']['book']['publication_day'],
                'average_rating' => $arr['author']['books']['book']['average_rating'],
                'description' => $arr['author']['books']['book']['description'],
            );
            array_push($books, $abook);
        } else {
            $end = count($arr['author']['books']['book']);
            for ($idx = 0; $idx < $end; $idx++) {
                $abook = array(
                    'book_id' => $arr['author']['books']['book'][$idx]['id'],
                    'book_title' => $arr['author']['books']['book'][$idx]['title'],
                    'image_url' => $arr['author']['books']['book'][$idx]['image_url'],

                    'publisher' => $arr['author']['books']['book'][$idx]['publisher'],
                    'publication_year' => $arr['author']['books']['book'][$idx]['publication_year'],
                    'publication_month' => $arr['author']['books']['book'][$idx]['publication_month'],
                    'publication_day' => $arr['author']['books']['book'][$idx]['publication_day'],
                    'average_rating' => $arr['author']['books']['book'][$idx]['average_rating'],
                    'description' => $arr['author']['books']['book'][$idx]['description'],
                );
                array_push($books, $abook);
            }
        }

        //TODO:  collect user data as array();
        $author_data = array(
            'author_name' => $author_name,
            'image_url' => $image_url,
            'gender' => $gender,
            'hometown' => $hometown,
            'books_array' => $books,
        );

        return array(
            'author_data' => $author_data,
        );
    }

    /**
     * Get description for a book.
     * @param string $author_id
     * @param string $book_id
     * @return boolean|string
     */
    public function get_description($author_id = "", $book_id = "") {
        if (empty($author_id) || empty($book_id)) {
            return FALSE;
        }

        $option = array(
            'url' => "https://www.goodreads.com/author/show.xml",
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "id",
            'query' => $author_id,
        );

        $arr = $this->fetch_data($option);
        $end = count($arr['author']['books']['book']);

        $description = "";
        for ($idx = 0; $idx < $end; $idx++) {
            if ($arr['author']['books']['book'][$idx]['id'] == $book_id) {

                if (count($arr['author']['books']['book'][$idx]['description']) > 0) {
                    $description = $arr['author']['books']['book'][$idx]['description'];
                }
            }
        }
        return $description;
    }

    /**
     * Fetechs API Data
     * @param  arrary $option : Parameter required for fetching.
     * @return array
     */
    public function fetch_data($option) {

        $url = $option['url'];

        // KEY
        $key_var_name = $option['key_var_name'];
        $key = $option['key'];

        // Ud2hoal08T5FYoDfRRaDdg
        // Query
        $query_var_name = $option['query_var_name'];
        $query = $option['query'];

        // query to url friendly code.
        $query_to_url = urlencode($query);

        // assembling url
        $url = $url . "?" . $key_var_name . "=" . $key . "&" . $query_var_name . "=" . $query_to_url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        $sxml = curl_exec($ch);
        if (!$sxml) {
            echo curl_error($ch);
        }
        curl_close($ch);

        $arr = "";

        // Whatever be the fetched data, only json is returned
        // xml to json : I support Json.
        if ($this->isJson($sxml)) {
            $arr = json_decode($sxml, TRUE);
        } else {
            $xml = simplexml_load_string($sxml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            $arr = json_decode($json, TRUE);
        }
        return $arr;
    }

    /**
     * Returns all search result's book id max to 118
     * @param string search query
     * @return array of book ids
     */
    public function collect_bookID($q = "") {
        if (empty($q)) {
            return FALSE;
        }

        $bookID_bucket = array();
        $count = 1;
        $q = urlencode($q);
        $x = 0;
        while (true) {
            $q = $q . "&page=$count";

            $option = array(
                'url' => "https://www.goodreads.com/search/index.xml",
                'key_var_name' => "key",
                'key' => $GOODREADS_KEY,
                'query_var_name' => "q",
                'query' => $q,
            );

            $arr = $this->fetch_data($option);
            $present_start = intval($arr['search']['results-start']);
            $present_end = intval($arr['search']['results-end']);
            $total_results = intval($arr['search']['total-results']);
            $count = $count + 1;
            $final = $present_end - ($present_start - 1);

            // echo "start = $present_start | end = $present_end  | final = $total_results <br>";

            for ($index = 0; $index < $final && ($index + $present_start < $total_results); $index++) {
                array_push($bookID_bucket, $arr['search']['results']['work'][$index]['best_book']['id']);
                $x++;
            }
            if ($x > 20) {
                break;
            }
            if ($total_results == ($present_start + $index)) {
                break;
            }
        }

        return $bookID_bucket;
    }

    /**
     * Returns size of image on server without downloading it.
     * @param  string $url
     * @return float      size of file(url)
     */
    public function retrieve_remote_file_size($url) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    /**
     * Get better size of image for a book
     * @param  string $isbn
     * @return string       url
     */
    public function get_best_image($isbn = "") {

        if (empty($isbn)) {
            return false;
        }
        $isbn = urlencode($isbn);

        $url = "http://api.dataweave.in/v1/book_search/searchByIsbn/";

        $option = array(
            'url' => $url,
            'key_var_name' => "api_key",
            'key' => "DATA_WEAVE_API_KEYSS",
            'query_var_name' => "isbn",
            'query' => $isbn,
        );

        $arr = $this->fetch_data($option);


        $count = intval($arr['count']);

        $maxSize = 0;
        $maxImage = "";
        $pricelist = array();
        for ($idx = 0; $idx < $count; $idx++) {
            $temp = array(
                'source' => $arr['data'][$idx]['source'],
                'price' => $arr['data'][$idx]['price'],
            );
            array_push($pricelist, $temp);
            $x = intval($this->retrieve_remote_file_size($arr['data'][$idx]['thumbnail']));
            if ($x > $maxSize) {
                $maxImage = $arr['data'][$idx]['thumbnail'];
                $maxSize = $x;
            }
        }
        $data = array(
            'maxImage' => $maxImage,
            'pricelist' => $pricelist,
        );
        return $data;
    }

    /**
     * checks whether input string is json or not
     * @param  string  $string : Data fetched from API Server
     * @return boolean         TRUE/FALSE
     */
    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Checks whether an array is associative or can be index with number
     * @param array $arr
     * @return boolean
     */
    public function isAssoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Get books details based on ISBN
     * @param string $isbn
     * @return boolean|array
     */
    public function get_bookDetails_isbn($isbn = "") {
        if (empty($isbn)) {
            return FALSE;
        }

        $isbn = urlencode($isbn);

        $option = array(
            'url' => "https://www.goodreads.com/book/isbn",
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "isbn",
            'query' => $isbn,
        );


        $arr = $this->fetch_data($option);

        $author_id = "";
        $author_name = "";

        if ($this->isAssoc($arr['book']['authors']['author'])) {
            // one author
            $author_id = $arr['book']['authors']['author']['id'];
            $author_name = $arr['book']['authors']['author']['name'];
        } else {
            // Multiple authors : Store the first lucky one. :P
            $author_id = $arr['book']['authors']['author'][0]['id'];
            $author_name = $arr['book']['authors']['author'][0]['name'];
        }

        $data = array(
            'publication_year' => $arr['book']['publication_year'],
            'publication_month' => $arr['book']['publication_month'],
            'publication_day' => $arr['book']['publication_day'],
            'publisher' => $arr['book']['publisher'],
            'average_rating' => $arr['book']['average_rating'],
            'book_id' => $arr['book']['id'],
            'book_title' => $arr['book']['title'],
            'author_id' => $author_id,
            'author_name' => $author_name,
            'language_code' => $arr['book']['language_code'],
            'no_of_pages' => $arr['book']['num_pages'],
        );
        return $data;
    }

    /**
     * Get search results from goodreads
     * @param string $querytext
     */
    public function get_search_results($querytext = "") {

        // make config before send.
        $option = array(
            'url' => "https://www.goodreads.com/search/index.xml",
            'key_var_name' => "key",
            'key' => $GOODREADS_KEY,
            'query_var_name' => "q",
            'query' => $querytext
        );
        $arr = $this->fetch_data($option);

        $search_details = array();

        if ($this->isAssoc($arr ['search'] ['results'] ['work'])) {

            $item_detail = array(
                'book_title' => $arr ['search'] ['results'] ['work'] ['best_book'] ['title'],
                'book_id' => $arr ['search'] ['results'] ['work'] ['best_book'] ['id'],
                'average_rating' => $arr ['search'] ['results'] ['work'] ['average_rating'],
                'image_url' => $arr ['search'] ['results'] ['work'] ['best_book'] ['image_url'],
                'author_name' => $arr ['search'] ['results'] ['work'] ['best_book'] ['author'] ['name'],
                'author_id' => $arr ['search'] ['results'] ['work'] ['best_book'] ['author'] ['id']
            );
            array_push($search_details, $item_detail);
        } else {
            // multiple work array
            $start = intval($arr ['search'] ['results-start']) - 1;
            $end = intval($arr ['search'] ['results-end']);

            for ($idx = $start; $idx < $end; $idx ++) {

                $item_detail = array(
                    'book_title' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['title'],
                    'book_id' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['id'],
                    'average_rating' => $arr ['search'] ['results'] ['work'] [$idx] ['average_rating'],
                    'image_url' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['image_url'],
                    'author_name' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['author'] ['name'],
                    'author_id' => $arr ['search'] ['results'] ['work'] [$idx] ['best_book'] ['author'] ['id']
                );
                array_push($search_details, $item_detail);
            }
        }
        $data = array(
            'search_details' => $search_details,
        );
        return $data;
    }

}
?>