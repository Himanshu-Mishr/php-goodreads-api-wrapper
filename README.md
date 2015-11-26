# php-goodreads-api-wrapper
Php class that contains methods that fetches data and return them as json, even if API response is in xml or json.

## Prequistive
Goodreads API key

## Configuration array
You need to pass configuration array to the methods.
`

            $option = array(
                'url' => "https://www.goodreads.com/search/index.xml",
                'key_var_name' => "key",
                'key' => $GOODREADS_KEY,
                'query_var_name' => "q",
                'query' => $q,
            );

`
## Methods:
Description about method