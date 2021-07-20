<?php

namespace App\Http\General;

use Illuminate\Support\Facades\Log;

trait errors {
    
    public function log_error_and_return_internal_server_error($error){
        Log::error(''.$error);

        return $this->return_internal_server_error();
    }

    public function return_internal_server_error(){
        return response()->json([
            'status' => false,
            'msg'    => 'internal server error!',
        ]);
    }
}