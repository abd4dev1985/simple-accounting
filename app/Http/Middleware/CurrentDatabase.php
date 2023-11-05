<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\DatabaseManager;


class CurrentDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {  
         app(DatabaseManager::class)->currentDatabase($request);
        //dd(config('database.connections.tentant.database'));
        return $next($request);
    }

}
