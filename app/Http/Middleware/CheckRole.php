<?php

namespace App\Http\Middleware;

use App\Models\WebUser;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRoleId = auth()->user()->role_id;

        if (!in_array($userRoleId, $roles)) {
            return response(['message' => "You don't have access for this!"],403);
        }

        if (auth()->user()->role && auth()->user()->role->slug == 'pub_owner') {
            $webRoleIds = WebUser::where('parent_id', auth()->user()->id)->pluck('id')->toArray();
            $request->merge(['web_user_ids' => $webRoleIds]);
        }

        return $next($request);
    }
}
