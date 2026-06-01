<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\ApiDocumentationService;

class ApiDocumentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Công cụ phát triển';

    protected static ?string $navigationLabel = 'API Documentation';

    protected static ?string $title = 'API Documentation';

    protected static string $view = 'filament.pages.api-documentation';

    public $documentation = [];
    public $selectedCategory = null;
    public $selectedEndpoint = null;
    public $selectedEndpointUri = null;
    public $searchQuery = '';
    public $isLoading = false;

    public function mount()
    {
        $this->isLoading = true;
        $this->loadDocumentation();
    }

    public function loadDocumentation()
    {
        $service = new ApiDocumentationService();
        $this->documentation = $service->generate();
        $this->isLoading = false;
    }

    public function selectCategory($category)
    {
        $this->selectedCategory = $category === $this->selectedCategory ? null : $category;
        $this->selectedEndpoint = null;
        $this->selectedEndpointUri = null;
    }

    public function selectEndpoint($uri)
    {
        $this->selectedEndpointUri = $uri === $this->selectedEndpointUri ? null : $uri;
        
        // Find the full endpoint data
        $this->selectedEndpoint = null;
        if ($this->selectedEndpointUri && $this->selectedCategory) {
            foreach ($this->filteredEndpoints[$this->selectedCategory] as $endpoint) {
                if ($endpoint['uri'] === $this->selectedEndpointUri) {
                    $this->selectedEndpoint = $endpoint;
                    break;
                }
            }
        }
    }

    public function getFilteredEndpointsProperty()
    {
        if (!$this->searchQuery) {
            return $this->documentation['endpoints'] ?? [];
        }

        $filtered = [];
        foreach ($this->documentation['endpoints'] as $category => $endpoints) {
            $filteredEndpoints = array_filter($endpoints, function($endpoint) {
                $search = strtolower($this->searchQuery);
                return str_contains(strtolower($endpoint['uri']), $search) ||
                       str_contains(strtolower($endpoint['controller']), $search) ||
                       str_contains(strtolower($endpoint['description']), $search);
            });

            if (!empty($filteredEndpoints)) {
                $filtered[$category] = array_values($filteredEndpoints);
            }
        }

        return $filtered;
    }

    public function getMethodColor($method)
    {
        return match(strtoupper($method)) {
            'GET' => 'bg-green-100 text-green-800',
            'POST' => 'bg-blue-100 text-blue-800',
            'PUT' => 'bg-yellow-100 text-yellow-800',
            'PATCH' => 'bg-orange-100 text-orange-800',
            'DELETE' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
