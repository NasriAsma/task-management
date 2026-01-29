<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RouteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Temporarily disable foreign key checks before truncating
        Schema::disableForeignKeyConstraints();
        DB::table('routes')->truncate();
        Schema::enableForeignKeyConstraints();

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            // skip closures or internal debug routes
            $uri = $route->uri();
            if (empty($uri) || str_contains($uri, '_debugbar')) {
                continue;
            }

            $methods = implode(',', $route->methods());
            $name = $route->getName() ?? $route->getActionName();

            DB::table('routes')->updateOrInsert(
                ['path' => $uri, 'method' => $methods],
                ['name' => $name]
            );
        }
    }
}
