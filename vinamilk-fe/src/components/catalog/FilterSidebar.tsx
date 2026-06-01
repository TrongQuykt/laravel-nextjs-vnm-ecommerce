"use client";

import { useRouter, useSearchParams, useParams } from "next/navigation";
import { useCallback, useEffect, useState, useTransition, useRef } from "react";
import { catalogApi } from "@/lib/api";
import { Attribute, Brand, ProductLine, Category } from "@/types";
import { ChevronDown, ChevronUp } from "lucide-react";

interface FilterData {
  categories: (Category & { count: number })[];
  brands: (Brand & { count: number })[];
  sugar_levels: (Attribute & { count: number })[];
  nutritional_needs: (Attribute & { count: number })[];
  flavors: (Attribute & { count: number })[];
  volumes: (Attribute & { count: number })[];
  packaging_types: (Attribute & { count: number })[];
}

interface FilterSidebarProps {
  productLines?: ProductLine[];
}

export default function FilterSidebar({ productLines = [] }: FilterSidebarProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const params = useParams();
  const currentPathSlug = params.slug as string;
  const [isPending, startTransition] = useTransition();
  
  const [filters, setFilters] = useState<FilterData | null>(null);
  const [openGroups, setOpenGroups] = useState<Record<string, boolean>>({});
  const [isInitialized, setIsInitialized] = useState(false);

  // Robust Optimistic State
  const [optimisticFilters, setOptimisticFilters] = useState<Record<string, string[]>>({});
  const isInteracting = useRef(false);
  const debounceTimer = useRef<NodeJS.Timeout | null>(null);

  // Sync from URL only when NOT interacting
  useEffect(() => {
    if (!isInteracting.current) {
      const next: Record<string, string[]> = {};
      searchParams.forEach((value, key) => {
        next[key] = value.split(",");
      });
      setOptimisticFilters(next);
    }
  }, [searchParams]);

  // Load and Persist Accordion State
  useEffect(() => {
    const saved = localStorage.getItem("vinamilk_sidebar_groups");
    const SIDEBAR_VERSION = "v2"; // Increment when structure changes
    
    if (saved) {
      const parsed = JSON.parse(saved);
      // Check if version matches, if not reset to defaults
      if (parsed.version === SIDEBAR_VERSION) {
        setOpenGroups(parsed.groups);
      } else {
        localStorage.removeItem("vinamilk_sidebar_groups");
        setOpenGroups({
          "Danh mục": true,
          "Dòng sản phẩm": true,
          "Thương hiệu": false,
        });
      }
    } else {
      setOpenGroups({
        "Danh mục": true,
        "Dòng sản phẩm": true,
        "Thương hiệu": false,
      });
    }
    setIsInitialized(true);
    catalogApi.getFilters().then(setFilters);
  }, []);

  useEffect(() => {
    if (isInitialized) {
      const SIDEBAR_VERSION = "v2";
      localStorage.setItem("vinamilk_sidebar_groups", JSON.stringify({
        version: SIDEBAR_VERSION,
        groups: openGroups
      }));
    }
  }, [openGroups, isInitialized]);

  const toggleGroup = (title: string) => {
    setOpenGroups(prev => ({ ...prev, [title]: !prev[title] }));
  };

  const syncToUrl = useCallback((currentFilters: Record<string, string[]>) => {
    const qParams = new URLSearchParams();
    
    // Preserve sort if exists
    const currentSort = searchParams.get('sort');
    if (currentSort) qParams.set('sort', currentSort);

    Object.entries(currentFilters).forEach(([name, values]) => {
        if (values.length > 0) {
            qParams.set(name, values.join(","));
        }
    });

    startTransition(() => {
        // Special logic for Category multi-select base URL
        const categories = currentFilters['category'] || [];
        
        // If we are on a specific category page but have multiple selections, move to all-products
        if (currentPathSlug !== 'all-products' && categories.length > 0) {
            // Ensure the current slug is also in the list if it wasn't already
            if (!categories.includes(currentPathSlug)) {
                categories.push(currentPathSlug);
                qParams.set('category', categories.join(','));
            }
            router.push(`/collections/all-products?${qParams.toString()}`, { scroll: false });
        } else {
            router.push(`?${qParams.toString()}`, { scroll: false });
        }
        
        // Clear interaction flag after transition starts
        setTimeout(() => {
            isInteracting.current = false;
        }, 500); 
    });
  }, [searchParams, currentPathSlug, router]);

  const toggleFilter = (name: string, value: string) => {
    isInteracting.current = true;

    // 1. Update UI instantly
    let finalFilters: Record<string, string[]> = {};
    setOptimisticFilters(prev => {
        const current = prev[name] || [];
        const next = current.includes(value) 
            ? current.filter(v => v !== value) 
            : [...current, value];
        const updated = { ...prev, [name]: next };
        finalFilters = updated;
        return updated;
    });

    // 2. Debounce URL Sync (300ms)
    if (debounceTimer.current) clearTimeout(debounceTimer.current);
    debounceTimer.current = setTimeout(() => {
        syncToUrl(finalFilters);
    }, 300);
  };

  if (!filters || !isInitialized) return <div className="w-full h-screen" />;

  const isSelected = (name: string, value: string) => {
    if (name === 'category' && currentPathSlug === value) return true;
    return optimisticFilters[name]?.includes(value) || false;
  };

  const getActiveCount = (name: string) => {
    const val = optimisticFilters[name];
    let count = val?.length || 0;
    if (name === 'category' && currentPathSlug !== 'all-products' && !val?.includes(currentPathSlug)) {
        count += 1;
    }
    return count;
  };

  return (
    <aside className="w-full flex flex-col gap-6 sticky top-28 h-fit pb-10 bg-transparent">
      {/* Category Filter */}
      <FilterGroup 
        title="Danh mục" 
        isOpen={openGroups["Danh mục"]} 
        onToggle={() => toggleGroup("Danh mục")}
        activeCount={getActiveCount('category')}
      >
        {filters.categories?.map((cat) => (
          <FilterItem 
            key={cat.id} 
            label={`${cat.name} (${cat.count})`} 
            active={isSelected('category', cat.slug)} 
            onClick={() => toggleFilter('category', cat.slug)} 
          />
        ))}
      </FilterGroup>

      {/* Product Line Filter */}
      {productLines.length > 0 && (
        <FilterGroup 
          title="Dòng sản phẩm" 
          isOpen={openGroups["Dòng sản phẩm"]} 
          onToggle={() => toggleGroup("Dòng sản phẩm")}
          activeCount={getActiveCount('product_line')}
        >
          {productLines.map((pl) => (
            <FilterItem
              key={pl.id}
              label={pl.count !== undefined ? `${pl.name} (${pl.count})` : pl.name}
              active={isSelected('product_line', pl.slug)}
              onClick={() => toggleFilter('product_line', pl.slug)}
            />
          ))}
        </FilterGroup>
      )}

      {/* Brand Filter */}
      <FilterGroup 
        title="Thương hiệu" 
        isOpen={openGroups["Thương hiệu"]} 
        onToggle={() => toggleGroup("Thương hiệu")}
        activeCount={getActiveCount('brand')}
      >
        {(filters.brands || []).map((b) => (
          <FilterItem 
            key={b.id} 
            label={`${b.name} (${b.count})`} 
            active={isSelected('brand', b.slug)} 
            onClick={() => toggleFilter('brand', b.slug)} 
          />
        ))}
      </FilterGroup>

      {/* Other filter groups follow... same pattern */}
      <FilterGroup title="Hương vị" isOpen={openGroups["Hương vị"]} onToggle={() => toggleGroup("Hương vị")} activeCount={getActiveCount('flavor')}>
        {filters.flavors.map(f => <FilterItem key={f.id} label={`${f.name} (${f.count})`} active={isSelected('flavor', f.slug)} onClick={() => toggleFilter('flavor', f.slug)} />)}
      </FilterGroup>

      <FilterGroup title="Thể tích" isOpen={openGroups["Thể tích / Khối lượng"]} onToggle={() => toggleGroup("Thể tích / Khối lượng")} activeCount={getActiveCount('volume')}>
        {filters.volumes.map(v => <FilterItem key={v.id} label={`${v.name} (${v.count})`} active={isSelected('volume', v.slug)} onClick={() => toggleFilter('volume', v.slug)} />)}
      </FilterGroup>
      
      <FilterGroup title="Nhu cầu" isOpen={openGroups["Nhu cầu dinh dưỡng"]} onToggle={() => toggleGroup("Nhu cầu dinh dưỡng")} activeCount={getActiveCount('need')}>
        {filters.nutritional_needs.map(n => <FilterItem key={n.id} label={`${n.name} (${n.count})`} active={isSelected('need', n.slug)} onClick={() => toggleFilter('need', n.slug)} />)}
      </FilterGroup>

      <FilterGroup title="Mức đường" isOpen={openGroups["Mức đường"]} onToggle={() => toggleGroup("Mức đường")} activeCount={getActiveCount('sugar')}>
        {filters.sugar_levels.map(s=> <FilterItem key={s.id} label={`${s.name} (${s.count})`} active={isSelected('sugar', s.slug)} onClick={() => toggleFilter('sugar', s.slug)} />)}
      </FilterGroup>
    </aside>
  );
}

