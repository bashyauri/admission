<li class="mt-0.5 w-full">
    <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap rounded-lg px-4 {{ Route::currentRouteName() === 'notifications' ? 'bg-white font-semibold text-slate-700 shadow-soft-xl' : 'font-medium text-slate-500 shadow-none hover:bg-slate-100' }} transition-colors dark:text-white dark:opacity-80"
        href="{{ route('notifications') }}">
        <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white text-sky-700 shadow-soft-2xl">
            <i class="fas fa-bell text-xs" aria-hidden="true"></i>
        </div>
        <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Notifications</span>
    </a>
</li>
