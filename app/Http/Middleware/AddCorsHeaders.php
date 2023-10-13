    <?php


    // app/Http/Middleware/AddCorsHeaders.php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Routing\Controllers\Middleware;

    class AddCorsHeaders extends Middleware
    {
        public function handle($request, Closure $next)
        {
            $response = $next($request);

            $response->header('Access-Control-Allow-Origin', '*'); // Adjust the origin
            $response->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

            return $response;
        }
    }
