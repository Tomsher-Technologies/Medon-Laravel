<?php

namespace App\Utility;

use App\Models\Search;

class SearchUtility
{
    public static function store($query, $request)
    {
        if ($query != null && $query != "") {
            $users_id = null;
            $users_id_type = 'user_id';

            if (auth('sanctum')->user()) {
                $users_id = auth('sanctum')->user()->id;
            } else {
                $users_id_type = 'temp_user_id';
                $users_id = $request->header('UserToken');
            }

            Search::create([
                $users_id_type => $users_id,
                'query' => $query,
                'ip_address' => $request->ip()
            ]);
        }
    }
}
