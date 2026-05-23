'use client';

import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';

interface TOCItem {
  id: string;
  text: string;
  level: number;
}

interface TableOfContentsProps {
  content: string;
}

const TableOfContents: React.FC<TableOfContentsProps> = ({ content }) => {
  const [items, setItems] = useState<TOCItem[]>([]);
  const [activeId, setActiveId] = useState<string>('');

  useEffect(() => {
    // Parse content to find h2, h3
    const parser = new DOMParser();
    const doc = parser.parseFromString(content, 'text/html');
    const headings = Array.from(doc.querySelectorAll('h2, h3'));
    
    const tocItems = headings.map((heading, index) => {
      const text = heading.textContent || '';
      const id = text.toLowerCase().replace(/\s+/g, '-') + '-' + index;
      return { id, text, level: parseInt(heading.tagName[1]) };
    });

    setItems(tocItems);
  }, [content]);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setActiveId(entry.target.id);
          }
        });
      },
      { rootMargin: '-100px 0px -60% 0px' }
    );

    const headingElements = document.querySelectorAll('article h2, article h3');
    headingElements.forEach((el) => observer.observe(el));

    return () => observer.disconnect();
  }, [items]);

  if (items.length === 0) return null;

  return (
    <div className="sticky top-24 max-h-[calc(100vh-120px)] overflow-y-auto hidden lg:block pr-8 border-r border-gray-100">
      <h4 className="text-[#001c9a] font-black text-lg mb-6 uppercase tracking-wider">
        Mục lục bài viết
      </h4>
      <nav className="space-y-1">
        {items.map((item) => (
          <a
            key={item.id}
            href={`#${item.id}`}
            className={`block py-2 text-sm transition-all duration-300 border-l-2 pl-4 ${
              activeId === item.id 
                ? 'text-[#001c9a] font-bold border-[#001c9a] bg-blue-50/50' 
                : 'text-gray-500 border-transparent hover:text-[#001c9a] hover:border-gray-200'
            } ${item.level === 3 ? 'ml-4' : ''}`}
          >
            {item.text}
          </a>
        ))}
      </nav>
    </div>
  );
};

export default TableOfContents;
