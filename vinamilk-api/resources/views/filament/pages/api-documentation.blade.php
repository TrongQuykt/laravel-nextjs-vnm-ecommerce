<x-filament-panels::page>
    @if($isLoading)
        <!-- Loading Skeleton -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 animate-pulse">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
            </div>
            <div class="grid grid-cols-4 gap-4">
                @for($i = 0; $i < 4; $i++)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 h-24"></div>
                @endfor
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 h-12"></div>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow h-96"></div>
                <div class="col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow h-96"></div>
                <div class="col-span-5 bg-white dark:bg-gray-800 rounded-lg shadow h-96"></div>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->documentation['info']['title'] ?? 'API Documentation' }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        {{ $this->documentation['info']['description'] ?? '' }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        v{{ $this->documentation['info']['version'] ?? '1.0.0' }}
                    </span>
                    <button wire:click="loadDocumentation" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mt-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->documentation['statistics']['total'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Endpoints</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">
                        {{ $this->documentation['statistics']['public'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Public</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">
                        {{ $this->documentation['statistics']['protected'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Protected</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-purple-700 dark:text-purple-400">
                        {{ count($this->documentation['statistics']['by_method'] ?? []) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Methods</div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <input 
                type="text" 
                wire:model.live="searchQuery" 
                placeholder="Search endpoints by URI, controller, or description..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
            >
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-12 gap-6">
            <!-- Sidebar - Categories -->
            <div class="col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Categories</h2>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    @foreach($this->filteredEndpoints as $category => $endpoints)
                        <button 
                            wire:click="selectCategory('{{ $category }}')"
                            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition {{ $selectedCategory === $category ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-l-indigo-500' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $category }}</span>
                                <span class="text-xs bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 px-2 py-1 rounded-full">
                                    {{ count($endpoints) }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Endpoint List -->
            <div class="col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        {{ $selectedCategory ?: 'All Endpoints' }}
                    </h2>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    @if($selectedCategory && isset($this->filteredEndpoints[$selectedCategory]))
                        @foreach($this->filteredEndpoints[$selectedCategory] as $endpoint)
                            @php
                                $isSelected = $selectedEndpoint && is_array($selectedEndpoint) && isset($selectedEndpoint['uri']) && $selectedEndpoint['uri'] === $endpoint['uri'];
                            @endphp
                            <button 
                                wire:click="selectEndpoint('{{ $endpoint['uri'] }}')"
                                class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 transition {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}"
                            >
                                <div class="flex items-center space-x-2 mb-1">
                                    @foreach(explode(', ', $endpoint['method']) as $method)
                                        <span class="px-2 py-1 text-xs font-bold rounded {{ $this->getMethodColor($method) }}">
                                            {{ $method }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    /{{ $endpoint['uri'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                    {{ $endpoint['controller'] }}@{{ $endpoint['action'] }}
                                </div>
                            </button>
                        @endforeach
                    @else
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            Select a category to view endpoints
                        </div>
                    @endif
                </div>
            </div>

            <!-- Endpoint Details -->
            <div class="col-span-5 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Endpoint Details</h2>
                </div>
                <div class="p-6 max-h-[600px] overflow-y-auto">
                    @if($selectedEndpoint)
                        <div class="space-y-4">
                            <!-- Method & URI -->
                            <div class="flex items-center space-x-3">
                                @foreach(explode(', ', $selectedEndpoint['method']) as $method)
                                    <span class="px-3 py-2 text-sm font-bold rounded {{ $this->getMethodColor($method) }}">
                                        {{ $method }}
                                    </span>
                                @endforeach
                                <code class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded text-sm text-gray-900 dark:text-white">
                                    /{{ $selectedEndpoint['uri'] }}
                                </code>
                            </div>

                            <!-- Full URI -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full URI</label>
                                <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                    <code class="text-sm text-gray-900 dark:text-white">{{ $selectedEndpoint['full_uri'] }}</code>
                                </div>
                            </div>

                            <!-- Controller -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Controller</label>
                                <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                    <code class="text-sm text-gray-900 dark:text-white">{{ $selectedEndpoint['controller'] }}@{{ $selectedEndpoint['action'] }}</code>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($selectedEndpoint['description'])
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                        <p class="text-sm text-gray-900 dark:text-white">{{ $selectedEndpoint['description'] }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Protection Status -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Authentication</label>
                                <div class="mt-1">
                                    @if($selectedEndpoint['protected'])
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Protected (auth:sanctum)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Public
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Parameters -->
                            @if(!empty($selectedEndpoint['parameters']))
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">URI Parameters</label>
                                    <div class="mt-2 space-y-2">
                                        @foreach($selectedEndpoint['parameters'] as $param)
                                            <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-mono mr-2">{{ $param }}</span>
                                                <span class="text-sm text-gray-600 dark:text-gray-400">path parameter</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Middleware -->
                            @if(!empty($selectedEndpoint['middleware']))
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Middleware</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($selectedEndpoint['middleware'] as $middleware)
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">{{ $middleware }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Try it out button -->
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a 
                                    href="{{ url('/' . $selectedEndpoint['full_uri']) }}" 
                                    target="_blank"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Open in New Tab
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>Select an endpoint to view details</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
