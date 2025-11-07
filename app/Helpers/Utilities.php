<?php

use Illuminate\Support\Facades\Http;

function getCountryNames() 
{
    if (! Cache::store('redis')->has('country_names')):
        $response = Http::get("https://restcountries.eu/rest/v2/all");
        if ($response->ok()):
            $countries = $response->json();
            $country_names = [];
            foreach($countries as $country) {
                $country_names[] = $country['name'];
            }
            $country_names[] = 'England';
            $country_names[] = 'Scotland';
            $country_names[] = 'Wales';
            $country_names[] = 'Northern Ireland';
            $country_names[] = 'Multiple';
            $country_names[] = 'Not Applicable';
            $country_names[] = 'Unknown';
            asort($country_names);
            array_unshift($country_names, 'Please Select');
            Cache::store('redis')->put('country_names', $country_names, 10080);
        else:
            Log::error($response);
            return array();
        endif;
    endif;
    return Cache::store('redis')->get('country_names');
}

/**
 * Replaces characters file systems cannot process
 */
function replaceSpecialFileSystemChars(String $s) {
    $s = str_replace(
        [":", "/", ";", "?", "[", "]", '"'],
        [" - ", "-", " ", "", "(", ")", "'"],
        $s
    );
    // Trim whitespace
    $s = trim($s);
    // Strip periods if they are at the end of the string
    return rtrim($s, '.');
}

/**
 * Only allow read queries
 *
 * @param String $query SQL query
 */
function isValidReadQuery(String $query) {
    if (stripos($query, 'DELETE') !== false
        || stripos( $query, 'UPDATE') !== false
        || stripos( $query, 'INSERT') !== false
        || stripos( $query, 'ALTER') !== false
        || stripos( $query, 'DROP') !== false) {
        return false;
    }
    return true;
}