function FilterGroup({ title, children, isOpen, onToggle, activeCount }: { title: string; children: React.ReactNode; isOpen: boolean; onToggle: () => void; activeCount?: number }) {
  return (
    <div className="flex flex-col gap-4">
      <button onClick={onToggle} className="flex items-center justify-between group cursor-pointer w-full text-left">
        <h3 className="text-[13px] font-bold tracking-tight text-[#001c9a]">
          {title} {activeCount ? `(${activeCount})` : ''}
        </h3>
        <span className="text-[#001c9a]/40">{isOpen ? <ChevronUp size={14} /> : <ChevronDown size={14} />}</span>
      </button>
      {isOpen && <div className="flex flex-col gap-3 pl-1">{children}</div>}
      <div className="h-px w-full bg-[#001c9a]/5 mt-2" />
    </div>
  );
}

function FilterItem({ label, active, onClick }: { label: string; active: boolean; onClick: () => void }) {
  return (
    <button onClick={onClick} className={`text-left text-[13px] flex items-center gap-3 relative z-10 transition-colors ${active ? "text-[#001c9a] font-bold" : "text-[#001c9a] hover:text-[#001c9a]/80"}`}>
      <div className={`w-4 h-4 rounded border flex items-center justify-center bg-transparent border-[#001c9a]/20 ${active ? "border-[#001c9a]" : ""}`}>
        {active && (
          <div className="bg-[#001c9a] w-full h-full rounded-[2px] flex items-center justify-center">
            <svg width="8" height="6" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 4L3.5 6.5L9 1" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </div>
        )}
      </div>
      {label}
    </button>
  );
}
