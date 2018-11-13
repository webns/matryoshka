<?php

namespace Laracasts\Matryoshka;

use Cache;

class FlushViews
{
    /**
     * Handle the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     */
    public function handle($request, $next)
    {
        config('matryoshka.cache_tags') ?
            Cache::tags(config('matryoshka.cache_tags'))->flush() :
            Cache::flush();

        return $next($request);
    }
}
