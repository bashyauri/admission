{{-- filepath: c:\laragon\www\admission\resources\views\livewire\staff\id-card-processing.blade.php --}}
<div>
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="flex items-start gap-3 p-4 mb-4 border rounded-lg border-green-200 bg-green-50 text-green-700 text-size-sm shadow-soft-sm">
            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">{{ session('success') }}</div>
            <button wire:click="$refresh" class="text-lime-500 hover:text-lime-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-start gap-3 p-4 mb-4 border rounded-lg border-red-200 bg-red-50 text-red-700 text-size-sm shadow-soft-sm">
            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">{{ session('error') }}</div>
            <button wire:click="$refresh" class="text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Control Bar --}}
    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 dark:bg-gray-900 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4 pb-3">
            <div class="flex items-center justify-between">
                <h6 class="mb-0 dark:text-white">ID Card Processing</h6>
                <span class="leading-tight text-size-xs text-slate-500">{{ $session }}</span>
            </div>
        </div>

        <div class="relative flex-auto p-4">
            <div class="flex flex-wrap -mx-3">
                {{-- Search --}}
                <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                    <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Search</label>
                    <div class="relative">
                        <input wire:model.debounce.400ms="search" type="text"
                            class="focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pl-8 pr-3 text-size-sm font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow dark:bg-gray-950 dark:text-white/80"
                            placeholder="Surname / Matric / Dept...">
                        <svg class="absolute w-4 h-4 text-gray-400 left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter --}}
                <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-2/12">
                    <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Filter</label>
                    <select wire:model="filter"
                        class="focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 text-size-sm font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow dark:bg-gray-950 dark:text-white/80">
                        <option value="all">All Students</option>
                        <option value="processed">Processed Only</option>
                        <option value="printed">Printed Only</option>
                        <option value="not_printed">Not Printed</option>
                    </select>
                </div>

                {{-- Sort Buttons --}}
                <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-4/12">
                    <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Sort By</label>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="toggleSort('surname')" type="button"
                            class="inline-block px-4 mr-1 py-2 font-bold text-center uppercase align-middle transition-all border rounded-lg cursor-pointer text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 leading-pro
                            {{ $sortBy === 'surname' ? 'border-fuchsia-300 bg-gradient-fuchsia text-white' : 'border-fuchsia-500 text-fuchsia-500 hover:shadow-soft-xs' }}">
                            Surname @if($sortBy === 'surname'){{ $sortDir === 'asc' ? '↑' : '↓' }}@endif
                        </button>
                        <button wire:click="toggleSort('department')" type="button"
                            class="inline-block px-4 py-2 mr-1 font-bold text-center uppercase align-middle transition-all border rounded-lg cursor-pointer text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 leading-pro
                            {{ $sortBy === 'department' ?  'border-fuchsia-300 bg-gradient-fuchsia text-white' : 'border-fuchsia-500 text-fuchsia-500 hover:shadow-soft-xs' }}">
                            Dept @if($sortBy === 'department'){{ $sortDir === 'asc' ? '↑' : '↓' }}@endif
                        </button>
                        <button wire:click="toggleSort('level')" type="button"
                            class="inline-block px-4 py-2 font-bold text-center uppercase align-middle transition-all border rounded-lg cursor-pointer text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 leading-pro
                            {{ $sortBy === 'level' ? 'border-fuchsia-300 bg-gradient-fuchsia text-white' : 'border-fuchsia-500 text-fuchsia-500 hover:shadow-soft-xs' }}">
                            Level @if($sortBy === 'level'){{ $sortDir === 'asc' ? '↑' : '↓' }}@endif
                        </button>
                    </div>
                </div>

                {{-- Per Page & Actions --}}
                <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                    <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Per Page</label>
                    <div class="flex gap-2">
                        <select wire:model="perPage"
                            class="focus:shadow-soft-primary-outline leading-5.6 mr-1 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 text-size-sm font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow dark:bg-gray-950 dark:text-white/80">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                            <option value="96">96</option>
                        </select>
                        <button wire:click="resetFilters" type="button"
                            class="inline-block px-4 py-2 mr-1 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">
                            Reset
                        </button>
                        <button wire:click="$refresh" type="button"
                            class="inline-block px-4 py-2 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            @if($stats)
                <div class="flex flex-wrap mt-4 -mx-3">
                    <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                            <div class="relative flex-auto p-4">
                                <p class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-500 dark:text-slate-400">Total Students</p>
                                <h5 class="mb-0 font-bold dark:text-white">{{ $stats['total'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                            <div class="relative flex-auto p-4">
                                <p class="mb-1 font-semibold leading-normal capitalize text-size-xs text-lime-500">Processed</p>
                                <h5 class="mb-0 font-bold text-lime-500">{{ $stats['processed'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="w-full max-w-full px-3 mb-4 shrink-0 sm:mb-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                            <div class="relative flex-auto p-4">
                                <p class="mb-1 font-semibold leading-normal capitalize text-size-xs text-cyan-500">Printed</p>
                                <h5 class="mb-0 font-bold text-cyan-500">{{ $stats['printed'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-6/12 lg:w-3/12">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                            <div class="relative flex-auto p-4">
                                <p class="mb-1 font-semibold leading-normal capitalize text-size-xs text-orange-500">Remaining</p>
                                <h5 class="mb-0 font-bold text-orange-500">{{ $stats['remaining'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div wire:loading class="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
        <div class="bg-white dark:bg-gray-950 rounded-2xl p-6 shadow-soft-2xl flex items-center gap-4">
            <div class="w-8 h-8 border-4 border-t-transparent rounded-full animate-spin border-fuchsia-500"></div>
            <span class="font-medium text-size-sm text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    {{-- Student Cards Grid --}}
    @if($rows->isEmpty())
        <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-12 text-center">
                <i class="text-gray-300 fas fa-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                <h5 class="mt-3 text-secondary dark:text-white">No students found</h5>
                <p class="mt-1 text-size-xs text-slate-400">Try adjusting your filters</p>
            </div>
        </div>
    @else
        <div class="flex flex-wrap -mx-3">
            @foreach($rows as $row)
                <div class="w-full max-w-full px-3 mb-6 shrink-0 sm:flex-0 sm:w-6/12 lg:w-4/12 xl:w-3/12">
                    <div class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                        <div class="relative flex-auto p-4">
                            <div class="flex gap-3">
                                {{-- Photo --}}
                                <div class="flex-shrink-0">
                                    <img src="{{ $row['photo'] }}" alt="{{ $row['surname'] }}"
                                        class="inline-flex items-center justify-center w-16 h-16 text-white transition-all duration-200 border-2 border-fuchsia-200 text-size-base ease-soft-in-out rounded-xl">
                                    @if($row['print'] === 'printed')
                                        <span class="inline-block px-2 py-1 mt-1 font-bold text-white rounded-full bg-gradient-cyan text-size-xxs">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
</svg>
 Printed
                                        </span>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h6 class="mb-1 font-bold leading-normal truncate text-size-sm dark:text-white"
                                        title="{{ $row['surname'] }}, {{ $row['firstname'] }} {{ $row['m_name'] }}">
                                        {{ $row['surname'] }}, {{ $row['firstname'] }}
                                        @if($row['m_name']) <span class="text-slate-500">{{ $row['m_name'] }}</span> @endif
                                    </h6>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-1 mb-2">
                                        <!--<button x-data="{copied:false}"-->
                                        <!--    @click="navigator.clipboard.writeText('{{ $row['surname'] }}, {{ $row['firstname'] }} {{ $row['m_name'] }}'); copied=true; setTimeout(()=>copied=false,1500)"-->
                                        <!--    type="button"-->
                                        <!--    class="inline-block px-3 py-1 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer text-size-xxs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-gradient-gray text-slate-700 hover:shadow-soft-xs">-->
                                        <!--    <i class="fas fa-copy"></i>-->
                                        <!--</button>-->
                                        <!--<span x-show="copied" x-transition class="font-bold text-size-xxs text-lime-500">✓</span>-->
                                        <button wire:click="openDetail('{{ $row['id'] }}')" type="button"
                                            class="inline-block px-3 py-1 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer text-size-xxs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 ml-1 bg-x-25 bg-slate-500 hover:shadow-soft-xs">
                                             View
                                        </button>
                                    </div>

                                    <p class="mb-1 font-semibold leading-normal text-size-xs text-fuchsia-500">
                                        <i class="fas fa-id-card"></i> {{ $row['matric_no'] }}
                                    </p>
                                    <p class="mb-1 leading-normal truncate text-size-xs text-slate-500 dark:text-slate-400" title="{{ $row['department'] }}">
                                        <i class="fas fa-building"></i> {{ $row['department'] }}
                                    </p>
                                    <p class="mb-2 leading-normal text-size-xs text-slate-500 dark:text-slate-400">
                                        <i class="fas fa-layer-group"></i> Level <span class="font-semibold">{{ $row['level'] }}</span>
                                    </p>

                                    {{-- Status Badges --}}
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        <!--<span class="inline-block px-2 py-1 mr-1 font-bold rounded text-size-xxs {{ $row['status'] === 'processed' ? 'bg-gradient-lime text-white' : 'bg-slate-400 text-white' }}">-->
                                        <!--    {{ ucfirst($row['status']) }}-->
                                        <!--</span>-->
                                        <span class="inline-block px-2 py-1 font-bold rounded text-size-xxs {{ $row['print'] === 'printed' ? 'bg-gradient-cyan text-white' : 'bg-slate-400 text-white' }}">
                                            {{ ucfirst(str_replace('_', ' ', $row['print'])) }}
                                        </span>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex gap-2">
                                        <!--<button wire:click="markProcessed('{{ $row['id'] }}')" wire:loading.attr="disabled" type="button"-->
                                        <!--    class="inline-block px-3 py-2 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer flex-1 hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">-->
                                        <!--    <span wire:loading.remove wire:target="markProcessed"><i class="fas fa-check"></i> Process</span>-->
                                        <!--    <span wire:loading wire:target="markProcessed"><i class="fas fa-spinner fa-spin"></i></span>-->
                                        <!--</button>-->

                                        @if($row['print'] === 'printed')
                                            <button wire:click="markPrinted('{{ $row['id'] }}', false)" wire:loading.attr="disabled" type="button"
                                                class="inline-block px-3 py-2 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer flex-1 hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-red leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                                                <span wire:loading.remove wire:target="markPrinted"><i class="fas fa-times"></i> Unmark</span>
                                                <span wire:loading wire:target="markPrinted"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                        @else
                                            <button wire:click="markPrinted('{{ $row['id'] }}', true)" wire:loading.attr="disabled" type="button"
                                                class="inline-block px-3 py-2 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer flex-1 hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-cyan leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                                                <span wire:loading.remove wire:target="markPrinted"><i class="fas fa-print"></i> Print</span>
                                                <span wire:loading wire:target="markPrinted"><i class="fas fa-spinner fa-spin"></i></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center mt-6">
            {{ $pageObj->links() }}
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="max-w-md m-4 bg-white dark:bg-gray-500 rounded-2xl shadow-soft-2xl">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4 pb-3">
                    <div class="flex items-center justify-between">
                        <h6 class="mb-0 dark:text-white">Student Details</h6>
                        <button wire:click="closeDetail" type="button"
                            class="active:shadow-soft-xs active:opacity-85 ease-soft-in leading-pro text-size-xs bg-150 bg-x-25 rounded-3.5xl p-1.2 h-6 w-6 flex cursor-pointer items-center justify-center border border-solid border-slate-400 bg-transparent text-center align-middle font-bold text-slate-400 shadow-none transition-all hover:bg-transparent hover:text-slate-400 hover:opacity-75">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="relative flex-auto p-4">
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Full Name</label>
                            <p class="mb-0 leading-normal text-size-sm dark:text-white">{{ $detail['full_name'] ?? '' }}</p>
                        </div>
                        <div>
                            <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Matric No</label>
                            <p class="mb-0 leading-normal text-size-sm dark:text-white">{{ $detail['matric_no'] ?? '' }}</p>
                        </div>
                        <div>
                            <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Department</label>
                            <p class="mb-0 leading-normal text-size-sm dark:text-white">{{ $detail['department'] ?? '' }}</p>
                        </div>
                        <div>
                            <label class="mb-1 font-semibold leading-normal capitalize text-size-xs text-slate-600 dark:text-slate-400">Level</label>
                            <p class="mb-0 leading-normal text-size-sm dark:text-white">{{ $detail['level'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 p-4 border-t border-solid border-black/12.5">
                    <button x-data="{copied:false}"
                        @click="navigator.clipboard.writeText('{{ $detail['full_name'] ?? '' }}'); copied=true; setTimeout(()=>copied=false,1500)"
                        type="button"
                        class="inline-block px-6 py-3 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-700">
                        <i class="fas fa-copy"></i> Copy Name
                        <span x-show="copied" class="ml-1 font-bold text-lime-500">✓</span>
                    </button>
                    <button wire:click="closeDetail" type="button"
                        class="inline-block px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-red leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
