'use client';

import React, { useState, useEffect, useRef } from 'react';
import { Send, X, MessageCircle } from 'lucide-react';

interface Message {
    role: 'user' | 'bot';
    content: string;
}

const QUICK_ACTIONS = [
    'Đơn hàng',
    'Sản phẩm',
    'Chương trình khuyến mãi mới',
    'Tìm hiểu Vinamilk',
    'Vinamilk Rewards'
];

export default function ChatWidget() {
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState<Message[]>([
        { role: 'bot', content: 'Chào bạn, mình là Vinamilk luôn ở đây để hỗ trợ bạn.' },
        { role: 'bot', content: 'Vinamilk có thể giúp gì cho bạn không?' }
    ]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const scrollRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    }, [messages]);

    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
        return () => { document.body.style.overflow = 'unset'; };
    }, [isOpen]);

    const handleSend = async (text: string) => {
        if (!text.trim() || isLoading) return;

        let sessionId = localStorage.getItem('chatSessionId');
        if (!sessionId) {
            sessionId = 'sess_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chatSessionId', sessionId);
        }

        const newMessages: Message[] = [...messages, { role: 'user', content: text }];
        setMessages(newMessages);
        setInput('');
        setIsLoading(true);

        try {
            const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Chat-Session-Id': sessionId
                },
                body: JSON.stringify({
                    message: text,
                    history: messages.map(m => ({ role: m.role, content: m.content }))
                }),
            });

            const data = await response.json();
            if (data.reply) {
                setMessages([...newMessages, { role: 'bot', content: data.reply }]);
            } else {
                setMessages([...newMessages, { role: 'bot', content: 'Xin lỗi, mình đang gặp chút trục trặc. Bạn thử lại nhé!' }]);
            }
        } catch (error) {
            setMessages([...newMessages, { role: 'bot', content: 'Lỗi kết nối. Vui lòng kiểm tra mạng!' }]);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <>
            {/* Nút mở Chat (Custom theo yêu cầu) */}
            <div id="chat-bot-button" className="animate__animated animate__fadeInUp fixed right-4 bottom-4 z-[9998] space-y-2 text-end lg:right-6">
                <button
                    onClick={() => setIsOpen(true)}
                    className="font-mono select-none inline-flex items-center gap-2 justify-center whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 cursor-pointer disabled:text-vnm-disabled disabled:bg-fill-disabled-light disabled:pointer-events-none disabled:cursor-default text-technical-md px-3 py-2.5 text-vnm-invert bg-[#cbf7ec] size-14 rounded-full border border-solid !shadow-sm transition duration-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" className="text-blue">
                        <path d="M216,40H40A16,16,0,0,0,24,56V184a16,16,0,0,0,16,16h60.43l13.68,23.94a16,16,0,0,0,27.78,0L155.57,200H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM84,132a12,12,0,1,1,12-12A12,12,0,0,1,84,132Zm44,0a12,12,0,1,1,12-12A12,12,0,0,1,128,132Zm44,0a12,12,0,1,1,12-12A12,12,0,0,1,172,132Z"></path>
                    </svg>
                </button>
            </div>

            {/* Overlay làm mờ nền */}
            {isOpen && (
                <div
                    className="fixed inset-0 bg-black/50 backdrop-blur-md z-[9999] transition-opacity"
                    onClick={() => setIsOpen(false)}
                />
            )}

            {/* Khung Chat nổi */}
            <div className={`fixed top-4 bottom-4 right-4 w-full max-w-[400px] bg-[#f0f4f9] shadow-2xl z-[10000] rounded-3xl overflow-hidden transform transition-transform duration-500 ease-in-out flex flex-col ${isOpen ? 'translate-x-0' : 'translate-x-[110%]'}`}>

                {/* Header */}
                <div className="bg-white p-4 flex items-center justify-between border-b border-blue-100">
                    <div className="pl-2">
                        <h2 className="text-[#002094] text-2xl font-bold tracking-tight">Vinamilk</h2>
                        <p className="text-[#002094]/70 text-xs font-semibold uppercase tracking-wider">Trực tuyến</p>
                    </div>
                    <button
                        onClick={() => setIsOpen(false)}
                        className="text-[#002094]/50 hover:text-[#002094] hover:bg-blue-50 p-2 rounded-full transition-all"
                    >
                        <X size={24} />
                    </button>
                </div>

                {/* Chat Content */}
                <div
                    ref={scrollRef}
                    className="flex-1 overflow-y-auto p-4 space-y-4"
                >
                    {messages.map((msg, idx) => (
                        <div key={idx} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'} items-start gap-2`}>
                            {msg.role === 'bot' && (
                                <div className="w-8 h-8 bg-[#002094] rounded-full flex items-center justify-center flex-shrink-0 mt-1 shadow-sm">
                                    <span className="text-white font-bold text-xs">V</span>
                                </div>
                            )}
                            <div className={`max-w-[85%] p-3 rounded-2xl text-[14px] leading-relaxed shadow-sm whitespace-pre-line ${msg.role === 'user'
                                ? 'bg-[#002094] text-white rounded-tr-none'
                                : 'bg-white text-[#002094] rounded-tl-none font-medium border border-blue-50'
                                }`}>
                                {msg.content}
                            </div>
                        </div>
                    ))}

                    {/* Quick Actions - Chỉ hiện khi chưa bắt đầu chat (chỉ có 2 tin nhắn chào) */}
                    {messages.length === 2 && (
                        <div className="flex flex-wrap gap-2 pt-2 px-2">
                            {QUICK_ACTIONS.map((action, i) => (
                                <button
                                    key={i}
                                    onClick={() => handleSend(action)}
                                    className="px-3 py-1.5 border border-[#002094] text-[#002094] rounded-lg font-bold hover:bg-[#002094] hover:text-white transition-all text-[12px]"
                                >
                                    {action}
                                </button>
                            ))}
                        </div>
                    )}

                    {isLoading && (
                        <div className="flex justify-start items-center gap-1.5 text-[#002094] p-2">
                            <div className="w-1.5 h-1.5 bg-[#002094] rounded-full animate-bounce"></div>
                            <div className="w-1.5 h-1.5 bg-[#002094] rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div className="w-1.5 h-1.5 bg-[#002094] rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                    )}
                </div>

                {/* Footer with Wavy Effect */}
                <div className="relative pt-10">
                    {/* SVG Wavy Background */}
                    <div className="absolute top-0 left-0 w-full overflow-hidden leading-[0]">
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none" className="w-full h-12">
                            <path d="M0.00,49.98 C150.00,150.00 349.20,-49.98 500.00,49.98 L500.00,150.00 L0.00,150.00 Z" style={{ stroke: 'none', fill: '#002094' }}></path>
                        </svg>
                    </div>

                    <div className="bg-[#002094] p-8 pb-10">
                        <div className="relative flex items-center border-b-2 border-white/50 focus-within:border-white transition-colors">
                            <input
                                type="text"
                                value={input}
                                onChange={(e) => setInput(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSend(input)}
                                placeholder="Nhập tin nhắn dưới 1.000 ký tự nhé!"
                                className="w-full bg-transparent text-white placeholder:text-white/60 py-3 pr-10 focus:outline-none text-sm"
                            />
                            <button
                                onClick={() => handleSend(input)}
                                className="absolute right-0 text-white hover:scale-110 transition-transform"
                            >
                                <Send size={24} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
