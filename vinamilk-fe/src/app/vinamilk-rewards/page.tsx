'use client';

import React, { useState, useEffect } from 'react';
import Navbar from '@/components/layout/Navbar';
import Footer from '@/components/layout/Footer';
import { Ticket, X, ChevronLeft, ChevronRight, History, Gift as GiftIcon } from 'lucide-react';
import { authFetchApi } from '@/lib/api';
import RewardHistorySidebar from '@/components/account/RewardHistorySidebar';

interface Banner {
    id: number;
    image: string;
    link: string | null;
    title: string | null;
    subtitle: string | null;
    show_text: boolean;
}

interface Reward {
    id: number;
    name: string;
    type: 'voucher' | 'gift';
    description: string;
    image: string | null;
    points_required: number;
    stock_quantity: number;
    user_limit: number;
    user_redemptions: number;
    can_redeem: boolean;
    progress: number;
}

export default function RewardsPage() {
    const [data, setData] = useState<{ user: any, rewards: Reward[], banners: Banner[] } | null>(null);
    const [loading, setLoading] = useState(true);
    const [selectedReward, setSelectedReward] = useState<Reward | null>(null);
    const [currentBanner, setCurrentBanner] = useState(0);
    const [isHistorySidebarOpen, setIsHistorySidebarOpen] = useState(false);
    const [minLoadingTimeElapsed, setMinLoadingTimeElapsed] = useState(false);
    const [showAllVouchers, setShowAllVouchers] = useState(false);
    const [showAllGifts, setShowAllGifts] = useState(false);

    useEffect(() => {
        if (selectedReward) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = 'unset';
    }, [selectedReward]);

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }
        
        // Set minimum loading time
        const minLoadingTimer = setTimeout(() => {
            setMinLoadingTimeElapsed(true);
        }, 800);
        
        fetchRewards();
        
        return () => clearTimeout(minLoadingTimer);
    }, []);

    const [isRedeeming, setIsRedeeming] = useState(false);

    const fetchRewards = async () => {
        try {
            const json = await authFetchApi<{ user: any, rewards: Reward[], banners: Banner[] }>('/rewards');
            setData(json);
        } catch (error: any) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handleRedeem = async (rewardId: number, rewardName: string, pointsRequired: number) => {
        const confirmRedeem = window.confirm(`Bạn có chắc chắn muốn dùng ${pointsRequired.toLocaleString()} điểm để quy đổi quà tặng "${rewardName}" không?`);
        if (!confirmRedeem) return;

        setIsRedeeming(true);
        try {
            const res = await authFetchApi<any>(`/rewards/${rewardId}/redeem`, {
                method: 'POST'
            });
            
            if (res && res.new_points !== undefined) {
                alert("Đổi quà thành công! Phần quà đã được thêm vào ví voucher của bạn.");
                setSelectedReward(null);
                await fetchRewards(); // Refresh points and buttons
            } else {
                alert(res.message || "Có lỗi xảy ra trong quá trình đổi quà.");
            }
        } catch (e: any) {
            console.error(e);
            alert(e.message || "Có lỗi xảy ra trong quá trình đổi quà.");
        } finally {
            setIsRedeeming(false);
        }
    };

    const nextBanner = () => {
        if (data?.banners.length) {
            setCurrentBanner((prev) => (prev + 1) % data.banners.length);
        }
    };

    const prevBanner = () => {
        if (data?.banners.length) {
            setCurrentBanner((prev) => (prev - 1 + data.banners.length) % data.banners.length);
        }
    };

    if (loading || !minLoadingTimeElapsed) return <RewardsPageSkeleton />;

    const vouchers = data?.rewards.filter(r => r.type === 'voucher') || [];
    const gifts = data?.rewards.filter(r => r.type === 'gift') || [];
    const banners = data?.banners || [];
    
    const displayedVouchers = showAllVouchers ? vouchers : vouchers.slice(0, 6);
    const displayedGifts = showAllGifts ? gifts : gifts.slice(0, 6);

    return (
        <div className="bg-[#fcfaf2] min-h-screen font-sans text-[#002094]">
            <Navbar />

            {/* NEW HERO SECTION */}
            <section className="bg-[#0213b0] relative h-[750px] w-full pt-20 overflow-hidden">
                {/* Factory Pattern Bottom */}
                <div className="absolute bottom-0 h-auto w-full overflow-hidden">
                    <div className="relative flex h-38 w-full">
                        {[...Array(20)].map((_, i) => (
                            <div key={i} className="relative -top-16.5 h-0 w-0 border-[66px] border-b-[76px] border-solid border-transparent border-b-[#001a7a] after:absolute after:top-[19px] after:-left-[66px] after:h-0 after:w-0 after:content-[''] after:border-[66px] after:border-t-[76px] after:border-solid after:border-transparent after:border-t-[#001a7a]"></div>
                        ))}
                    </div>
                </div>

                {/* Floating Images */}
                <div className="hidden md:block">
                    <div className="absolute bottom-[16.73vw] left-[6.66vw] h-[13.6vw] w-[10.4vw] transition-opacity duration-700">
                        <img alt="Phụ nữ" src="https://d8um25gjecm9v.cloudfront.net/cms/mom_f7145c9379.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[7.43vw] left-[13.19vw] h-[5vw] w-[6.52vw] transition-opacity duration-700">
                        <img alt="Con mèo" src="https://d8um25gjecm9v.cloudfront.net/cms/cat_4d8c6ad5d0.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[4.86vw] left-[23.19vw] h-[10.76vw] w-[13.12vw] transition-opacity duration-700">
                        <img alt="Đàn ông và con gái" src="https://d8um25gjecm9v.cloudfront.net/cms/dad_Daughter_31d43aa365.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[16.25vw] right-[11.52vw] h-[12.5vw] w-[18.12vw] transition-opacity duration-700">
                        <img alt="Đàn ông" src="https://d8um25gjecm9v.cloudfront.net/cms/dad_18e519298c.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[15.41vw] right-[7.29vw] h-[6.73vw] w-[5.69vw] transition-opacity duration-700">
                        <img alt="Con chó" src="https://d8um25gjecm9v.cloudfront.net/cms/dog_b1758a9ffa.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[5.34vw] right-[24.44vw] h-[5vw] w-[5.69vw] transition-opacity duration-700">
                        <img alt="Con vịt" src="https://d8um25gjecm9v.cloudfront.net/cms/duck_d9e156307c.webp" className="w-full h-full object-contain" />
                    </div>
                    <div className="absolute bottom-[2.43vw] right-[32.63vw] h-[9.44vw] w-[14.44vw] transition-opacity duration-700">
                        <img alt="Trẻ em" src="https://d8um25gjecm9v.cloudfront.net/cms/children_7023765bc5.webp" className="w-full h-full object-contain" />
                    </div>
                </div>

                {/* User Info Card */}
                <div className="absolute flex w-full justify-center top-[180px]">
                    <div className="flex flex-col items-center gap-6 text-center">
                        <div className="flex flex-col text-center text-white">
                            <p className="font-serif italic text-lg mb-1">Xin chào</p>
                            <h2 className="text-4xl font-bold uppercase !text-white">{data?.user.name}</h2>
                        </div>
                        <div className="bg-[#fcfaf2] flex w-[320px] flex-col items-center justify-center gap-4 rounded-xl p-8 shadow-xl">
                            <div className="text-[10px] uppercase font-bold tracking-widest text-[#002094] opacity-70">Bạn đang có</div>
                            <h3 className="text-4xl font-bold text-[#002094]">{data?.user.reward_points.toLocaleString()} Điểm</h3>

                            <div className="w-full space-y-3 mt-4">
                                <button className="w-full flex items-center justify-center gap-2 bg-[#002094] text-white py-3 rounded-lg font-bold text-[13px] hover:bg-blue-900 transition-colors">
                                    <GiftIcon size={18} />
                                    <span>Quà của tôi</span>
                                </button>
                                <button
                                    onClick={() => setIsHistorySidebarOpen(true)}
                                    className="w-full flex items-center justify-center gap-2 border border-[#002094] text-[#002094] py-3 rounded-lg font-bold text-[13px] hover:bg-blue-50 transition-colors"
                                >
                                    <History size={18} />
                                    <span>Lịch sử điểm thưởng</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <main className="max-w-7xl mx-auto px-4 pb-16 pt-40 mt-[-140px] relative z-10">
                {/* Banner Carousel */}
                {banners.length > 0 && (
                    <div className="relative w-full h-[380px] md:h-[550px] mb-20 overflow-hidden rounded-2xl group border border-gray-100">
                        <div className="flex h-full transition-transform duration-700 ease-in-out" style={{ transform: `translateX(-${currentBanner * 100}%)` }}>
                            {banners.map((banner) => (
                                <div key={banner.id} className="w-full h-full flex-shrink-0 relative bg-white">
                                    <a href={banner.link || '#'} className={banner.link ? 'cursor-pointer' : 'cursor-default'}>
                                        <img src={banner.image} className="w-full h-full object-cover" />
                                        {banner.show_text && (banner.title || banner.subtitle) && (
                                            <div className="absolute inset-0 bg-black/10 flex flex-col justify-center px-12 text-white">
                                                {banner.subtitle && <p className="text-xl font-medium mb-2">{banner.subtitle}</p>}
                                                {banner.title && <h2 className="text-5xl font-black italic">{banner.title}</h2>}
                                            </div>
                                        )}
                                    </a>
                                </div>
                            ))}
                        </div>
                        {banners.length > 1 && (
                            <>
                                <button onClick={prevBanner} className="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-white flex items-center justify-center shadow-sm rounded-sm z-10">
                                    <ChevronLeft size={16} className="text-[#002094]" />
                                </button>
                                <button onClick={nextBanner} className="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-white flex items-center justify-center shadow-sm rounded-sm z-10">
                                    <ChevronRight size={16} className="text-[#002094]" />
                                </button>
                                <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
                                    {banners.map((_, i) => (
                                        <button key={i} onClick={() => setCurrentBanner(i)} className={`h-1.5 rounded-full transition-all ${currentBanner === i ? 'w-8 bg-white' : 'w-2 bg-white/50'}`} />
                                    ))}
                                </div>
                            </>
                        )}
                    </div>
                )}

                {/* Sections */}
                <div className="mb-24">
                    <div className="text-center mb-10">
                        <h2 className="text-3xl font-black italic text-[#002094]">Đổi điểm, nhận ngay Voucher!</h2>
                        <div className="w-24 h-1 bg-[#002094] mx-auto mt-4 opacity-20"></div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {displayedVouchers.map(reward => (
                            <RewardCard key={reward.id} reward={reward} onClick={() => setSelectedReward(reward)} />
                        ))}
                    </div>
                    {vouchers.length > 6 && (
                        <div className="flex justify-center mt-8">
                            <button
                                onClick={() => setShowAllVouchers(!showAllVouchers)}
                                className="font-mono select-none inline-flex items-center gap-2 justify-center whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer disabled:text-vnm-disabled disabled:bg-fill-disabled-light disabled:pointer-events-none disabled:cursor-default transition duration-300 text-technical-md h-8 px-3 py-1.5 rounded-sm text-vnm-primary border-vnm-primary border bg-transparent disabled:border-border-disabled-light hover:bg-fill-tertiary"
                            >
                                {showAllVouchers ? 'Thu gọn' : 'Xem tất cả'}
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 256 256" className={`ml-2 transition-transform ${showAllVouchers ? 'rotate-180' : ''}`}>
                                    <path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path>
                                </svg>
                            </button>
                        </div>
                    )}
                </div>

                <div className="mb-16">
                    <div className="text-center mb-10">
                        <h2 className="text-3xl font-black italic text-[#002094]">Đổi quà mê say</h2>
                        <div className="w-24 h-1 bg-[#002094] mx-auto mt-4 opacity-20"></div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {displayedGifts.map(reward => (
                            <RewardCard key={reward.id} reward={reward} onClick={() => setSelectedReward(reward)} />
                        ))}
                    </div>
                    {gifts.length > 6 && (
                        <div className="flex justify-center mt-8">
                            <button
                                onClick={() => setShowAllGifts(!showAllGifts)}
                                className="font-mono select-none inline-flex items-center gap-2 justify-center whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer disabled:text-vnm-disabled disabled:bg-fill-disabled-light disabled:pointer-events-none disabled:cursor-default transition duration-300 text-technical-md h-8 px-3 py-1.5 rounded-sm text-vnm-primary border-vnm-primary border bg-transparent disabled:border-border-disabled-light hover:bg-fill-tertiary"
                            >
                                {showAllGifts ? 'Thu gọn' : 'Xem tất cả'}
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 256 256" className={`ml-2 transition-transform ${showAllGifts ? 'rotate-180' : ''}`}>
                                    <path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path>
                                </svg>
                            </button>
                        </div>
                    )}
                </div>
            </main>

            {/* Sidebar Detail */}
            {selectedReward && (
                <>
                    <div className="fixed inset-0 bg-black/40 z-[9998]" onClick={() => setSelectedReward(null)}></div>
                    <div className="fixed top-4 bottom-4 right-4 w-full max-w-[420px] bg-white z-[9999] flex flex-col animate-slide-left rounded-[12px] overflow-hidden shadow-2xl">
                        <div className="relative w-full shrink-0 bg-white">
                            <button onClick={() => setSelectedReward(null)} className="absolute top-4 right-4 z-20 w-8 h-8 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-sm">
                                <X size={18} className="text-[#002094]" />
                            </button>
                            {selectedReward.image && <img src={selectedReward.image} className="w-full h-auto block" />}
                            <div className="absolute bottom-0 w-full h-6 bg-[#002094] flex items-center justify-between px-2 overflow-hidden">
                                {[...Array(25)].map((_, i) => <div key={i} className="w-2 h-4 bg-white rounded-full -mb-3"></div>)}
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto px-8 py-10 custom-scrollbar bg-[#fffff1]">
                            <div className="flex items-center gap-3 mb-8">
                                <span className="text-[13px] font-bold whitespace-nowrap">{selectedReward.points_required.toLocaleString()} Điểm</span>
                                <div className="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div className="h-full bg-green-500" style={{ width: `${selectedReward.progress}%` }}></div>
                                </div>
                            </div>
                            <div className="border-t border-dotted border-blue-200 mb-8"></div>
                            <h2 className="text-[16px] font-bold italic mb-6 leading-snug">{selectedReward.name}</h2>
                            <div className="space-y-4 text-[#002094] text-[12px] font-medium leading-relaxed pb-10 prose prose-sm prose-v-navy max-w-none">
                                <div dangerouslySetInnerHTML={{ __html: selectedReward.description || '' }} />
                            </div>
                        </div>
                        <div className="px-8 py-5 border-t border-gray-50 bg-[#fffff1]">
                            <div className="flex justify-center gap-2 text-[11px] font-bold mb-4 text-center">
                                <span className={selectedReward.stock_quantity < 100 ? 'text-[#b61500]' : ''}>Còn {selectedReward.stock_quantity.toLocaleString()} voucher</span>
                                <span className="mx-1 text-gray-300">•</span>
                                <span className="text-[#002094]">Còn {selectedReward.user_limit - selectedReward.user_redemptions}/{selectedReward.user_limit} lượt</span>
                            </div>
                            {selectedReward.can_redeem ? (
                                <button
                                    onClick={() => handleRedeem(selectedReward.id, selectedReward.name, selectedReward.points_required)}
                                    disabled={isRedeeming}
                                    className="w-full bg-[#002094] border border-[#002094] text-white py-3 rounded text-[13px] font-bold hover:bg-[#001a7a] transition-all uppercase tracking-wide disabled:opacity-50"
                                >
                                    {isRedeeming ? "Đang xử lý..." : "Quy đổi ngay"}
                                </button>
                            ) : selectedReward.stock_quantity > 0 && selectedReward.user_limit - selectedReward.user_redemptions > 0 ? (
                                <button
                                    onClick={() => window.location.href = '/'}
                                    className="w-full border border-[#002094] py-3 rounded text-[13px] font-bold hover:bg-[#002094] hover:text-white transition-all uppercase tracking-wide"
                                >
                                    Kiếm điểm
                                </button>
                            ) : null}
                        </div>
                    </div>
                </>
            )}

            <RewardHistorySidebar
                isOpen={isHistorySidebarOpen}
                onClose={() => setIsHistorySidebarOpen(false)}
            />

            <style jsx>{`
                @keyframes slide-left { from { transform: translateX(100%); } to { transform: translateX(0); } }
                .animate-slide-left { animation: slide-left 0.3s ease-out; }
                .custom-scrollbar::-webkit-scrollbar { width: 5px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: #f8f8f8; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            `}</style>
        </div>
    );
}

function RewardsPageSkeleton() {
    return (
        <div className="bg-[#fcfaf2] min-h-screen font-sans text-[#002094]">
            <Navbar />

            {/* HERO SECTION SKELETON */}
            <section className="bg-[#0213b0] relative h-[750px] w-full pt-20 overflow-hidden">
                <div className="absolute bottom-0 h-auto w-full overflow-hidden">
                    <div className="relative flex h-38 w-full">
                        {[...Array(20)].map((_, i) => (
                            <div key={i} className="relative -top-16.5 h-0 w-0 border-[66px] border-b-[76px] border-solid border-transparent border-b-[#001a7a] after:absolute after:top-[19px] after:-left-[66px] after:h-0 after:w-0 after:content-[''] after:border-[66px] after:border-t-[76px] after:border-solid after:border-transparent after:border-t-[#001a7a]"></div>
                        ))}
                    </div>
                </div>

                <div className="absolute flex w-full justify-center top-[180px]">
                    <div className="flex flex-col items-center gap-6 text-center">
                        <div className="flex flex-col text-center text-white">
                            <div className="h-6 w-32 bg-white/20 rounded animate-pulse mb-2"></div>
                            <div className="h-12 w-64 bg-white/20 rounded animate-pulse"></div>
                        </div>
                        <div className="bg-[#fcfaf2] flex w-[320px] flex-col items-center justify-center gap-4 rounded-xl p-8 shadow-xl">
                            <div className="h-4 w-32 bg-[#002094]/20 rounded animate-pulse"></div>
                            <div className="h-12 w-48 bg-[#002094]/20 rounded animate-pulse"></div>
                            <div className="w-full space-y-3 mt-4">
                                <div className="h-12 bg-[#002094]/20 rounded-lg animate-pulse"></div>
                                <div className="h-12 bg-[#002094]/20 rounded-lg animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <main className="max-w-7xl mx-auto px-4 pb-16 pt-40 mt-[-140px] relative z-10">
                {/* BANNER SKELETON */}
                <div className="relative w-full h-[380px] md:h-[550px] mb-20 overflow-hidden rounded-2xl bg-white border border-gray-100 animate-pulse"></div>

                {/* VOUCHERS SECTION SKELETON */}
                <div className="mb-24">
                    <div className="text-center mb-10">
                        <div className="h-10 w-64 bg-[#002094]/10 rounded animate-pulse mx-auto mb-4"></div>
                        <div className="h-1 w-24 bg-[#002094]/10 rounded animate-pulse mx-auto"></div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {[1, 2, 3].map((i) => (
                            <div key={i} className="bg-white border border-gray-200 rounded-lg overflow-hidden animate-pulse">
                                <div className="w-full h-48 bg-gray-100"></div>
                                <div className="p-5">
                                    <div className="h-4 w-20 bg-gray-100 rounded mb-4"></div>
                                    <div className="h-px bg-gray-100 my-4"></div>
                                    <div className="h-6 w-full bg-gray-100 rounded mb-2"></div>
                                    <div className="h-4 w-full bg-gray-100 rounded mb-6"></div>
                                    <div className="h-4 w-full bg-gray-100 rounded"></div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* GIFTS SECTION SKELETON */}
                <div className="mb-16">
                    <div className="text-center mb-10">
                        <div className="h-10 w-64 bg-[#002094]/10 rounded animate-pulse mx-auto mb-4"></div>
                        <div className="h-1 w-24 bg-[#002094]/10 rounded animate-pulse mx-auto"></div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {[1, 2, 3].map((i) => (
                            <div key={i} className="bg-white border border-gray-200 rounded-lg overflow-hidden animate-pulse">
                                <div className="w-full h-48 bg-gray-100"></div>
                                <div className="p-5">
                                    <div className="h-4 w-20 bg-gray-100 rounded mb-4"></div>
                                    <div className="h-px bg-gray-100 my-4"></div>
                                    <div className="h-6 w-full bg-gray-100 rounded mb-2"></div>
                                    <div className="h-4 w-full bg-gray-100 rounded mb-6"></div>
                                    <div className="h-4 w-full bg-gray-100 rounded"></div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </main>
        </div>
    );
}

function RewardCard({ reward, onClick }: { reward: Reward, onClick: () => void }) {
    return (
        <div onClick={onClick} className="bg-white border border-gray-200 overflow-hidden cursor-pointer flex flex-col group">
            <div className="w-full relative bg-white">
                {reward.image ? (
                    <img src={reward.image} className="w-full h-auto block transition-transform" />
                ) : (
                    <div className="w-full h-48 bg-[#002094] flex items-center justify-center"><Ticket className="text-white/20" size={48} /></div>
                )}
            </div>
            <div className="p-5 flex-1 flex flex-col">
                <div className="flex items-center gap-3 mb-4">
                    <span className="text-[12px] font-bold whitespace-nowrap text-[#002094]">{reward.points_required.toLocaleString()} Điểm</span>
                    <div className="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div className="h-full bg-green-500" style={{ width: `${reward.progress}%` }}></div>
                    </div>
                </div>
                <div className="border-t border-dotted border-blue-200 my-4"></div>
                <h3 className="text-[14px] font-bold italic mb-2 line-clamp-2 leading-tight h-10 text-[#002094]">{reward.name}</h3>
                <div className="space-y-1 mb-6 h-10 overflow-hidden">
                    <div 
                        className="text-[11px] text-gray-400 line-clamp-2 prose prose-sm prose-v-navy max-w-none"
                        dangerouslySetInnerHTML={{ __html: reward.description || '' }} 
                    />
                </div>
                <div className="flex justify-between items-center mt-auto pt-2">
                    <div className="text-[11px] font-bold text-[#002094]">
                        <span className={reward.stock_quantity < 100 ? 'text-[#b61500]' : ''}>Còn {reward.stock_quantity.toLocaleString()} voucher</span>
                        <span className="mx-1 text-gray-300">•</span>
                        <span>Còn {reward.user_limit - reward.user_redemptions}/{reward.user_limit} lượt</span>
                    </div>
                    {reward.can_redeem ? (
                        <button className="bg-[#002094] text-white px-4 py-1.5 rounded text-[10px] font-bold hover:bg-blue-900 transition-colors">Quy đổi</button>
                    ) : (
                        <button className="border border-[#002094] px-4 py-1.5 rounded text-[10px] font-bold hover:bg-[#002094] hover:text-white transition-colors">Kiếm điểm</button>
                    )}
                </div>
            </div>
        </div>
    );
}
