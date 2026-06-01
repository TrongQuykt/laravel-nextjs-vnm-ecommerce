import React from 'react';
import Link from 'next/link';
import { MegaMenu } from '@/types';
import { getImageUrl } from '@/lib/api';
import { ArrowRight } from 'lucide-react';

interface MegaMenuDropdownProps {
    menu: MegaMenu;
}

export default function MegaMenuDropdown({ menu }: MegaMenuDropdownProps) {
    // Lấy ảnh sản phẩm: ưu tiên computed_main_image (từ volumeMedia), sau đó main_image
    const productImage = menu.featured_product?.computed_main_image
        || getImageUrl(menu.featured_product?.main_image ?? null)
        || null;

    return (
        <div className="w-full bg-[#fefef0] border-t-2 border-[#0213b0]/10 shadow-2xl">
            {/* Căn chỉnh ngang gióng theo Logo / Cart icon bằng cùng px-10 md:px-20 */}
            <div className="max-w-[1440px] mx-auto px-10 md:px-20 flex">

                {/* ====== CỘT TRÁI ====== */}
                <div className="w-[240px] flex-shrink-0 flex flex-col p-5 gap-4 border-r border-gray-100">

                    {/* Featured Product Card */}
                    {menu.featured_product && (
                        <div className="rounded-xl p-4 relative flex flex-col gap-2 group/card overflow-hidden">
                            {/* Badge MỚI + Tag */}
                            <div className="flex justify-between items-start mb-1">
                                <span className="text-[#0213b0] font-black text-[10px] uppercase tracking-wider">MỚI</span>
                                {menu.featured_product.card_tag && (
                                    <div className="border border-[#0213b0] rounded-full px-2 py-0.5">
                                        <span className="text-[#0213b0] font-bold text-[9px] uppercase">{menu.featured_product.card_tag.name}</span>
                                    </div>
                                )}
                            </div>

                            {/* Product Image */}
                            <div className="relative w-full aspect-[4/3] flex items-center justify-center">
                                {productImage ? (
                                    <img
                                        src={productImage}
                                        alt={menu.featured_product.name}
                                        className="object-contain w-full h-full"
                                    />
                                ) : (
                                    <div className="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
                                        <span className="text-gray-400 text-xs">Chưa có ảnh</span>
                                    </div>
                                )}
                            </div>

                            {/* Product Name & Arrow */}
<h3 className="text-[#0213b0] font-bold text-[13px] leading-snug flex items-center gap-1.5 dynamic-title border-b border-[#0213b0]/20">
    <span>{menu.featured_product.name}</span>
    <ArrowRight className="w-4 h-4 text-[#0213b0] shrink-0 inline-block align-middle" />
</h3>

                            <Link href={`/products/${menu.featured_product.slug}`} className="absolute inset-0 z-10" />
                        </div>
                    )}

                    {/* Bottom Links - Admin Configurable */}
                    {menu.bottom_links && menu.bottom_links.length > 0 && (
                        <div className="flex flex-col gap-2">
                            {menu.bottom_links.map((link, idx) => {
                                const isPink = link.theme === 'pink';
                                const bg = isPink ? 'bg-[#f4dbe8] hover:bg-[#ebd0df]' : 'bg-[#d8f2eb] hover:bg-[#cdeee5]';

                                return (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        className={`${bg} text-[#0213b0] font-bold text-[13px] uppercase py-2.5 px-4 rounded-md flex justify-between items-center transition-colors`}
                                    >
                                        <span className="tracking-wide">{link.label}</span>
                                        {link.badge && (
                                            <span
                                                className="border-[1.5px] border-[#0213b0] text-[#0213b0] text-[10px] font-black uppercase px-2 py-0.5 -rotate-3 inline-block"
                                                style={{ borderRadius: '40% / 50%' }}
                                            >
                                                {link.badge}
                                            </span>
                                        )}
                                    </Link>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* ====== CỘT PHẢI ====== */}
                <div className="flex-1 py-8 px-10">
                    <div className="grid grid-cols-4 gap-x-10 gap-y-8">
                        {menu.columns?.map((col, colIdx) => (
                            <div key={colIdx}>
                                <h4 className="text-v-navy font-black text-[12px] uppercase tracking-widest mb-4">{col.title}</h4>
                                <ul className="flex flex-col gap-2.5">
                                    {col.links?.map((link, linkIdx) => (
                                        <li key={linkIdx}>
                                            <Link
    href={link.url}
    className="text-v-navy text-[14px] flex items-baseline gap-0.5 px-2 py-1 rounded-md transition-colors duration-200 hover:bg-[#d3e1ff82]"
>
    <span>{link.label}</span>
    {link.badge && (
        <sup className="text-[11px] font-sans font-bold opacity-60 ml-0.5">{link.badge}</sup>
    )}
</Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                </div>

            </div>
        </div>
    );
}
