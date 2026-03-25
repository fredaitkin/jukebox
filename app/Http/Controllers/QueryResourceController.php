<?php

/**
 * Controller for query requests
 *
 * @package Jukebox
 * @author  Fred Aitkin
 */

namespace App\Http\Controllers;

use App\Http\Requests\RunQueryRequest;
use App\Http\Controllers\Controller;
use DB;
use Exception;

/**
 * QueryResourceController handles query requests.
 *
 * Retrieves query results
 */
class QueryResourceController extends Controller
{

    /**
     * Run query
     *
     * @param RunQueryRequest $request Request object
     *
     * @return Response
     */
    public function query(RunQueryRequest $request)
    {
        $results = '';
        $count = 0;
        $validated = $request->validated();
        $query = $validated['myquery'];
        $show_cols = $request->boolean('show_cols');

        if (! empty($query)):
            try {
                $rows = DB::select($query);
                foreach($rows as $row):
                    $row = (array) $row;
                    foreach($row as $col => $val):
                        if ($show_cols):
                            $results .= $col . ' ';
                        endif;
                        $results .= $val . ' ';
                    endforeach;
                    $results .= "\n";
                    $count++;
                endforeach;
                $results = 'Count: ' . $count . "\n\n" . $results;
            } catch (Exception $e) {
                $results = $e->getMessage() . "\n";
            }
        endif;
        return view(
            'query', [
                'myquery' => $query,
                'results' => $results,
                'show_cols' => $show_cols,
            ]
        );
    }

}
